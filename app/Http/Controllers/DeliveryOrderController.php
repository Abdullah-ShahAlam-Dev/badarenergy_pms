<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\DeliveryOrder;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

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
            ])->select('delivery_orders.*');
 
            return DataTables::of($model)
                ->addColumn('action', function ($row) {
                    $buttons = '';
                    if (!$row->invoice_id && $row->source_type === 'order' && $row->source_id) {
                        $buttons .= '<a href="' . route('invoices.create') . '?order=' . $row->source_id . '" class="btn btn-primary btn-sm rounded mr-2">Create Invoice</a>';
                    } else {
                        $buttons .= '<span class="badge badge-success px-2 py-1 mr-2">Invoiced</span>';
                    }
                    
                    $buttons .= '<a href="' . route('delivery-orders.show', $row->id) . '" target="_blank" class="btn btn-secondary btn-sm rounded"><i class="fa fa-print mr-1"></i>Print DO</a>';
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
        $this->deliveryOrder = DeliveryOrder::findOrFail($id);
        $this->employees = User::allEmployees(null, true);
        
        return view('delivery-orders.edit', $this->data);
    }

    public function update(Request $request, $id)
    {
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
            'stockTransfer.items.serials.serial'
        ])->findOrFail($id);
        $this->invoiceSetting = invoice_setting();
        $this->printView = true;
        return view('delivery-orders.pdf', $this->data);
    }
}
