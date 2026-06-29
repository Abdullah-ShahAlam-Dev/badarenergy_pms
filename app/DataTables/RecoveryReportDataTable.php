<?php

namespace App\DataTables;

use App\Models\User;
use App\Services\SalesReportsService;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Illuminate\Support\Facades\DB;

class RecoveryReportDataTable extends BaseDataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addIndexColumn()
            ->addColumn('formatted_target', function ($row) {
                // Target represents total billed (invoice sales) in Phase 2
                return currency_format($row->target_amount);
            })
            ->addColumn('formatted_recovered', function ($row) {
                return currency_format($row->recovered_amount ?? 0.00);
            })
            ->addColumn('formatted_variance', function ($row) {
                $variance = $row->target_amount - ($row->recovered_amount ?? 0.00);
                return currency_format($variance < 0 ? 0.00 : $variance);
            })
            ->addColumn('formatted_achievement', function ($row) {
                $pct = $row->target_amount > 0 ? (($row->recovered_amount ?? 0.00) / $row->target_amount) * 100 : 100.00;
                return round($pct, 2) . '%';
            });
    }

    public function query(User $model)
    {
        $filters = request()->only([
            'startDate', 'endDate', 'dealerId', 'salespersonId', 'warehouseId', 'productId', 'modelId', 'city'
        ]);

        $companyId = company() ? company()->id : null;

        // Start with salesperson employees
        $query = $model->query()
            ->join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->where('roles.name', 'employee');

        if ($companyId) {
            $query->where('users.company_id', $companyId);
        }

        if (!empty($filters['salespersonId']) && $filters['salespersonId'] !== 'all') {
            $query->where('users.id', $filters['salespersonId']);
        }

        if (!in_array('admin', user_roles())) {
            $query->where('users.id', user()->id);
        }

        // Subquery sales (target)
        $query->select([
            'users.id',
            'users.name as salesperson',
            DB::raw("
                (
                    SELECT COALESCE(SUM(inv.total), 0)
                    FROM invoices inv
                    JOIN client_details cd ON cd.user_id = inv.client_id
                    WHERE cd.salesperson_id = users.id
                    " . ($companyId ? " AND inv.company_id = {$companyId}" : "") . "
                    " . (!empty($filters['startDate']) && !empty($filters['endDate']) ? " AND inv.issue_date BETWEEN '{$filters['startDate']}' AND '{$filters['endDate']}'" : "") . "
                    " . (!empty($filters['dealerId']) && $filters['dealerId'] !== 'all' ? " AND inv.client_id = {$filters['dealerId']}" : "") . "
                    " . (!empty($filters['warehouseId']) && $filters['warehouseId'] !== 'all' ? " AND inv.warehouse_id = {$filters['warehouseId']}" : "") . "
                    " . (!empty($filters['city']) && $filters['city'] !== 'all' ? " AND cd.city = '{$filters['city']}'" : "") . "
                ) as target_amount
            "),
            // Subquery collections (recovered)
            DB::raw("
                (
                    SELECT COALESCE(SUM(pay.amount), 0)
                    FROM payments pay
                    JOIN client_details cd ON cd.user_id = pay.customer_id
                    WHERE cd.salesperson_id = users.id
                    AND pay.status = 'complete'
                    " . ($companyId ? " AND pay.company_id = {$companyId}" : "") . "
                    " . (!empty($filters['startDate']) && !empty($filters['endDate']) ? " AND pay.paid_on BETWEEN '{$filters['startDate']}' AND '{$filters['endDate']}'" : "") . "
                    " . (!empty($filters['dealerId']) && $filters['dealerId'] !== 'all' ? " AND pay.customer_id = {$filters['dealerId']}" : "") . "
                    " . (!empty($filters['city']) && $filters['city'] !== 'all' ? " AND cd.city = '{$filters['city']}'" : "") . "
                ) as recovered_amount
            ")
        ]);

        return $query;
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('recovery-report-table')
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
                    window.LaravelDataTables["recovery-report-table"].buttons().container()
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
            'salesperson' => ['data' => 'salesperson', 'name' => 'users.name', 'title' => 'Salesperson Name'],
            'formatted_target' => ['data' => 'formatted_target', 'name' => 'target_amount', 'title' => 'New Invoiced Sales (PKR)', 'searchable' => false],
            'formatted_recovered' => ['data' => 'formatted_recovered', 'name' => 'recovered_amount', 'title' => 'Actual Recovered (PKR)', 'searchable' => false],
            'formatted_variance' => ['data' => 'formatted_variance', 'name' => 'variance', 'title' => 'Billed-to-Collected Variance', 'searchable' => false, 'orderable' => false],
            'formatted_achievement' => ['data' => 'formatted_achievement', 'name' => 'achievement_pct', 'title' => 'Achievement %', 'searchable' => false, 'orderable' => false]
        ];
    }
}
