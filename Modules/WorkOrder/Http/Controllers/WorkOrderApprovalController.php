<?php

namespace Modules\WorkOrder\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use App\Models\User;
use Illuminate\Http\Request;
use Modules\WorkOrder\Entities\ApprovalMapping;
use Modules\WorkOrder\Entities\WorkOrder;
use Modules\WorkOrder\Entities\WorkOrderApproval;
use Modules\WorkOrder\Notifications\WorkOrderApproved;
use Modules\WorkOrder\Notifications\WorkOrderRejected;

class WorkOrderApprovalController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('workorder::modules.workOrder.approvals');
    }

    /**
     * Approve a pending work order.
     * Only the mapped approver for the creator may approve.
     */
    public function approve(Request $request, $id)
    {
        $workOrder = WorkOrder::where('company_id', company()->id)->findOrFail($id);

        // ── Security: only assigned approver may act ───────────────────────────
        $mapping = ApprovalMapping::getApproverFor($workOrder->created_by);
        abort_403(!$mapping || $mapping->approver_id !== user()->id);
        abort_403(!$workOrder->isPendingApproval());

        $request->validate([
            'remarks' => 'nullable|string|max:1000',
        ]);

        $workOrder->update([
            'status'      => 'approved',
            'approved_by' => user()->id,
            'approved_at' => now(),
            'last_updated_by' => user()->id,
        ]);

        WorkOrderApproval::create([
            'work_order_id' => $workOrder->id,
            'user_id'       => user()->id,
            'action'        => 'approved',
            'remarks'       => $request->remarks ?? 'Approved.',
            'ip_address'    => $request->ip(),
        ]);

        // Notify creator
        $creator = User::find($workOrder->created_by);
        if ($creator) {
            $creator->notify(new WorkOrderApproved($workOrder));
        }

        return Reply::successWithData(__('workorder::modules.workOrder.approvedSuccess'), [
            'redirectUrl' => route('work-orders.show', $workOrder->id),
        ]);
    }

    /**
     * Reject a pending work order.
     * Only the mapped approver for the creator may reject.
     */
    public function reject(Request $request, $id)
    {
        $workOrder = WorkOrder::where('company_id', company()->id)->findOrFail($id);

        $mapping = ApprovalMapping::getApproverFor($workOrder->created_by);
        abort_403(!$mapping || $mapping->approver_id !== user()->id);
        abort_403(!$workOrder->isPendingApproval());

        $request->validate([
            'remarks' => 'required|string|max:1000',
        ]);

        $workOrder->update([
            'status'          => 'rejected',
            'last_updated_by' => user()->id,
        ]);

        WorkOrderApproval::create([
            'work_order_id' => $workOrder->id,
            'user_id'       => user()->id,
            'action'        => 'rejected',
            'remarks'       => $request->remarks,
            'ip_address'    => $request->ip(),
        ]);

        // Notify creator
        $creator = User::find($workOrder->created_by);
        if ($creator) {
            $creator->notify(new WorkOrderRejected($workOrder));
        }

        return Reply::successWithData(__('workorder::modules.workOrder.rejectedSuccess'), [
            'redirectUrl' => route('work-orders.show', $workOrder->id),
        ]);
    }

    /**
     * Send back a work order for revision.
     * Only the assigned approver may send back.
     */
    public function sendBack(Request $request, $id)
    {
        $workOrder = WorkOrder::where('company_id', company()->id)->findOrFail($id);

        $mapping = ApprovalMapping::getApproverFor($workOrder->created_by);
        abort_403(!$mapping || $mapping->approver_id !== user()->id);
        abort_403(!$workOrder->isPendingApproval());

        $request->validate([
            'remarks' => 'required|string|max:1000',
        ]);

        $workOrder->update([
            'status'          => 'draft',
            'last_updated_by' => user()->id,
        ]);

        WorkOrderApproval::create([
            'work_order_id' => $workOrder->id,
            'user_id'       => user()->id,
            'action'        => 'sent_back',
            'remarks'       => $request->remarks,
            'ip_address'    => $request->ip(),
        ]);

        return Reply::successWithData(__('workorder::modules.workOrder.sentBackSuccess'), [
            'redirectUrl' => route('work-orders.show', $workOrder->id),
        ]);
    }
}
