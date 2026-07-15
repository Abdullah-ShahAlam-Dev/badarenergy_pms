<?php

namespace App\Http\Controllers;

use App\Services\TransferReceiptService;
use App\Services\TransferHistoryService;
use Illuminate\Http\Request;

class TransferReceiptController extends AccountBaseController
{
    protected $receiptService;
    protected $historyService;

    public function __construct()
    {
        parent::__construct();
        $this->receiptService = new TransferReceiptService();
        $this->historyService = new TransferHistoryService();
    }

    public function receive(Request $request, $id)
    {
        abort_403(user()->permission('add_inventory') == 'none');

        $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|integer',
            'items.*.serials' => 'required|array'
        ]);

        try {
            $this->receiptService->receive($id, user()->id, $request->all());
            $this->historyService->logAction($id, user()->id, 'receive', [
                'received_by' => user()->name,
                'items_logged' => count($request->items)
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Stock transfer receipt processed successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
