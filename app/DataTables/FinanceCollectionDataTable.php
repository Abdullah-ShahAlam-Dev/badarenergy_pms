<?php

namespace App\DataTables;

use App\Models\Payment;
use App\Services\SalesReportsService;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Carbon\Carbon;

class FinanceCollectionDataTable extends BaseDataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addIndexColumn()
            ->addColumn('formatted_date', function ($row) {
                return Carbon::parse($row->paid_on)->format($this->company->date_format);
            })
            ->addColumn('formatted_amount', function ($row) {
                return currency_format($row->amount);
            })
            ->addColumn('bank_name', function ($row) {
                return $row->offlineMethod ? $row->offlineMethod->name : ($row->gateway ?: '--');
            });
    }

    public function query(Payment $model)
    {
        $service = new SalesReportsService();
        $filters = request()->only([
            'startDate', 'endDate', 'dealerId', 'salespersonId', 'warehouseId', 'productId', 'modelId', 'city', 'paymentStatus'
        ]);

        $query = $model->query()
            ->with(['offlineMethod'])
            ->leftJoin('users as clients', 'clients.id', '=', 'payments.customer_id')
            ->select([
                'payments.id',
                'payments.paid_on',
                'payments.transaction_id as reference_number',
                'clients.name as dealer',
                'payments.gateway as payment_mode',
                'payments.offline_method_id',
                'payments.amount'
            ]);

        return $service->applyPaymentFilters($query, $filters);
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('finance-collection-table')
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
                    window.LaravelDataTables["finance-collection-table"].buttons().container()
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
            'formatted_date' => ['data' => 'formatted_date', 'name' => 'payments.paid_on', 'title' => 'Date'],
            'reference_number' => ['data' => 'reference_number', 'name' => 'payments.transaction_id', 'title' => 'Reference / Receipt No.'],
            'dealer' => ['data' => 'dealer', 'name' => 'clients.name', 'title' => 'Dealer Name'],
            'payment_mode' => ['data' => 'payment_mode', 'name' => 'payments.gateway', 'title' => 'Payment Mode'],
            'bank_name' => ['data' => 'bank_name', 'name' => 'payments.offline_method_id', 'title' => 'Bank / Gateway Name', 'orderable' => false],
            'formatted_amount' => ['data' => 'formatted_amount', 'name' => 'payments.amount', 'title' => 'Amount Collected']
        ];
    }
}
