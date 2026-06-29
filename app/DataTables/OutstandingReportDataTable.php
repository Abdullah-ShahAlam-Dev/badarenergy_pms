<?php

namespace App\DataTables;

use App\Models\DealerAgingSnapshot;
use App\Services\SalesReportsService;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Illuminate\Support\Facades\DB;

class OutstandingReportDataTable extends BaseDataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addIndexColumn()
            ->addColumn('formatted_limit', function ($row) {
                return $row->credit_limit > 0 ? currency_format($row->credit_limit) : 'No Limit';
            })
            ->addColumn('formatted_outstanding', function ($row) {
                return currency_format($row->outstanding_balance);
            })
            ->addColumn('formatted_overdue', function ($row) {
                $overdue = $row->outstanding_balance - $row->current;
                return currency_format($overdue < 0 ? 0.00 : $overdue);
            })
            ->addColumn('formatted_utilization', function ($row) {
                if ($row->credit_limit > 0) {
                    $pct = ($row->outstanding_balance / $row->credit_limit) * 100;
                    return round($pct, 2) . '%';
                }
                return '0%';
            });
    }

    public function query(DealerAgingSnapshot $model)
    {
        $filters = request()->only([
            'startDate', 'endDate', 'dealerId', 'salespersonId', 'warehouseId', 'productId', 'modelId', 'city'
        ]);

        $companyId = company() ? company()->id : null;

        $query = $model->query()
            ->join('users as clients', 'clients.id', '=', 'dealer_aging_snapshots.dealer_id')
            ->leftJoin('client_details as cd', 'cd.user_id', '=', 'dealer_aging_snapshots.dealer_id')
            ->leftJoin('users as salesperson', 'salesperson.id', '=', 'cd.salesperson_id')
            ->select([
                'dealer_aging_snapshots.id',
                'clients.name as dealer',
                'salesperson.name as salesperson',
                'cd.credit_limit',
                'dealer_aging_snapshots.outstanding as outstanding_balance',
                'dealer_aging_snapshots.current',
                'dealer_aging_snapshots.company_id'
            ]);

        if ($companyId) {
            $query->where('dealer_aging_snapshots.company_id', $companyId);
        }

        if (!empty($filters['dealerId']) && $filters['dealerId'] !== 'all') {
            $query->where('dealer_aging_snapshots.dealer_id', $filters['dealerId']);
        }

        if (!empty($filters['salespersonId']) && $filters['salespersonId'] !== 'all') {
            $query->where('cd.salesperson_id', $filters['salespersonId']);
        }

        if (!empty($filters['city']) && $filters['city'] !== 'all') {
            $query->where('cd.city', $filters['city']);
        }

        // Salesperson user restriction constraint
        if (!in_array('admin', user_roles())) {
            $query->where('cd.salesperson_id', user()->id);
        }

        return $query;
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('outstanding-report-table')
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
                    window.LaravelDataTables["outstanding-report-table"].buttons().container()
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
            'salesperson' => ['data' => 'salesperson', 'name' => 'salesperson.name', 'title' => 'Assigned Salesperson'],
            'formatted_limit' => ['data' => 'formatted_limit', 'name' => 'cd.credit_limit', 'title' => 'Credit Limit'],
            'formatted_outstanding' => ['data' => 'formatted_outstanding', 'name' => 'dealer_aging_snapshots.outstanding', 'title' => 'Outstanding Balance (PKR)'],
            'formatted_overdue' => ['data' => 'formatted_overdue', 'name' => 'overdue_balance', 'title' => 'Overdue Balance (PKR)', 'searchable' => false, 'orderable' => false],
            'formatted_utilization' => ['data' => 'formatted_utilization', 'name' => 'utilization', 'title' => 'Credit Util. (%)', 'searchable' => false, 'orderable' => false]
        ];
    }
}
