<?php

namespace Modules\GatePass\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use Illuminate\Http\Request;
use Modules\GatePass\Entities\GatePassRequest;
use Modules\GatePass\Entities\GatePassApprovalLog;

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

        return Reply::success($msg);
    }

    public function securityAction(Request $request, $id)
    {
        abort_403(user()->permission('authorize_gate_pass') == 'none');

        $gatePass = GatePassRequest::findOrFail($id);
        $action = $request->action; // allow, reject

        if ($action == 'allow') {
            $gatePass->status = 'completed';
            $logAction = 'security_cleared';
            $msg = 'Exit allowed by Security';
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

        return Reply::success($msg);
    }
}
