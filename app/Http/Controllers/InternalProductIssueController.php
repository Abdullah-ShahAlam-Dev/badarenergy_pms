<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\User;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\CareOfLedger;
use App\Services\InternalProductIssueService;
use Illuminate\Http\Request;
use Exception;

class InternalProductIssueController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'Internal Product Issues';
        $this->activeMenu = 'internal-product-issues';

        $this->middleware(function ($request, $next) {
            abort_403(!in_array('admin', user_roles()) && user()->permission('add_order') == 'none');
            return $next($request);
        });
    }

    public function index()
    {
        $companyId = company() ? company()->id : 1;
        $this->issues = CareOfLedger::where('company_id', $companyId)
            ->where('transaction_type', 'product_issue')
            ->with(['careOfUser', 'order', 'deliveryOrder', 'gatePassRequest'])
            ->orderBy('id', 'desc')
            ->get();

        return view('internal-product-issues.index', $this->data);
    }

    public function create()
    {
        $companyId = company() ? company()->id : 1;
        $this->careOfUsers = User::allEmployees(null, true, 'all');
        $this->products = Product::where('company_id', $companyId)->get();
        $this->warehouses = Warehouse::where('company_id', $companyId)->where('is_active', 1)->get();

        return view('internal-product-issues.create', $this->data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'care_of_id' => 'required|exists:users,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'product_id' => 'required|array|min:1',
            'product_id.*' => 'required|exists:products,id',
            'quantity' => 'required|array|min:1',
            'quantity.*' => 'required|numeric|min:0.01',
            'unit_price' => 'nullable|array',
            'purpose' => 'nullable|string|max:500',
            'vehicle_number' => 'nullable|string|max:100',
            'driver_name' => 'nullable|string|max:100',
        ]);

        $items = [];
        foreach ($request->product_id as $key => $pId) {
            $items[] = [
                'product_id' => $pId,
                'quantity' => (float) $request->quantity[$key],
                'unit_price' => (float) ($request->unit_price[$key] ?? 0.00),
                'batch_id' => $request->batch_id[$key] ?? null,
            ];
        }

        try {
            $service = app(InternalProductIssueService::class);
            $result = $service->processProductIssue($request->only([
                'care_of_id', 'warehouse_id', 'purpose', 'vehicle_number', 'driver_name'
            ]), $items);

            return Reply::successWithData('Internal Product Issue processed successfully. Order, DO, Gate Pass, and Care Of Ledger updated.', [
                'redirectUrl' => route('internal-product-issues.index'),
            ]);
        } catch (Exception $e) {
            return Reply::error($e->getMessage());
        }
    }
}
