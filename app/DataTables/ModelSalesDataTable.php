<?php

namespace App\DataTables;

use App\Models\InvoiceItems;
use App\Services\SalesReportsService;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Illuminate\Support\Facades\DB;

class ModelSalesDataTable extends BaseDataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addIndexColumn()
            ->addColumn('formatted_amount', function ($row) {
                return currency_format($row->amount);
            });
    }

    public function query(InvoiceItems $model)
    {
        $service = new SalesReportsService();
        $filters = request()->only([
            'startDate', 'endDate', 'dealerId', 'salespersonId', 'warehouseId', 'productId', 'modelId', 'city', 'invoiceStatus'
        ]);

        $query = $model->query()
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->join('products', 'products.id', '=', 'invoice_items.product_id')
            ->join('product_sub_categories as psc', 'psc.id', '=', 'products.sub_category_id')
            ->select([
                'products.sub_category_id',
                'psc.category_name as model',
                DB::raw('SUM(invoice_items.quantity) as qty_sold'),
                DB::raw('SUM(invoice_items.amount) as amount')
            ]);

        $companyId = company() ? company()->id : null;
        if ($companyId) {
            $query->where('invoices.company_id', $companyId);
        }
        if (!empty($filters['startDate']) && !empty($filters['endDate'])) {
            $query->whereBetween('invoices.issue_date', [$filters['startDate'], $filters['endDate']]);
        }
        if (!empty($filters['dealerId']) && $filters['dealerId'] !== 'all') {
            $query->where('invoices.client_id', $filters['dealerId']);
        }
        if (!empty($filters['salespersonId']) && $filters['salespersonId'] !== 'all') {
            $query->join('client_details as cd', 'cd.user_id', '=', 'invoices.client_id')
                ->where('cd.salesperson_id', $filters['salespersonId']);
        }
        if (!empty($filters['warehouseId']) && $filters['warehouseId'] !== 'all') {
            $query->where('invoices.warehouse_id', $filters['warehouseId']);
        }
        if (!empty($filters['city']) && $filters['city'] !== 'all') {
            if (strpos(implode(',', $query->getQuery()->joins ?? []), 'client_details') === false) {
                $query->join('client_details as cd', 'cd.user_id', '=', 'invoices.client_id');
            }
            $query->where('cd.city', $filters['city']);
        }
        if (!empty($filters['productId']) && $filters['productId'] !== 'all') {
            $query->where('invoice_items.product_id', $filters['productId']);
        }
        if (!empty($filters['modelId']) && $filters['modelId'] !== 'all') {
            $query->where('products.sub_category_id', $filters['modelId']);
        }
        if (!empty($filters['invoiceStatus']) && $filters['invoiceStatus'] !== 'all') {
            $query->where('invoices.status', $filters['invoiceStatus']);
        } else {
            $query->whereIn('invoices.status', ['paid', 'unpaid', 'partial']);
        }

        // Salesperson user restriction constraint
        if (!in_array('admin', user_roles())) {
            if (strpos(implode(',', $query->getQuery()->joins ?? []), 'client_details') === false) {
                $query->join('client_details as cd_restrict', 'cd_restrict.user_id', '=', 'invoices.client_id');
            }
            $alias = strpos(implode(',', $query->getQuery()->joins ?? []), 'cd_restrict') !== false ? 'cd_restrict' : 'cd';
            $query->where($alias . '.salesperson_id', user()->id);
        }

        $query->groupBy('products.sub_category_id', 'psc.category_name');

        return $query;
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('model-sales-table')
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
                    window.LaravelDataTables["model-sales-table"].buttons().container()
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
            'model' => ['data' => 'model', 'name' => 'psc.category_name', 'title' => 'Product Model/Subcategory'],
            'qty_sold' => ['data' => 'qty_sold', 'name' => 'qty_sold', 'title' => 'Quantity Sold', 'searchable' => false],
            'formatted_amount' => ['data' => 'formatted_amount', 'name' => 'amount', 'title' => 'Total Sales Amount', 'searchable' => false]
        ];
    }
}
