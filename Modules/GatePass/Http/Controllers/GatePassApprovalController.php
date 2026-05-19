<?php

namespace Modules\GatePass\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\GatePass\Entities\GatePassRequest;
use Modules\GatePass\Entities\GatePassApprovalLog;
use Modules\GatePass\Entities\GatePassItem;
use Modules\GatePass\Entities\GatePassItemReturn;
use Modules\GatePass\Notifications\GatePassStatusNotification;

class GatePassApprovalController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function hodAction(Request $request, $id)
    {
        abort_403(user()->permission('approve_gate_pass') == 'none');

        $gatePass = GatePassRequest::findOrFail($id);
        $action = $request->action; // approve, reject, revision

        if ($action == 'approve') {
            $gatePass->status = 'pending_store';
            $logAction = 'hod_approved';
            $msg = 'Request approved by HOD';
        } elseif ($action == 'reject') {
            $gatePass->status = 'rejected_hod';
            $logAction = 'hod_rejected';
            $msg = 'Request rejected by HOD';
        } else {
            $gatePass->status = 'sent_back';
            $logAction = 'hod_sent_back';
            $msg = 'Request sent back for revision';
        }

        $gatePass->hod_id = user()->id;
        $gatePass->hod_remarks = $request->remarks;
        $gatePass->save();

        GatePassApprovalLog::create([
            'gate_pass_request_id' => $gatePass->id,
            'user_id' => user()->id,
            'action' => $logAction,
            'remarks' => $request->remarks,
            'ip_address' => $request->ip()
        ]);

        if ($logAction == 'hod_sent_back' && $gatePass->user) {
            $gatePass->user->notify(new GatePassStatusNotification($gatePass, 'hod_sent_back', $request->remarks, user()));
        }

        return Reply::success($msg);
    }

    public function storeAction(Request $request, $id)
    {
        abort_403(user()->permission('verify_gate_pass') == 'none');

        $gatePass = GatePassRequest::findOrFail($id);
        $action = $request->action; // authorize, reject, hold

        if ($action == 'authorize') {
            $gatePass->status = 'pending_security';
            $logAction = 'store_authorized';
            $msg = 'Request authorized by Store';
            
            // Generate QR Code hash if not exists
            if (!$gatePass->qr_code) {
                $gatePass->qr_code = md5($gatePass->request_number . time());
            }
        } elseif ($action == 'reject') {
            $gatePass->status = 'rejected_store';
            $logAction = 'store_rejected';
            $msg = 'Request rejected by Store';
        } elseif ($action == 'send_back') {
            $gatePass->status = 'pending_hod';
            $logAction = 'store_sent_back';
            $msg = 'Request sent back to HOD by Store';
        } else {
            $gatePass->status = 'hold_store';
            $logAction = 'store_hold';
            $msg = 'Request put on hold by Store';
        }

        $gatePass->store_id = user()->id;
        $gatePass->store_remarks = $request->remarks;
        $gatePass->save();

        GatePassApprovalLog::create([
            'gate_pass_request_id' => $gatePass->id,
            'user_id' => user()->id,
            'action' => $logAction,
            'remarks' => $request->remarks,
            'ip_address' => $request->ip()
        ]);

        if ($logAction == 'store_sent_back' && $gatePass->hod) {
            $gatePass->hod->notify(new GatePassStatusNotification($gatePass, 'store_sent_back', $request->remarks, user()));
        }

        return Reply::success($msg);
    }

    public function securityAction(Request $request, $id)
    {
        abort_403(user()->permission('authorize_gate_pass') == 'none');

        $gatePass = GatePassRequest::findOrFail($id);
        $action = $request->action; // allow, reject, send_back

        if ($action == 'allow') {
            if ($gatePass->return_type == 'returnable') {
                $gatePass->status = 'open';
                $msg = 'Exit allowed by Security. Gate Pass is now open for returns.';
            } else {
                $gatePass->status = 'completed';
                $msg = 'Exit allowed by Security. Gate Pass completed.';
            }
            $logAction = 'security_cleared';
        } elseif ($action == 'send_back') {
            $gatePass->status = 'pending_store';
            $logAction = 'security_sent_back';
            $msg = 'Request sent back to Store by Security';
        } else {
            $gatePass->status = 'rejected_security';
            $logAction = 'security_rejected';
            $msg = 'Exit rejected by Security';
        }

        $gatePass->security_id = user()->id;
        $gatePass->security_remarks = $request->remarks;
        $gatePass->save();

        GatePassApprovalLog::create([
            'gate_pass_request_id' => $gatePass->id,
            'user_id' => user()->id,
            'action' => $logAction,
            'remarks' => $request->remarks,
            'ip_address' => $request->ip()
        ]);

        if ($logAction == 'security_sent_back' && $gatePass->store) {
            $gatePass->store->notify(new GatePassStatusNotification($gatePass, 'security_sent_back', $request->remarks, user()));
        }

        return Reply::success($msg);
    }

    public function recordReturn(Request $request, $id)
    {
        abort_403(user()->permission('verify_gate_pass') == 'none');

        $gatePass = GatePassRequest::with('items')->findOrFail($id);
        
        if ($gatePass->status != 'open') {
            return Reply::error('Returns can only be recorded for open returnable gate passes.');
        }

        $request->validate([
            'item_ids' => 'required|array',
            'item_ids.*' => 'exists:gate_pass_items,id',
            'return_qty' => 'required|array',
            'settle_qty' => 'required|array',
        ]);

        \DB::beginTransaction();
        try {
            $hasActivity = false;
            foreach ($request->item_ids as $itemId) {
                $item = GatePassItem::findOrFail($itemId);
                $returnQty = floatval($request->return_qty[$itemId] ?? 0);
                $settleQty = floatval($request->settle_qty[$itemId] ?? 0);
                $status = $request->settle_status[$itemId] ?? 'returned';
                $remarks = $request->remarks[$itemId] ?? null;

                if ($returnQty <= 0 && $settleQty <= 0) {
                    continue;
                }

                $pendingQty = $item->quantity - $item->returned_quantity - $item->settled_quantity;

                if (($returnQty + $settleQty) > $pendingQty) {
                    return Reply::error("Total returned and settled quantity for item '{$item->item_name}' cannot exceed pending quantity of {$pendingQty}.");
                }

                if ($returnQty > 0) {
                    $item->returned_quantity += $returnQty;
                    
                    GatePassItemReturn::create([
                        'gate_pass_request_id' => $gatePass->id,
                        'gate_pass_item_id' => $item->id,
                        'user_id' => user()->id,
                        'quantity' => $returnQty,
                        'type' => 'return',
                        'status' => 'returned',
                        'remarks' => $remarks
                    ]);
                    $hasActivity = true;
                }

                if ($settleQty > 0) {
                    $item->settled_quantity += $settleQty;
                    
                    GatePassItemReturn::create([
                        'gate_pass_request_id' => $gatePass->id,
                        'gate_pass_item_id' => $item->id,
                        'user_id' => user()->id,
                        'quantity' => $settleQty,
                        'type' => 'settlement',
                        'status' => $status,
                        'remarks' => $remarks,
                        'settlement_note' => $remarks
                    ]);
                    $hasActivity = true;
                }

                $item->save();
            }

            if (!$hasActivity) {
                return Reply::error('No returned or settled quantities were entered.');
            }

            // Recalculate if fully returned/settled
            $allDone = true;
            foreach ($gatePass->items as $item) {
                $item->refresh();
                if (($item->returned_quantity + $item->settled_quantity) < $item->quantity) {
                    $allDone = false;
                    break;
                }
            }

            if ($allDone) {
                $gatePass->status = 'completed';
                $gatePass->save();

                GatePassApprovalLog::create([
                    'gate_pass_request_id' => $gatePass->id,
                    'user_id' => user()->id,
                    'action' => 'fully_returned',
                    'remarks' => 'All returnable items have been fully returned or settled.',
                    'ip_address' => $request->ip()
                ]);
                $msg = 'All items fully received/settled. Gate Pass auto-closed.';
            } else {
                GatePassApprovalLog::create([
                    'gate_pass_request_id' => $gatePass->id,
                    'user_id' => user()->id,
                    'action' => 'partial_return',
                    'remarks' => 'Recorded partial item returns/settlements.',
                    'ip_address' => $request->ip()
                ]);
                $msg = 'Return/settlement recorded successfully.';
            }

            \DB::commit();
            return Reply::success($msg);

        } catch (\Exception $e) {
            \DB::rollBack();
            return Reply::error($e->getMessage());
        }
    }

    public function manuallyClose(Request $request, $id)
    {
        abort_403(user()->permission('verify_gate_pass') == 'none');

        $gatePass = GatePassRequest::with('items')->findOrFail($id);
        
        if ($gatePass->status != 'open') {
            return Reply::error('Only open returnable gate passes can be manually closed.');
        }

        $request->validate([
            'manual_close_remarks' => 'required|string',
        ]);

        \DB::beginTransaction();
        try {
            foreach ($gatePass->items as $item) {
                $pendingQty = $item->quantity - $item->returned_quantity - $item->settled_quantity;
                if ($pendingQty > 0) {
                    $item->settled_quantity += $pendingQty;
                    $item->save();

                    GatePassItemReturn::create([
                        'gate_pass_request_id' => $gatePass->id,
                        'gate_pass_item_id' => $item->id,
                        'user_id' => user()->id,
                        'quantity' => $pendingQty,
                        'type' => 'settlement',
                        'status' => 'unreturnable',
                        'remarks' => $request->manual_close_remarks,
                        'settlement_note' => $request->manual_close_remarks
                    ]);
                }
            }

            $gatePass->status = 'completed';
            $gatePass->save();

            GatePassApprovalLog::create([
                'gate_pass_request_id' => $gatePass->id,
                'user_id' => user()->id,
                'action' => 'manually_closed',
                'remarks' => $request->manual_close_remarks,
                'ip_address' => $request->ip()
            ]);

            \DB::commit();
            return Reply::success('Gate Pass manually settled and closed successfully.');

        } catch (\Exception $e) {
            \DB::rollBack();
            return Reply::error($e->getMessage());
        }
    }
}
