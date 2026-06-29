<?php

namespace App\DataTables;

use App\Models\Invoice;
use App\Services\SalesReportsService;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Illuminate\Support\Facades\DB;

class DealerSalesDataTable extends BaseDataTable
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
            ->addColumn('formatted_outstanding', function ($row) {
                $outstanding = $row->amount - ($row->collections ?? 0.00) - ($row->returns ?? 0.00);
                return currency_format($outstanding < 0 ? 0.00 : $outstanding);
            });
    }

    public function query(Invoice $model)
    {
        $service = new SalesReportsService();
        $filters = request()->only([
            'startDate', 'endDate', 'dealerId', 'salespersonId', 'warehouseId', 'productId', 'modelId', 'city', 'invoiceStatus'
        ]);

        $query = $model->query()
            ->join('users as clients', 'clients.id', '=', 'invoices.client_id')
            ->leftJoin('client_details as cd', 'cd.user_id', '=', 'invoices.client_id')
            ->select([
                'invoices.client_id',
                'clients.name as dealer',
                'cd.dealer_code as dealer_code',
                DB::raw('COUNT(invoices.id) as sales_count'),
                DB::raw('SUM(invoices.total) as amount'),
                DB::raw("
                    (
                        SELECT SUM(payments.amount) 
                        FROM payments 
                        WHERE payments.status = 'complete' 
                        AND payments.customer_id = invoices.client_id
                    ) as collections
                "),
                DB::raw("
                    (
                        SELECT SUM(credit_notes.total) 
                        FROM credit_notes 
                        JOIN invoices as inv_cn ON inv_cn.id = credit_notes.invoice_id
                        WHERE inv_cn.client_id = invoices.client_id
                    ) as returns
                ")
            ]);

        $query = $service->applyInvoiceFilters($query, $filters);
        
        $query->groupBy('invoices.client_id', 'clients.name', 'cd.dealer_code');

        return $query;
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('dealer-sales-table')
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
                    window.LaravelDataTables["dealer-sales-table"].buttons().container()
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
            'dealer' => ['data' => 'dealer', 'name' => 'clients.name', 'title' => 'Dealer Name'],
            'dealer_code' => ['data' => 'dealer_code', 'name' => 'cd.dealer_code', 'title' => 'Dealer Code'],
            'sales_count' => ['data' => 'sales_count', 'name' => 'sales_count', 'title' => 'Invoice Count', 'searchable' => false],
            'formatted_amount' => ['data' => 'formatted_amount', 'name' => 'amount', 'title' => 'Total Sales Amount', 'searchable' => false],
            'formatted_collections' => ['data' => 'formatted_collections', 'name' => 'collections', 'title' => 'Total Collections', 'searchable' => false],
            'formatted_outstanding' => ['data' => 'formatted_outstanding', 'name' => 'outstanding', 'title' => 'Total Outstanding', 'searchable' => false]
        ];
    }
}
