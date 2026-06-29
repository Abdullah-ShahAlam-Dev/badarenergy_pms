<?php

namespace App\DataTables;

use App\Models\StockTransfer;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Carbon\Carbon;

class StockTransferDataTable extends BaseDataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addIndexColumn()
            ->addColumn('formatted_date', function ($row) {
                return Carbon::parse($row->created_at)->format($this->company->date_format);
            })
            ->addColumn('formatted_status', function ($row) {
                $statusColors = [
                    StockTransfer::STATUS_DRAFT => 'secondary',
                    StockTransfer::STATUS_PENDING_APPROVAL => 'warning',
                    StockTransfer::STATUS_APPROVED => 'primary',
                    StockTransfer::STATUS_DISPATCHED => 'info',
                    StockTransfer::STATUS_IN_TRANSIT => 'info',
                    StockTransfer::STATUS_PARTIALLY_RECEIVED => 'warning',
                    StockTransfer::STATUS_RECEIVED => 'success',
                    StockTransfer::STATUS_CANCELLED => 'dark',
                    StockTransfer::STATUS_REJECTED => 'danger'
                ];
                $color = $statusColors[$row->status] ?? 'secondary';
                return '<span class="badge badge-' . $color . '">' . ucfirst(str_replace('_', ' ', $row->status)) . '</span>';
            })
            ->addColumn('action', function ($row) {
                $btn = '<div class="btn-group" role="group">';
                $btn .= '<a href="' . route('stock-transfers.show', $row->id) . '" class="btn btn-xs btn-primary"><i class="fa fa-eye"></i> View</a>';
                
                if ($row->status === StockTransfer::STATUS_DRAFT && user()->permission('edit_stock_transfer') != 'none') {
                    $btn .= '<a href="' . route('stock-transfers.edit', $row->id) . '" class="btn btn-xs btn-warning ml-1"><i class="fa fa-edit"></i> Edit</a>';
                }
                
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['formatted_status', 'action']);
    }

    public function query(StockTransfer $model)
    {
        $query = $model->query()
            ->join('warehouses as source', 'source.id', '=', 'stock_transfers.source_warehouse_id')
            ->join('warehouses as dest', 'dest.id', '=', 'stock_transfers.destination_warehouse_id')
            ->join('users as creator', 'creator.id', '=', 'stock_transfers.created_by')
            ->select([
                'stock_transfers.id',
                'stock_transfers.transfer_number',
                'stock_transfers.challan_number',
                'source.name as source_warehouse',
                'dest.name as destination_warehouse',
                'creator.name as created_by_name',
                'stock_transfers.status',
                'stock_transfers.created_at'
            ])
            ->where('stock_transfers.company_id', company()->id);

        // Apply filters
        if (request()->has('startDate') && request()->get('startDate') !== '') {
            $query->whereDate('stock_transfers.created_at', '>=', request()->get('startDate'));
        }
        if (request()->has('endDate') && request()->get('endDate') !== '') {
            $query->whereDate('stock_transfers.created_at', '<=', request()->get('endDate'));
        }
        if (request()->has('source_warehouse_id') && request()->get('source_warehouse_id') !== 'all') {
            $query->where('stock_transfers.source_warehouse_id', request()->get('source_warehouse_id'));
        }
        if (request()->has('destination_warehouse_id') && request()->get('destination_warehouse_id') !== 'all') {
            $query->where('stock_transfers.destination_warehouse_id', request()->get('destination_warehouse_id'));
        }
        if (request()->has('status') && request()->get('status') !== 'all') {
            $query->where('stock_transfers.status', request()->get('status'));
        }

        return $query;
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('stock-transfers-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->destroy(true)
            ->responsive(true)
            ->serverSide(true)
            ->processing(true)
            ->dom($this->domHtml)
            ->language(__('app.datatable'))
            ->parameters([
                'initComplete' => 'function () {
                    window.LaravelDataTables["stock-transfers-table"].buttons().container()
                     .appendTo("#table-actions")
                 }'
            ])
            ->buttons(
                Button::make(['extend' => 'excel', 'text' => '<i class="fa fa-file-export"></i> Excel']),
                Button::make(['extend' => 'csv', 'text' => '<i class="fa fa-file-csv"></i> CSV']),
                Button::make(['extend' => 'pdf', 'text' => '<i class="fa fa-file-pdf"></i> PDF']),
                Button::make(['extend' => 'print', 'text' => '<i class="fa fa-print"></i> Print'])
            );
    }

    protected function getColumns()
    {
        return [
            '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => false, 'title' => '#'],
            'transfer_number' => ['data' => 'transfer_number', 'name' => 'stock_transfers.transfer_number', 'title' => 'Transfer No.'],
            'source_warehouse' => ['data' => 'source_warehouse', 'name' => 'source.name', 'title' => 'Source Warehouse'],
            'destination_warehouse' => ['data' => 'destination_warehouse', 'name' => 'dest.name', 'title' => 'Destination Warehouse'],
            'created_by_name' => ['data' => 'created_by_name', 'name' => 'creator.name', 'title' => 'Created By'],
            'formatted_status' => ['data' => 'formatted_status', 'name' => 'stock_transfers.status', 'title' => 'Status'],
            'formatted_date' => ['data' => 'formatted_date', 'name' => 'stock_transfers.created_at', 'title' => 'Created Date'],
            'action' => ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false]
        ];
    }
}
