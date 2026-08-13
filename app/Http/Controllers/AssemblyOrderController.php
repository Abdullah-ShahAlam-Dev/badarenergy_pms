<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\AssemblyOrder;
use App\Models\AssemblyOrderItem;
use App\Models\AssemblyFaultLog;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\AssemblyLineService;
use Illuminate\Http\Request;
use Exception;

class AssemblyOrderController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'Assembly Line Jobs';
        $this->activeMenu = 'assembly-orders';

        $this->middleware(function ($request, $next) {
            abort_403(!in_array('admin', user_roles()) && user()->permission('add_inventory') == 'none');
            return $next($request);
        });
    }

    public function index()
    {
        $companyId = company() ? company()->id : 1;
        $this->assemblyOrders = AssemblyOrder::where('company_id', $companyId)
            ->with(['targetProduct', 'warehouse', 'creator'])
            ->orderBy('id', 'desc')
            ->get();

        return view('assembly-orders.index', $this->data);
    }

    public function create()
    {
        $companyId = company() ? company()->id : 1;
        $this->products = Product::where('company_id', $companyId)->get();
        $this->warehouses = Warehouse::where('company_id', $companyId)->where('is_active', 1)->get();
        $this->nextAssemblyNumber = AssemblyOrder::nextAssemblyNumber();

        return view('assembly-orders.create', $this->data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'raw_product_id' => 'required|array|min:1',
            'raw_product_id.*' => 'required|exists:products,id',
            'quantity_required' => 'required|array|min:1',
            'quantity_required.*' => 'required|numeric|min:0.01',
        ]);

        $items = [];
        foreach ($request->raw_product_id as $key => $rawId) {
            $qty = (float) $request->quantity_required[$key];
            if ($qty > 0) {
                $items[] = [
                    'raw_product_id' => $rawId,
                    'quantity' => $qty,
                ];
            }
        }

        if (empty($items)) {
            return Reply::error('Please select at least one assembly part with a valid quantity.');
        }

        try {
            $service = app(AssemblyLineService::class);
            $data = [
                'warehouse_id' => $request->warehouse_id,
                'target_product_id' => $request->target_product_id ?: null,
                'quantity_to_assemble' => $request->quantity_to_assemble ?: 0.00,
                'notes' => $request->notes,
            ];

            $assembly = $service->processDirectWithdrawal($data, $items);

            return Reply::successWithData('Assembly Stock Withdrawn & Inventory Deducted successfully.', [
                'redirectUrl' => route('assembly-orders.show', $assembly->id),
            ]);
        } catch (Exception $e) {
            return Reply::error($e->getMessage());
        }
    }

    public function show($id)
    {
        $companyId = company() ? company()->id : 1;
        $this->assemblyOrder = AssemblyOrder::where('company_id', $companyId)
            ->with(['targetProduct', 'warehouse', 'creator', 'items.rawProduct', 'faultLogs.product', 'faultLogs.user'])
            ->findOrFail($id);

        return view('assembly-orders.show', $this->data);
    }

    public function complete(Request $request, $id)
    {
        $companyId = company() ? company()->id : 1;
        $assembly = AssemblyOrder::where('company_id', $companyId)->findOrFail($id);

        try {
            $service = app(AssemblyLineService::class);
            $actualUsage = (array) $request->actual_usage;
            $service->completeAssembly($assembly, $actualUsage);

            return Reply::success('Assembly Job completed successfully. Finished product sent to Warehouse Intake.');
        } catch (Exception $e) {
            return Reply::error($e->getMessage());
        }
    }

    public function logFault(Request $request, $id)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'fault_quantity' => 'required|numeric|min:0.01',
            'reason' => 'nullable|string|max:500',
        ]);

        $companyId = company() ? company()->id : 1;
        $assembly = AssemblyOrder::where('company_id', $companyId)->findOrFail($id);

        try {
            $service = app(AssemblyLineService::class);
            $service->logFaultyPart($assembly, (int) $request->product_id, (float) $request->fault_quantity, $request->reason);

            return Reply::success('Faulty part logged and moved to Fault Stock successfully.');
        } catch (Exception $e) {
            return Reply::error($e->getMessage());
        }
    }
}
