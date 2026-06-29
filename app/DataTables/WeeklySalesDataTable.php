<?php

namespace App\DataTables;

use App\Models\Invoice;
use App\Services\SalesReportsService;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class WeeklySalesDataTable extends BaseDataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addIndexColumn()
            ->addColumn('week', function ($row) {
                $start = Carbon::parse($row->week_start)->startOfWeek();
                $end = Carbon::parse($row->week_start)->endOfWeek();
                return $start->format($this->company->date_format) . ' - ' . $end->format($this->company->date_format);
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

        // Group by year and week
        $query->selectRaw("
            DATE_FORMAT(invoices.issue_date, '%x-%v') as week_num,
            MIN(invoices.issue_date) as week_start,
            COUNT(invoices.id) as sales_count,
            SUM(invoices.total) as amount,
            (
                SELECT SUM(payments.amount) 
                FROM payments 
                WHERE payments.status = 'complete' 
                AND DATE_FORMAT(payments.paid_on, '%x-%v') = DATE_FORMAT(MIN(invoices.issue_date), '%x-%v')
            ) as collections
        ")
        ->groupBy(DB::raw("DATE_FORMAT(invoices.issue_date, '%x-%v')"));

        return $query;
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('weekly-sales-table')
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
                    window.LaravelDataTables["weekly-sales-table"].buttons().container()
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
            'week' => ['data' => 'week', 'name' => 'week_start', 'title' => 'Week / Period', 'orderable' => false],
            'sales_count' => ['data' => 'sales_count', 'name' => 'sales_count', 'title' => 'Invoice Count', 'searchable' => false],
            'formatted_amount' => ['data' => 'formatted_amount', 'name' => 'amount', 'title' => 'Total Sales Amount', 'searchable' => false],
            'formatted_collections' => ['data' => 'formatted_collections', 'name' => 'collections', 'title' => 'Total Collections', 'searchable' => false],
            'formatted_outstanding' => ['data' => 'formatted_outstanding', 'name' => 'outstanding', 'title' => 'Total Outstanding', 'searchable' => false]
        ];
    }
}
