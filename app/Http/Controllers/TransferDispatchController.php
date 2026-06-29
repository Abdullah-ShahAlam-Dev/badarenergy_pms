<?php

namespace App\Http\Controllers;

use App\Services\TransferDispatchService;
use App\Services\TransferHistoryService;
use Illuminate\Http\Request;

class TransferDispatchController extends AccountBaseController
{
    protected $dispatchService;
    protected $historyService;

    public function __construct()
    {
        parent::__construct();
        $this->dispatchService = new TransferDispatchService();
        $this->historyService = new TransferHistoryService();
    }

    public function dispatch(Request $request, $id)
    {
        abort_403(user()->permission('dispatch_stock_transfer') == 'none');

        $request->validate([
            'vehicle_number' => 'nullable|string|max:50',
            'driver_name' => 'nullable|string|max:100'
        ]);

        try {
            $this->dispatchService->dispatch($id, user()->id, $request->all());
            $this->historyService->logAction($id, user()->id, 'dispatch', [
                'dispatched_by' => user()->name,
                'vehicle_number' => $request->vehicle_number,
                'driver_name' => $request->driver_name
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Stock transfer items successfully dispatched.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
