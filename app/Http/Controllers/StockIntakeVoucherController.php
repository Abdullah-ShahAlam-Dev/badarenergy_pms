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
            abort_403(!in_array('admin', user_roles()) && user()->permission('approve_stock_intake') == 'none');

            return $next($request);
        });
    }

    /**
     * Display a listing of stock intake vouchers.
     */
    public function index(StockIntakeVoucherDataTable $dataTable)
    {
        $this->viewPermission = user()->permission('approve_stock_intake');
        $this->addPermission = user()->permission('add_inventory');

        abort_403($this->viewPermission == 'none' && !in_array('admin', user_roles()));

        return $dataTable->render('stock-intakes.index', $this->data);
    }

    /**
     * Show the form for creating a new stock intake.
     */
    public function create()
    {
        $this->addPermission = user()->permission('add_inventory');
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
        $this->addPermission = user()->permission('add_inventory');
        abort_403($this->addPermission != 'all' && !in_array('admin', user_roles()));

        $intakeDate = $request->intake_date;
        if ($intakeDate) {
            try {
                $intakeDate = \Carbon\Carbon::createFromFormat(company()->date_format, $intakeDate)->format('Y-m-d');
            } catch (\Exception $e) {
                $intakeDate = \Carbon\Carbon::parse($intakeDate)->format('Y-m-d');
            }
        }

        $companyId = company() ? company()->id : 1;
        $shipmentId = $request->shipment_id;

        if ($shipmentId === 'create_new' || (!empty($request->container_number) && (empty($shipmentId) || $shipmentId === 'create_new'))) {
            $generator = app(\App\Services\ShipmentGeneratorService::class);
            $shipment = new \App\Models\Shipment();
            $shipment->company_id = $companyId;
            $shipment->shipment_number = $generator->generate($companyId);
            $shipment->container_number = strip_tags($request->container_number);
            $shipment->bill_of_lading = strip_tags($request->bill_of_lading);
            $shipment->manufacturing_ref = strip_tags($request->manufacturing_ref);
            $shipment->port_of_origin = strip_tags($request->port_of_origin);
            $shipment->port_of_discharge = strip_tags($request->port_of_discharge);
            if ($request->eta) {
                try {
                    $shipment->eta = \Carbon\Carbon::createFromFormat(company()->date_format, $request->eta)->format('Y-m-d');
                } catch (\Exception $e) {
                    $shipment->eta = \Carbon\Carbon::parse($request->eta)->format('Y-m-d');
                }
            }
            $shipment->status = $request->shipment_status ?: 'arrived';
            $shipment->remarks = strip_tags($request->shipment_remarks);
            $shipment->save();

            $shipmentId = $shipment->id;
        }

        $data = $request->only(['warehouse_id', 'intake_type', 'remarks']);
        $data['shipment_id'] = ($shipmentId && $shipmentId !== 'create_new') ? $shipmentId : null;
        $data['intake_date'] = $intakeDate;
        $data['intake_type'] = $request->intake_type ?: 'direct';

        $service = app(StockIntakeService::class);
        $service->createVoucher($data, $request->items);

        return Reply::successWithData('Shipment and Stock Intake Voucher created successfully.', [
            'redirectUrl' => route('stock-intakes.index'),
        ]);
    }

    /**
     * Display details of a stock intake voucher.
     */
    public function show($id)
    {
        $this->viewPermission = user()->permission('approve_stock_intake');
        abort_403($this->viewPermission == 'none' && !in_array('admin', user_roles()));

        $companyId = company() ? company()->id : 1;
        $this->voucher = StockIntakeVoucher::where('company_id', $companyId)
            ->with(['items.product', 'items.batch', 'warehouse', 'shipment', 'creator', 'serials.product'])
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
        $this->deletePermission = user()->permission('delete_inventory');
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
    public function printBarcodes(Request $request, $id)
    {
        $this->viewPermission = user()->permission('approve_stock_intake');
        abort_403($this->viewPermission == 'none' && !in_array('admin', user_roles()));

        $companyId = company() ? company()->id : 1;
        $productId = $request->product_id;

        $voucher = StockIntakeVoucher::where('company_id', $companyId)
            ->with(['serials' => function ($query) use ($productId) {
                if ($productId) {
                    $query->where('product_id', $productId);
                }
                $query->with('product');
            }])
            ->findOrFail($id);

        $factory = resolve(\App\Services\Barcode\BarcodeDriverFactory::class);
        $code128Driver = $factory->make('code128');
        $qrCodeDriver = $factory->make('qrcode');

        $termsUrl = \App\Facades\WorkflowConfig::get('barcode', 'terms_url', 'https://badarenergy.com/terms', $companyId);
        $barcodes = [];

        foreach ($voucher->serials as $serial) {
            $barcodes[] = [
                'serial_number' => $serial->serial_number,
                'product_name' => $serial->product ? $serial->product->name : 'N/A',
                'barcode_svg' => $code128Driver->generate($serial->serial_number),
                'qrcode_svg' => $qrCodeDriver->generate($termsUrl),
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
        $this->viewPermission = user()->permission('approve_stock_intake');
        abort_403($this->viewPermission == 'none' && !in_array('admin', user_roles()));

        $companyId = company() ? company()->id : 1;
        $serial = \App\Models\ProductSerial::where('company_id', $companyId)
            ->where('intake_voucher_id', $voucherId)
            ->with(['product'])
            ->findOrFail($serialId);

        $factory = resolve(\App\Services\Barcode\BarcodeDriverFactory::class);
        $code128Driver = $factory->make('code128');
        $qrCodeDriver = $factory->make('qrcode');

        $termsUrl = \App\Facades\WorkflowConfig::get('barcode', 'terms_url', 'https://badarenergy.com/terms', $companyId);

        $barcode = [
            'serial_number' => $serial->serial_number,
            'product_name' => $serial->product ? $serial->product->name : 'N/A',
            'barcode_svg' => $code128Driver->generate($serial->serial_number),
            'qrcode_svg' => $qrCodeDriver->generate($termsUrl),
        ];

        return view('stock-intakes.barcodes', [
            'barcodes' => [$barcode],
            'voucher_number' => $serial->serial_number
        ]);
    }
}
