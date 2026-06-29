<?php

namespace App\Http\Controllers;

use App\DataTables\StockTransferDataTable;
use App\Models\StockTransfer;
use App\Models\Warehouse;
use App\Models\Product;
use App\Services\StockTransferService;
use App\Services\TransferHistoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockTransferController extends AccountBaseController
{
    protected $transferService;
    protected $historyService;

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'WMS Stock Transfers';
        $this->transferService = new StockTransferService();
        $this->historyService = new TransferHistoryService();
    }

    public function index(StockTransferDataTable $dataTable)
    {
        abort_403(user()->permission('view_stock_transfer') == 'none');

        $this->warehouses = Warehouse::where('is_active', true)
            ->where('company_id', company()->id)
            ->get();

        return $dataTable->render('stock-transfers.index', $this->data);
    }

    public function create()
    {
        abort_403(user()->permission('add_stock_transfer') == 'none');

        $this->warehouses = Warehouse::where('is_active', true)
            ->where('company_id', company()->id)
            ->get();

        $this->products = Product::where('company_id', company()->id)
            ->with(['serials' => function ($q) {
                $q->where('status', 'available');
            }])
            ->get();

        return view('stock-transfers.create', $this->data);
    }

    public function store(Request $request)
    {
        abort_403(user()->permission('add_stock_transfer') == 'none');

        $request->validate([
            'source_warehouse_id' => 'required|integer',
            'destination_warehouse_id' => 'required|integer',
            'items' => 'required|array',
            'items.*.product_id' => 'required|integer',
            'items.*.quantity' => 'required|numeric|min:0.01'
        ]);

        try {
            $transfer = $this->transferService->createDraft($request->all());
            
            // Log creation in history
            $this->historyService->logAction($transfer->id, user()->id, 'create', [
                'transfer_number' => $transfer->transfer_number,
                'source_warehouse' => $transfer->sourceWarehouse->name,
                'destination_warehouse' => $transfer->destinationWarehouse->name
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Transfer draft created successfully.',
                'redirectUrl' => route('stock-transfers.show', $transfer->id)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function edit($id)
    {
        abort_403(user()->permission('edit_stock_transfer') == 'none');

        $this->transfer = StockTransfer::with(['items.product', 'items.serials.serial'])->findOrFail($id);
        
        if ($this->transfer->status !== StockTransfer::STATUS_DRAFT) {
            abort(400, "Only drafts can be modified.");
        }

        $this->warehouses = Warehouse::where('is_active', true)
            ->where('company_id', company()->id)
            ->get();

        $this->products = Product::where('company_id', company()->id)
            ->with(['serials' => function ($q) {
                $q->where('status', 'available');
            }])
            ->get();

        return view('stock-transfers.edit', $this->data);
    }

    public function update(Request $request, $id)
    {
        abort_403(user()->permission('edit_stock_transfer') == 'none');

        $request->validate([
            'source_warehouse_id' => 'required|integer',
            'destination_warehouse_id' => 'required|integer',
            'items' => 'required|array',
            'items.*.product_id' => 'required|integer',
            'items.*.quantity' => 'required|numeric|min:0.01'
        ]);

        try {
            $transfer = $this->transferService->updateDraft($id, $request->all());
            
            $this->historyService->logAction($transfer->id, user()->id, 'edit', [
                'updated_by' => user()->name
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Transfer draft updated successfully.',
                'redirectUrl' => route('stock-transfers.show', $transfer->id)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function show($id)
    {
        abort_403(user()->permission('view_stock_transfer') == 'none');

        $this->transfer = StockTransfer::with([
            'sourceWarehouse',
            'destinationWarehouse',
            'creator',
            'approver',
            'dispatcher',
            'receiver',
            'canceller',
            'items.product',
            'items.serials.serial',
            'logs.user'
        ])->findOrFail($id);

        $wmsSetting = DB::table('wms_settings')->where('company_id', company()->id)->first();
        $this->approvalRequired = $wmsSetting ? $wmsSetting->transfer_approval_required : false;

        return view('stock-transfers.show', $this->data);
    }

    public function destroy($id)
    {
        abort_403(user()->permission('delete_stock_transfer') == 'none');

        try {
            DB::transaction(function () use ($id) {
                $transfer = StockTransfer::findOrFail($id);
                if ($transfer->status !== StockTransfer::STATUS_DRAFT) {
                    throw new \Exception("Only draft transfers can be deleted.");
                }

                // Revert serial status back to available
                foreach ($transfer->items as $item) {
                    foreach ($item->serials as $ts) {
                        $ts->serial->update(['status' => ProductSerial::STATUS_AVAILABLE]);
                    }
                }
                $transfer->delete();
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Transfer draft deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function cancel($id)
    {
        abort_403(user()->permission('cancel_stock_transfer') == 'none');

        try {
            DB::transaction(function () use ($id) {
                $transfer = StockTransfer::findOrFail($id);
                
                if (in_array($transfer->status, [StockTransfer::STATUS_RECEIVED, StockTransfer::STATUS_CANCELLED, StockTransfer::STATUS_REJECTED])) {
                    throw new \Exception("This stock transfer is already finalized or cancelled.");
                }

                // Release reserved or transit serials back to available in source warehouse
                foreach ($transfer->items as $item) {
                    foreach ($item->serials as $ts) {
                        $ts->serial->update([
                            'status' => ProductSerial::STATUS_AVAILABLE,
                            'warehouse_id' => $transfer->source_warehouse_id
                        ]);
                    }
                }

                $transfer->update([
                    'status' => StockTransfer::STATUS_CANCELLED,
                    'cancelled_by' => user()->id,
                    'cancelled_at' => now(),
                    'cancelled_ip' => request()->ip()
                ]);

                $this->historyService->logAction($transfer->id, user()->id, 'cancel', [
                    'cancelled_by' => user()->name
                ]);
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Transfer request cancelled successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
