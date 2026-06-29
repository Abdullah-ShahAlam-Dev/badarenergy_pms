<?php

namespace App\Http\Controllers;

use App\DataTables\InventoryDataTable;
use App\DataTables\ProductSerialDataTable;
use App\Helper\Reply;
use App\Http\Requests\Inventory\StoreAdjustmentRequest;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\StockAdjustmentService;
use Illuminate\Http\Request;

class InventoryController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.inventory';

        $this->middleware(function ($request, $next) {
            abort_403(!in_array('admin', user_roles()) && user()->permission('view_inventory') == 'none');

            return $next($request);
        });
    }

    /**
     * Display current inventories list.
     */
    public function index(InventoryDataTable $dataTable)
    {
        $this->viewPermission   = user()->permission('view_inventory');
        $this->adjustPermission = user()->permission('adjust_inventory');

        abort_403($this->viewPermission == 'none' && !in_array('admin', user_roles()));

        $this->warehouses = Warehouse::active()->get();
        $this->products = Product::all();

        return $dataTable->render('inventory.index', $this->data);
    }

    /**
     * Show form for manual stock adjustment (AJAX / Right Modal).
     */
    public function create()
    {
        // Direct navigation is redirected to index
        if (!request()->ajax()) {
            return redirect(route('inventory.index'));
        }

        $this->adjustPermission = user()->permission('adjust_inventory');
        abort_403($this->adjustPermission != 'all' && !in_array('admin', user_roles()));

        $this->warehouses = Warehouse::active()->get();
        $this->products = Product::all();
        $this->pageTitle = __('modules.inventory.manualAdjustment');

        $this->defaultProductId = request()->product_id;
        $this->defaultWarehouseId = request()->warehouse_id;

        $html = view('inventory.ajax.create', $this->data)->render();
        return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
    }

    /**
     * Save a manual stock adjustment.
     */
    public function store(StoreAdjustmentRequest $request, StockAdjustmentService $adjustmentService)
    {
        $this->adjustPermission = user()->permission('adjust_inventory');
        abort_403($this->adjustPermission != 'all' && !in_array('admin', user_roles()));

        try {
            $serialNumbers = [];
            if ($request->has('serial_numbers') && !is_null($request->serial_numbers)) {
                $serialNumbers = array_filter(array_map('trim', explode("\n", str_replace("\r", "", $request->serial_numbers))));
            }

            $adjustmentService->adjustStock(
                $request->product_id,
                $request->warehouse_id,
                (float) $request->quantity,
                $request->type,
                $request->category ?: 'available',
                'manual',
                null,
                strip_tags($request->remarks),
                $serialNumbers
            );

            return Reply::successWithData(__('messages.recordSaved'), [
                'redirectUrl' => route('inventory.index'),
            ]);
        } catch (\Exception $e) {
            return Reply::error($e->getMessage());
        }
    }

    /**
     * Display the serial numbers search and tracking list.
     */
    public function serials(ProductSerialDataTable $dataTable)
    {
        $this->viewPermission = user()->permission('view_inventory');
        abort_403($this->viewPermission == 'none' && !in_array('admin', user_roles()));

        $this->warehouses = Warehouse::active()->get();
        $this->products = Product::where('is_serialized', true)->get();
        $this->pageTitle = 'Serial Numbers Tracking';

        return $dataTable->render('inventory.serials', $this->data);
    }
}
