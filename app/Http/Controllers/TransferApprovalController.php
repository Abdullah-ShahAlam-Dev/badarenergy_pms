<?php

namespace App\Http\Controllers;

use App\Services\TransferApprovalService;
use App\Services\TransferHistoryService;
use Illuminate\Http\Request;

class TransferApprovalController extends AccountBaseController
{
    protected $approvalService;
    protected $historyService;

    public function __construct()
    {
        parent::__construct();
        $this->approvalService = new TransferApprovalService();
        $this->historyService = new TransferHistoryService();
    }

    public function approve(Request $request, $id)
    {
        abort_403(user()->permission('approve_stock_transfer') == 'none');

        try {
            $this->approvalService->approve($id, user()->id);
            $this->historyService->logAction($id, user()->id, 'approve', [
                'approved_by' => user()->name
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Stock transfer request approved.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function reject(Request $request, $id)
    {
        abort_403(user()->permission('approve_stock_transfer') == 'none');

        $request->validate([
            'reason' => 'required|string|max:255'
        ]);

        try {
            $this->approvalService->reject($id, user()->id, $request->reason);
            $this->historyService->logAction($id, user()->id, 'reject', [
                'rejected_by' => user()->name,
                'reason' => $request->reason
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Stock transfer request rejected.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
