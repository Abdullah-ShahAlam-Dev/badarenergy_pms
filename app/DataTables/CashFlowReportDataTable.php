<?php

namespace App\DataTables;

use App\Models\Payment;
use App\Services\SalesReportsService;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CashFlowReportDataTable extends BaseDataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addIndexColumn()
            ->addColumn('month_name', function ($row) {
                return Carbon::parse($row->period)->format('F Y');
            })
            ->addColumn('formatted_inflow', function ($row) {
                return currency_format($row->cash_inflow);
            })
            ->addColumn('formatted_outflow', function ($row) {
                return currency_format($row->cash_outflow ?? 0.00);
            })
            ->addColumn('formatted_net', function ($row) {
                $net = $row->cash_inflow - ($row->cash_outflow ?? 0.00);
                return currency_format($net);
            });
    }

    public function query(Payment $model)
    {
        $filters = request()->only([
            'startDate', 'endDate', 'dealerId', 'salespersonId', 'city'
        ]);

        $companyId = company() ? company()->id : null;

        $query = $model->query()
            ->where('payments.status', 'complete');

        if ($companyId) {
            $query->where('payments.company_id', $companyId);
        }
        if (!empty($filters['startDate']) && !empty($filters['endDate'])) {
            $query->whereBetween('payments.paid_on', [$filters['startDate'], $filters['endDate']]);
        }
        if (!empty($filters['dealerId']) && $filters['dealerId'] !== 'all') {
            $query->where('payments.customer_id', $filters['dealerId']);
        }
        if (!empty($filters['salespersonId']) && $filters['salespersonId'] !== 'all') {
            $query->join('client_details as cd', 'cd.user_id', '=', 'payments.customer_id')
                ->where('cd.salesperson_id', $filters['salespersonId']);
        }
        if (!empty($filters['city']) && $filters['city'] !== 'all') {
            if (strpos(implode(',', $query->getQuery()->joins ?? []), 'client_details') === false) {
                $query->join('client_details as cd', 'cd.user_id', '=', 'payments.customer_id');
            }
            $query->where('cd.city', $filters['city']);
        }

        // Salesperson user restriction constraint
        if (!in_array('admin', user_roles())) {
            if (strpos(implode(',', $query->getQuery()->joins ?? []), 'client_details') === false) {
                $query->join('client_details as cd_restrict', 'cd_restrict.user_id', '=', 'payments.customer_id');
            }
            $alias = strpos(implode(',', $query->getQuery()->joins ?? []), 'cd_restrict') !== false ? 'cd_restrict' : 'cd';
            $query->where($alias . '.salesperson_id', user()->id);
        }

        $query->selectRaw("
            DATE_FORMAT(payments.paid_on, '%Y-%m') as month_num,
            MIN(payments.paid_on) as period,
            SUM(payments.amount) as cash_inflow,
            (
                SELECT SUM(price) 
                FROM expenses 
                WHERE expenses.status = 'approved' 
                " . ($companyId ? " AND expenses.company_id = {$companyId}" : "") . "
                AND DATE_FORMAT(expenses.purchase_date, '%Y-%m') = DATE_FORMAT(MIN(payments.paid_on), '%Y-%m')
            ) as cash_outflow
        ")
        ->groupBy(DB::raw("DATE_FORMAT(payments.paid_on, '%Y-%m')"));

        return $query;
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('cashflow-report-table')
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
                    window.LaravelDataTables["cashflow-report-table"].buttons().container()
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
            'month_name' => ['data' => 'month_name', 'name' => 'period', 'title' => 'Period / Month', 'orderable' => false],
            'formatted_inflow' => ['data' => 'formatted_inflow', 'name' => 'cash_inflow', 'title' => 'Cash Inflow (Collections)', 'searchable' => false],
            'formatted_outflow' => ['data' => 'formatted_outflow', 'name' => 'cash_outflow', 'title' => 'Cash Outflow (Expenses)', 'searchable' => false],
            'formatted_net' => ['data' => 'formatted_net', 'name' => 'net_flow', 'title' => 'Net Cash Flow', 'searchable' => false, 'orderable' => false]
        ];
    }
}
