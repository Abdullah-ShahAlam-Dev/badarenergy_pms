<?php

namespace App\DataTables;

use App\Models\Invoice;
use App\Services\SalesReportsService;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Carbon\Carbon;

class DailySalesDataTable extends BaseDataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addIndexColumn()
            ->addColumn('formatted_date', function ($row) {
                return Carbon::parse($row->issue_date)->format($this->company->date_format);
            })
            ->addColumn('formatted_amount', function ($row) {
                return currency_format($row->amount);
            })
            ->addColumn('formatted_status', function ($row) {
                $statusColors = [
                    'paid' => 'success',
                    'unpaid' => 'danger',
                    'partial' => 'warning',
                    'draft' => 'secondary',
                    'canceled' => 'dark'
                ];
                $color = $statusColors[$row->status] ?? 'secondary';
                return '<span class="badge badge-' . $color . '">' . ucfirst($row->status) . '</span>';
            })
            ->rawColumns(['formatted_status']);
    }

    public function query(Invoice $model)
    {
        $service = new SalesReportsService();
        $filters = request()->only([
            'startDate', 'endDate', 'dealerId', 'salespersonId', 'warehouseId', 'productId', 'modelId', 'city', 'invoiceStatus'
        ]);

        // Default date range filter to current month if empty
        if (empty($filters['startDate']) && !request()->ajax()) {
            $filters['startDate'] = now($this->company->timezone)->startOfMonth()->toDateString();
            $filters['endDate'] = now($this->company->timezone)->toDateString();
        }

        $query = $model->query()
            ->leftJoin('users as clients', 'clients.id', '=', 'invoices.client_id')
            ->leftJoin('client_details as cd', 'cd.user_id', '=', 'invoices.client_id')
            ->leftJoin('users as salesperson', 'salesperson.id', '=', 'cd.salesperson_id')
            ->select([
                'invoices.id',
                'invoices.invoice_number',
                'invoices.issue_date',
                'clients.name as dealer',
                'salesperson.name as salesperson',
                'invoices.total as amount',
                'invoices.status'
            ]);

        return $service->applyInvoiceFilters($query, $filters);
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('daily-sales-table')
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
                    window.LaravelDataTables["daily-sales-table"].buttons().container()
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
            'formatted_date' => ['data' => 'formatted_date', 'name' => 'invoices.issue_date', 'title' => 'Date'],
            'invoice_number' => ['data' => 'invoice_number', 'name' => 'invoices.invoice_number', 'title' => 'Invoice Number'],
            'dealer' => ['data' => 'dealer', 'name' => 'clients.name', 'title' => 'Dealer'],
            'salesperson' => ['data' => 'salesperson', 'name' => 'salesperson.name', 'title' => 'Salesperson'],
            'formatted_amount' => ['data' => 'formatted_amount', 'name' => 'invoices.total', 'title' => 'Total Amount'],
            'formatted_status' => ['data' => 'formatted_status', 'name' => 'invoices.status', 'title' => 'Status']
        ];
    }
}
