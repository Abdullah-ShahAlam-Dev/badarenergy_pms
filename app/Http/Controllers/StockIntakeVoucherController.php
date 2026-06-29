<?php

namespace App\Http\Controllers;

use App\DataTables\StockIntakeVoucherDataTable;
use App\Helper\Reply;
use App\Http\Requests\StockIntake\StoreStockIntakeRequest;
use App\Models\StockIntakeVoucher;
use App\Models\Warehouse;
use App\Models\Shipment;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Services\StockIntakeService;
use App\Facades\WorkflowConfig;
use Illuminate\Http\Request;
use Exception;

class StockIntakeVoucherController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'Stock Intake Vouchers';
        $this->activeMenu = 'stock-intakes';

        $this->middleware(function ($request, $next) {
            abort_403(!in_array('admin', user_roles()) && user()->permission('view_stock_intake') == 'none');

            return $next($request);
        });
    }

    /**
     * Display a listing of stock intake vouchers.
     */
    public function index(StockIntakeVoucherDataTable $dataTable)
    {
        $this->viewPermission = user()->permission('view_stock_intake');
        $this->addPermission = user()->permission('add_stock_intake');

        abort_403($this->viewPermission == 'none' && !in_array('admin', user_roles()));

        return $dataTable->render('stock-intakes.index', $this->data);
    }

    /**
     * Show the form for creating a new stock intake.
     */
    public function create()
    {
        $this->addPermission = user()->permission('add_stock_intake');
        abort_403($this->addPermission != 'all' && !in_array('admin', user_roles()));

        $companyId = company() ? company()->id : 1;

        $this->warehouses = Warehouse::where('company_id', $companyId)->where('is_active', 1)->get();
        $this->shipments = Shipment::where('company_id', $companyId)->whereNotIn('status', ['cancelled', 'completed'])->get();
        $this->products = Product::where('company_id', $companyId)->where('allow_purchase', 1)->get();
        $this->batches = ProductBatch::where('company_id', $companyId)->get();

        $this->pageTitle = 'Create Stock Intake Voucher';

        return view('stock-intakes.create', $this->data);
    }

    /**
     * Store a newly created stock intake voucher.
     */
    public function store(StoreStockIntakeRequest $request)
    {
        $this->addPermission = user()->permission('add_stock_intake');
        abort_403($this->addPermission != 'all' && !in_array('admin', user_roles()));

        $intakeDate = $request->intake_date;
        if ($intakeDate) {
            try {
                $intakeDate = \Carbon\Carbon::createFromFormat(company()->date_format, $intakeDate)->format('Y-m-d');
            } catch (\Exception $e) {
                $intakeDate = \Carbon\Carbon::parse($intakeDate)->format('Y-m-d');
            }
        }

        $data = $request->only(['warehouse_id', 'shipment_id', 'remarks']);
        $data['intake_date'] = $intakeDate;

        $service = app(StockIntakeService::class);
        $service->createVoucher($data, $request->items);

        return Reply::successWithData(__('messages.recordSaved'), [
            'redirectUrl' => route('stock-intakes.index'),
        ]);
    }

    /**
     * Display details of a stock intake voucher.
     */
    public function show($id)
    {
        $this->viewPermission = user()->permission('view_stock_intake');
        abort_403($this->viewPermission == 'none' && !in_array('admin', user_roles()));

        $companyId = company() ? company()->id : 1;
        $this->voucher = StockIntakeVoucher::where('company_id', $companyId)
            ->with(['items.product', 'items.batch', 'warehouse', 'shipment', 'creator', 'serials'])
            ->findOrFail($id);

        $this->approvePermission = user()->permission('approve_stock_intake');

        $this->pageTitle = 'Stock Intake Voucher - ' . $this->voucher->voucher_number;

        return view('stock-intakes.show', $this->data);
    }

    /**
     * Approve and post a stock intake voucher.
     */
    public function approve($id)
    {
        $this->approvePermission = user()->permission('approve_stock_intake');
        abort_403($this->approvePermission != 'all' && !in_array('admin', user_roles()));

        $companyId = company() ? company()->id : 1;
        $voucher = StockIntakeVoucher::where('company_id', $companyId)->findOrFail($id);

        $service = app(StockIntakeService::class);
        $service->approveVoucher($voucher);

        return Reply::success('Stock Intake Voucher approved and inventory updated successfully.');
    }

    /**
     * Remove the specified stock intake voucher.
     */
    public function destroy($id)
    {
        $this->deletePermission = user()->permission('delete_stock_intake');
        abort_403($this->deletePermission != 'all' && !in_array('admin', user_roles()));

        $companyId = company() ? company()->id : 1;
        $voucher = StockIntakeVoucher::where('company_id', $companyId)->findOrFail($id);

        if (in_array($voucher->status, ['completed', 'approved'])) {
            return Reply::error('Approved or completed stock intake vouchers cannot be deleted.');
        }

        $voucher->items()->delete();
        $voucher->delete();

        return Reply::success(__('messages.deleteSuccess'));
    }

    /**
     * Print all barcodes for the Stock Intake Voucher.
     */
    public function printBarcodes($id)
    {
        $this->viewPermission = user()->permission('view_stock_intake');
        abort_403($this->viewPermission == 'none' && !in_array('admin', user_roles()));

        $companyId = company() ? company()->id : 1;
        $voucher = StockIntakeVoucher::where('company_id', $companyId)
            ->with(['serials.product'])
            ->findOrFail($id);

        $barcodeGenerator = resolve(\App\Services\BarcodeGeneratorService::class);
        $barcodes = [];

        foreach ($voucher->serials as $serial) {
            $barcodes[] = [
                'serial_number' => $serial->serial_number,
                'product_name' => $serial->product ? $serial->product->name : 'N/A',
                'svg' => $barcodeGenerator->generate($serial->serial_number, $companyId),
            ];
        }

        return view('stock-intakes.barcodes', [
            'barcodes' => $barcodes,
            'voucher_number' => $voucher->voucher_number
        ]);
    }

    /**
     * Print a single barcode label.
     */
    public function printSingleBarcode($voucherId, $serialId)
    {
        $this->viewPermission = user()->permission('view_stock_intake');
        abort_403($this->viewPermission == 'none' && !in_array('admin', user_roles()));

        $companyId = company() ? company()->id : 1;
        $serial = \App\Models\ProductSerial::where('company_id', $companyId)
            ->where('intake_voucher_id', $voucherId)
            ->with(['product'])
            ->findOrFail($serialId);

        $barcodeGenerator = resolve(\App\Services\BarcodeGeneratorService::class);
        $barcode = [
            'serial_number' => $serial->serial_number,
            'product_name' => $serial->product ? $serial->product->name : 'N/A',
            'svg' => $barcodeGenerator->generate($serial->serial_number, $companyId),
        ];

        return view('stock-intakes.barcodes', [
            'barcodes' => [$barcode],
            'voucher_number' => $serial->serial_number
        ]);
    }
}
