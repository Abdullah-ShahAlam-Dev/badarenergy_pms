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
                'dispatcher', 
                'stockTransfer.sourceWarehouse', 
                'stockTransfer.destinationWarehouse'
            ])->select('delivery_orders.*');

            return DataTables::of($model)
                ->addColumn('action', function ($row) {
                    $action = '<div class="task_view">
                        <div class="dropdown">
                            <a class="align-items-center d-flex justify-content-center dropdown-toggle f-16 text-lightest" href="javascript:;" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fa fa-ellipsis-h"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right">';
                    
                    $action .= '<a class="dropdown-item open-edit-modal" href="javascript:;" data-do-id="' . $row->id . '"><i class="fa fa-edit mr-2"></i>Edit / Dispatch</a>';
                    $action .= '<a class="dropdown-item" href="' . route('delivery-orders.show', $row->id) . '" target="_blank"><i class="fa fa-print mr-2"></i>Print DO Slip</a>';
                    
                    $action .= '</div></div></div>';
                    return $action;
                })
                ->editColumn('id', function ($row) {
                    return '#' . $row->id;
                })
                ->editColumn('invoice_number', function ($row) {
                    if ($row->source_type === 'transfer' && $row->transfer_id) {
                        return '<a href="' . route('stock-transfers.show', $row->transfer_id) . '" class="text-darkest-grey font-weight-bold">' . ($row->stockTransfer->transfer_number ?? ('TRF-' . $row->transfer_id)) . '</a> <span class="badge badge-secondary">Transfer</span>';
                    }
                    return $row->invoice_id ? '<a href="' . route('invoices.show', $row->invoice_id) . '" class="text-darkest-grey font-weight-bold">' . ($row->invoice->invoice_number ?? ('#'.$row->invoice_id)) . '</a>' : '--';
                })
                ->editColumn('client_name', function ($row) {
                    if ($row->source_type === 'transfer' && $row->stockTransfer) {
                        $src = $row->stockTransfer->sourceWarehouse->name ?? 'Source';
                        $dest = $row->stockTransfer->destinationWarehouse->name ?? 'Dest';
                        return 'WMS: ' . $src . ' &rarr; ' . $dest;
                    }
                    return $row->invoice->client->name ?? '--';
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
            'dispatcher',
            'stockTransfer.sourceWarehouse',
            'stockTransfer.destinationWarehouse',
            'stockTransfer.items.product',
            'stockTransfer.items.serials.serial'
        ])->findOrFail($id);
        return view('delivery-orders.pdf', $this->data);
    }
}
