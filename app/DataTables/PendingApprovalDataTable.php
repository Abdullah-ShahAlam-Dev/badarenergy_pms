<?php

namespace App\DataTables;

use App\Models\StockTransfer;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Carbon\Carbon;

class PendingApprovalDataTable extends BaseDataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addIndexColumn()
            ->addColumn('formatted_date', function ($row) {
                return Carbon::parse($row->created_at)->format($this->company->date_format);
            })
            ->addColumn('action', function ($row) {
                return '<a href="' . route('stock-transfers.show', $row->id) . '" class="btn btn-xs btn-primary"><i class="fa fa-eye"></i> Review</a>';
            });
    }

    public function query(StockTransfer $model)
    {
        return $model->query()
            ->join('warehouses as source', 'source.id', '=', 'stock_transfers.source_warehouse_id')
            ->join('warehouses as dest', 'dest.id', '=', 'stock_transfers.destination_warehouse_id')
            ->join('users as creator', 'creator.id', '=', 'stock_transfers.created_by')
            ->select([
                'stock_transfers.id',
                'stock_transfers.transfer_number',
                'source.name as source_warehouse',
                'dest.name as destination_warehouse',
                'creator.name as created_by_name',
                'stock_transfers.created_at'
            ])
            ->where('stock_transfers.company_id', company()->id)
            ->where('stock_transfers.status', StockTransfer::STATUS_PENDING_APPROVAL);
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('pending-approval-transfers-table')
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
            'created_by_name' => ['data' => 'created_by_name', 'name' => 'creator.name', 'title' => 'Requested By'],
            'formatted_date' => ['data' => 'formatted_date', 'name' => 'stock_transfers.created_at', 'title' => 'Requested Date'],
            'action' => ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false]
        ];
    }
}
