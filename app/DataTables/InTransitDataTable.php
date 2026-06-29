<?php

namespace App\DataTables;

use App\Models\StockTransfer;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Carbon\Carbon;

class InTransitDataTable extends BaseDataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addIndexColumn()
            ->addColumn('formatted_date', function ($row) {
                return Carbon::parse($row->dispatched_at)->format($this->company->date_format);
            })
            ->addColumn('formatted_status', function ($row) {
                return '<span class="badge badge-info">' . ucfirst(str_replace('_', ' ', $row->status)) . '</span>';
            })
            ->addColumn('action', function ($row) {
                return '<a href="' . route('stock-transfers.show', $row->id) . '" class="btn btn-xs btn-primary"><i class="fa fa-eye"></i> View</a>';
            })
            ->rawColumns(['formatted_status', 'action']);
    }

    public function query(StockTransfer $model)
    {
        return $model->query()
            ->join('warehouses as source', 'source.id', '=', 'stock_transfers.source_warehouse_id')
            ->join('warehouses as dest', 'dest.id', '=', 'stock_transfers.destination_warehouse_id')
            ->select([
                'stock_transfers.id',
                'stock_transfers.transfer_number',
                'source.name as source_warehouse',
                'dest.name as destination_warehouse',
                'stock_transfers.vehicle_number',
                'stock_transfers.driver_name',
                'stock_transfers.status',
                'stock_transfers.dispatched_at'
            ])
            ->where('stock_transfers.company_id', company()->id)
            ->whereIn('stock_transfers.status', [StockTransfer::STATUS_DISPATCHED, StockTransfer::STATUS_IN_TRANSIT, StockTransfer::STATUS_PARTIALLY_RECEIVED]);
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('intransit-transfers-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->destroy(true)
            ->responsive(true)
            ->serverSide(true)
            ->processing(true)
            ->dom($this->domHtml)
            ->language(__('app.datatable'));
    }

    protected function getColumns()
    {
        return [
            '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => false, 'title' => '#'],
            'transfer_number' => ['data' => 'transfer_number', 'name' => 'stock_transfers.transfer_number', 'title' => 'Transfer No.'],
            'source_warehouse' => ['data' => 'source_warehouse', 'name' => 'source.name', 'title' => 'Source Warehouse'],
            'destination_warehouse' => ['data' => 'destination_warehouse', 'name' => 'dest.name', 'title' => 'Destination Warehouse'],
            'vehicle_number' => ['data' => 'vehicle_number', 'name' => 'stock_transfers.vehicle_number', 'title' => 'Vehicle No.'],
            'driver_name' => ['data' => 'driver_name', 'name' => 'stock_transfers.driver_name', 'title' => 'Driver Name'],
            'formatted_status' => ['data' => 'formatted_status', 'name' => 'stock_transfers.status', 'title' => 'Status'],
            'formatted_date' => ['data' => 'formatted_date', 'name' => 'stock_transfers.dispatched_at', 'title' => 'Dispatch Date'],
            'action' => ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false]
        ];
    }
}
