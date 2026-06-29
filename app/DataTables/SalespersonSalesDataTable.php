<?php

namespace App\DataTables;

use App\Models\Invoice;
use App\Services\SalesReportsService;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Illuminate\Support\Facades\DB;

class SalespersonSalesDataTable extends BaseDataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addIndexColumn()
            ->addColumn('formatted_amount', function ($row) {
                return currency_format($row->amount);
            })
            ->addColumn('formatted_collections', function ($row) {
                return currency_format($row->collections ?? 0.00);
            })
            ->addColumn('formatted_recovery_ratio', function ($row) {
                $ratio = $row->amount > 0 ? (($row->collections ?? 0.00) / $row->amount) * 100 : 0.00;
                return round($ratio, 2) . '%';
            });
    }

    public function query(Invoice $model)
    {
        $service = new SalesReportsService();
        $filters = request()->only([
            'startDate', 'endDate', 'dealerId', 'salespersonId', 'warehouseId', 'productId', 'modelId', 'city', 'invoiceStatus'
        ]);

        $query = $model->query()
            ->join('client_details as cd', 'cd.user_id', '=', 'invoices.client_id')
            ->join('users as salesperson', 'salesperson.id', '=', 'cd.salesperson_id')
            ->select([
                'cd.salesperson_id',
                'salesperson.name as salesperson',
                DB::raw('COUNT(invoices.id) as sales_count'),
                DB::raw('SUM(invoices.total) as amount'),
                DB::raw("
                    (
                        SELECT SUM(payments.amount) 
                        FROM payments 
                        JOIN client_details as cd_pay ON cd_pay.user_id = payments.customer_id
                        WHERE payments.status = 'complete' 
                        AND cd_pay.salesperson_id = cd.salesperson_id
                    ) as collections
                ")
            ]);

        $query = $service->applyInvoiceFilters($query, $filters);
        
        $query->groupBy('cd.salesperson_id', 'salesperson.name');

        return $query;
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('salesperson-sales-table')
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
                    window.LaravelDataTables["salesperson-sales-table"].buttons().container()
                     .appendTo("#table-actions")
                 }'
            ])
            ->buttons(
                Button::make(['extend' => 'excel', 'text' => '<i class="fa fa-file-export"></i> Excel']),
                Button::make(['extend' => 'csv', 'text' => '<i class="fa fa-file-csv"></i> CSV']),
                Button::make(['extend' => 'pdf', 'text' => '<i class="fa fa-file-pdf"></i> PDF'])
            );
    }

    protected function getColumns()
    {
        return [
            '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => false, 'title' => '#'],
            'salesperson' => ['data' => 'salesperson', 'name' => 'salesperson.name', 'title' => 'Salesperson Name'],
            'sales_count' => ['data' => 'sales_count', 'name' => 'sales_count', 'title' => 'Invoice Count', 'searchable' => false],
            'formatted_amount' => ['data' => 'formatted_amount', 'name' => 'amount', 'title' => 'Total Sales Amount', 'searchable' => false],
            'formatted_collections' => ['data' => 'formatted_collections', 'name' => 'collections', 'title' => 'Total Collections', 'searchable' => false],
            'formatted_recovery_ratio' => ['data' => 'formatted_recovery_ratio', 'name' => 'recovery_ratio', 'title' => 'Recovery Ratio (%)', 'searchable' => false]
        ];
    }
}
