<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\DeliveryOrder;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class DeliveryOrderController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'Delivery Orders';
        $this->middleware(function ($request, $next) {
            if (in_array('client', user_roles())) {
                abort(403);
            }
            abort_403(!in_array('admin', user_roles()) && user()->permission('view_inventory') == 'none');
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $model = DeliveryOrder::with([
                'invoice.client', 
                'invoice.order.addedBy',
                'order.client',
                'order.addedBy',
                'dispatcher', 
                'stockTransfer.sourceWarehouse', 
                'stockTransfer.destinationWarehouse'
            ])->select('delivery_orders.*')
            ->orderBy('delivery_orders.id', 'desc');
 
            return DataTables::of($model)
                ->addColumn('action', function ($row) {
                    $buttons = '';
                    if ($row->status === 'pending') {
                        $buttons .= '<button type="button" class="btn btn-primary btn-sm rounded open-scan-modal mr-2" data-do-id="' . $row->id . '"><i class="fa fa-barcode mr-1"></i>Scan</button>';
                    } else {
                        $buttons .= '<a href="' . route('delivery-orders.show', $row->id) . '" target="_blank" class="btn btn-secondary btn-sm rounded mr-2"><i class="fa fa-print mr-1"></i>Print DO</a>';
                    }
                    return $buttons;
                })
                ->editColumn('id', function ($row) {
                    return $row->delivery_order_number;
                })
                ->editColumn('invoice_number', function ($row) {
                    if ($row->source_type === 'transfer' && $row->transfer_id) {
                        return '<a href="' . route('stock-transfers.show', $row->transfer_id) . '" class="text-darkest-grey font-weight-bold">' . ($row->stockTransfer->transfer_number ?? ('TRF-' . $row->transfer_id)) . '</a> <span class="badge badge-secondary">Transfer</span>';
                    }
                    if ($row->source_type === 'order' && $row->source_id) {
                        return '<a href="' . route('orders.show', $row->source_id) . '" class="text-darkest-grey font-weight-bold">' . ($row->order->order_number ?? ('#'.$row->source_id)) . '</a> <span class="badge badge-info">Order</span>';
                    }
                    return $row->invoice_id ? '<a href="' . route('invoices.show', $row->invoice_id) . '" class="text-darkest-grey font-weight-bold">' . ($row->invoice->invoice_number ?? ('#'.$row->invoice_id)) . '</a>' : '--';
                })
                ->editColumn('client_name', function ($row) {
                    if ($row->source_type === 'transfer' && $row->stockTransfer) {
                        $src = $row->stockTransfer->sourceWarehouse->name ?? 'Source';
                        $dest = $row->stockTransfer->destinationWarehouse->name ?? 'Dest';
                        return 'WMS: ' . $src . ' &rarr; ' . $dest;
                    }
                    if ($row->source_type === 'order' && $row->order) {
                        return $row->order->client->name ?? '--';
                    }
                    return $row->invoice->client->name ?? '--';
                })
                ->addColumn('salesperson', function ($row) {
                    if ($row->source_type === 'order' && $row->order && $row->order->addedBy) {
                        return $row->order->addedBy->name;
                    }
                    if ($row->invoice && $row->invoice->order && $row->invoice->order->addedBy) {
                        return $row->invoice->order->addedBy->name;
                    }
                    return '--';
                })
                ->editColumn('issue_date', function ($row) {
                    return $row->issue_date ? $row->issue_date->format(company()->date_format) : '--';
                })
                ->editColumn('dispatcher', function ($row) {
                    return $row->dispatcher ? $row->dispatcher->name : '<span class="text-lightest">Not Assigned</span>';
                })
                ->editColumn('status', function ($row) {
                    $style = '';
                    switch ($row->status) {
                        case 'pending':
                            $style = 'badge-warning';
                            break;
                        case 'dispatched':
                            $style = 'badge-info';
                            break;
                        case 'delivered':
                            $style = 'badge-success';
                            break;
                        case 'cancelled':
                            $style = 'badge-danger';
                            break;
                    }
                    return '<span class="badge ' . $style . '">' . ucfirst($row->status) . '</span>';
                })
                ->rawColumns(['action', 'invoice_number', 'dispatcher', 'status'])
                ->make(true);
        }

        return view('delivery-orders.index', $this->data);
    }

    public function edit($id)
    {
        abort_403(user()->permission('manage_dispatch') == 'none' && !in_array('admin', user_roles()));

        $this->deliveryOrder = DeliveryOrder::findOrFail($id);
        $this->employees = User::allEmployees(null, true);
        
        return view('delivery-orders.edit', $this->data);
    }

    public function update(Request $request, $id)
    {
        abort_403(user()->permission('manage_dispatch') == 'none' && !in_array('admin', user_roles()));

        $request->validate([
            'dispatcher_id' => 'nullable|exists:users,id',
            'status' => 'required|in:pending,dispatched,delivered,cancelled',
        ]);

        $deliveryOrder = DeliveryOrder::findOrFail($id);
        $deliveryOrder->update([
            'dispatcher_id' => $request->dispatcher_id,
            'status' => $request->status,
        ]);

        return Reply::success('Delivery Order updated successfully.');
    }

    public function show($id)
    {
        $this->deliveryOrder = DeliveryOrder::with([
            'invoice.items.product', 
            'invoice.client', 
            'order.items.product',
            'order.client',
            'order.addedBy',
            'dispatcher',
            'stockTransfer.sourceWarehouse',
            'stockTransfer.destinationWarehouse',
            'stockTransfer.items.product',
            'stockTransfer.items.serials.serial',
            'lines.lineSerials.serial'
        ])->findOrFail($id);
        $this->invoiceSetting = invoice_setting();
        $this->printView = true;
        return view('delivery-orders.pdf', $this->data);
    }

    public function validateSerial(Request $request, $id)
    {
        abort_403(user()->permission('manage_dispatch') == 'none' && !in_array('admin', user_roles()));

        $do = DeliveryOrder::findOrFail($id);
        $serialNumber = trim($request->serial_number);

        if (empty($serialNumber)) {
            return Reply::error('Serial number cannot be empty.');
        }

        $companyId = $do->company_id ?? (company() ? company()->id : 1);
        $serial = \App\Models\ProductSerial::where('company_id', $companyId)
            ->where('serial_number', $serialNumber)
            ->with('product')
            ->first();

        if (!$serial) {
            return Reply::error("Serial number [{$serialNumber}] does not exist in the system database.");
        }

        if ($serial->status !== \App\Enums\SerialStatus::AVAILABLE->value) {
            return Reply::error("Serial number [{$serialNumber}] is currently not available. Current status: " . ucfirst($serial->status));
        }

        $do->load(['lines.product']);
        $matchedLine = $do->lines->first(function ($line) use ($serial) {
            return $line->product_id == $serial->product_id;
        });

        if (!$matchedLine) {
            return Reply::error("Product [{$serial->product->name}] does not belong to this Delivery Order.");
        }

        $warehouseId = $do->warehouse_id ?? $this->resolveWarehouseId($do);
        if ($warehouseId && $serial->warehouse_id != $warehouseId) {
            $expectedWarehouse = \App\Models\Warehouse::find($warehouseId);
            $actualWarehouse = \App\Models\Warehouse::find($serial->warehouse_id);
            $expectedName = $expectedWarehouse ? $expectedWarehouse->name : 'Expected';
            $actualName = $actualWarehouse ? $actualWarehouse->name : 'Actual';
            return Reply::error("Serial [{$serialNumber}] belongs to warehouse '{$actualName}' but this DO requires warehouse '{$expectedName}'.");
        }

        return Reply::dataOnly([
            'status' => 'success',
            'serial_id' => $serial->id,
            'product_id' => $serial->product_id,
            'product_name' => $serial->product->name,
            'serial_number' => $serial->serial_number,
        ]);
    }

    public function submitScannedSerials(Request $request, $id)
    {
        abort_403(user()->permission('manage_dispatch') == 'none' && !in_array('admin', user_roles()));

        $do = DeliveryOrder::findOrFail($id);
        $scannedSerials = (array) $request->serials;

        $do->load(['lines.product']);
        $companyId = $do->company_id ?? (company() ? company()->id : 1);
        $serialsInDb = \App\Models\ProductSerial::where('company_id', $companyId)
            ->whereIn('serial_number', $scannedSerials)
            ->get()
            ->keyBy('serial_number');

        $lineSerialsMapping = [];

        foreach ($do->lines as $line) {
            $isSerialized = $line->product ? $line->product->is_serialized : false;
            if (!$isSerialized) {
                continue;
            }

            $expectedQty = (int) $line->quantity_requested;
            $productScans = [];
            foreach ($scannedSerials as $sNo) {
                if (isset($serialsInDb[$sNo]) && $serialsInDb[$sNo]->product_id == $line->product_id) {
                    $productScans[] = $serialsInDb[$sNo]->id;
                }
            }

            if (count($productScans) !== $expectedQty) {
                return Reply::error("Product [{$line->product->name}] requires {$expectedQty} serials, but only " . count($productScans) . " were scanned.");
            }

            $lineSerialsMapping[$line->id] = $productScans;
        }

        try {
            DB::beginTransaction();

            $do->lockForUpdate();

            $doService = resolve(\App\Services\DeliveryOrderService::class);
            $doService->releaseReservations($do);
            $doService->reserveSerials($do, $lineSerialsMapping);
            $doService->dispatch($do, auth()->id());

            DB::commit();

            return Reply::success('Delivery Order successfully scanned and dispatched.');
        } catch (\Exception $e) {
            DB::rollBack();
            return Reply::error($e->getMessage());
        }
    }

    public function getDODetails($id)
    {
        $do = DeliveryOrder::with(['lines.product'])->findOrFail($id);
        return response()->json([
            'id' => $do->id,
            'delivery_order_number' => $do->delivery_order_number,
            'lines' => $do->lines->map(function ($line) {
                return [
                    'id' => $line->id,
                    'product_id' => $line->product_id,
                    'product_name' => $line->product->name,
                    'is_serialized' => $line->product->is_serialized,
                    'quantity_requested' => $line->quantity_requested,
                ];
            })
        ]);
    }

    protected function resolveWarehouseId($do): ?int
    {
        if (isset($do->warehouse_id) && $do->warehouse_id) {
            return $do->warehouse_id;
        }

        if ($do->invoice_id && $do->invoice) {
            return $do->invoice->warehouse_id;
        }

        if ($do->transfer_id && $do->stockTransfer) {
            return $do->stockTransfer->from_warehouse_id;
        }

        $firstLine = $do->lines()->first();
        if ($firstLine) {
            $firstSerialLink = $firstLine->lineSerials()->first();
            if ($firstSerialLink && $firstSerialLink->serial) {
                return $firstSerialLink->serial->warehouse_id;
            }
        }

        return null;
    }
}
