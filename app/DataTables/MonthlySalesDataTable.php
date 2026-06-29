<?php

namespace App\DataTables;

use App\Models\Invoice;
use App\Services\SalesReportsService;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MonthlySalesDataTable extends BaseDataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addIndexColumn()
            ->addColumn('month', function ($row) {
                return Carbon::parse($row->month_start)->format('F Y');
            })
            ->addColumn('formatted_amount', function ($row) {
                return currency_format($row->amount);
            })
            ->addColumn('formatted_collections', function ($row) {
                return currency_format($row->collections ?? 0.00);
            })
            ->addColumn('formatted_outstanding', function ($row) {
                $outstanding = $row->amount - ($row->collections ?? 0.00);
                return currency_format($outstanding < 0 ? 0.00 : $outstanding);
            });
    }

    public function query(Invoice $model)
    {
        $service = new SalesReportsService();
        $filters = request()->only([
            'startDate', 'endDate', 'dealerId', 'salespersonId', 'warehouseId', 'productId', 'modelId', 'city', 'invoiceStatus'
        ]);

        $query = $model->query();
        $service->applyInvoiceFilters($query, $filters);

        // Group by year and month
        $query->selectRaw("
            DATE_FORMAT(invoices.issue_date, '%Y-%m') as month_num,
            MIN(invoices.issue_date) as month_start,
            COUNT(invoices.id) as sales_count,
            SUM(invoices.total) as amount,
            (
                SELECT SUM(payments.amount) 
                FROM payments 
                WHERE payments.status = 'complete' 
                AND DATE_FORMAT(payments.paid_on, '%Y-%m') = DATE_FORMAT(MIN(invoices.issue_date), '%Y-%m')
            ) as collections
        ")
        ->groupBy(DB::raw("DATE_FORMAT(invoices.issue_date, '%Y-%m')"));

        return $query;
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('monthly-sales-table')
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
                    window.LaravelDataTables["monthly-sales-table"].buttons().container()
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
            'month' => ['data' => 'month', 'name' => 'month_start', 'title' => 'Month / Year', 'orderable' => false],
            'sales_count' => ['data' => 'sales_count', 'name' => 'sales_count', 'title' => 'Invoice Count', 'searchable' => false],
            'formatted_amount' => ['data' => 'formatted_amount', 'name' => 'amount', 'title' => 'Total Sales Amount', 'searchable' => false],
            'formatted_collections' => ['data' => 'formatted_collections', 'name' => 'collections', 'title' => 'Total Collections', 'searchable' => false],
            'formatted_outstanding' => ['data' => 'formatted_outstanding', 'name' => 'outstanding', 'title' => 'Total Outstanding', 'searchable' => false]
        ];
    }
}
