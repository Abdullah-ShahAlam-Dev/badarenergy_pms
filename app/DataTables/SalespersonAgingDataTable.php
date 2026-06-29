<?php

namespace App\DataTables;

use App\Models\User;
use App\Scopes\ActiveScope;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Illuminate\Support\Facades\DB;

class SalespersonAgingDataTable extends BaseDataTable
{
    /**
     * Build DataTable class.
     *
     * @param mixed $query
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->editColumn('salesperson_name', function ($row) {
                return '<span class="font-weight-bold text-dark-grey">' . $row->salesperson_name . '</span>';
            })
            ->editColumn('assigned_dealers', function ($row) {
                return '<span class="badge badge-light">' . $row->assigned_dealers . ' dealers</span>';
            })
            ->editColumn('total_outstanding', function ($row) {
                return '<span class="font-weight-bold text-danger">' . currency_format($row->total_outstanding ?? 0.00, company()->currency_id) . '</span>';
            })
            ->editColumn('current', function ($row) {
                return currency_format($row->current_bal ?? 0.00, company()->currency_id);
            })
            ->editColumn('bucket_1_15', function ($row) {
                return currency_format($row->bucket_1_15_bal ?? 0.00, company()->currency_id);
            })
            ->editColumn('bucket_16_30', function ($row) {
                return currency_format($row->bucket_16_30_bal ?? 0.00, company()->currency_id);
            })
            ->editColumn('bucket_31_60', function ($row) {
                return currency_format($row->bucket_31_60_bal ?? 0.00, company()->currency_id);
            })
            ->editColumn('bucket_61_90', function ($row) {
                return currency_format($row->bucket_61_90_bal ?? 0.00, company()->currency_id);
            })
            ->editColumn('bucket_91_plus', function ($row) {
                return currency_format($row->bucket_91_plus_bal ?? 0.00, company()->currency_id);
            })
            ->addColumn('limit_exceeded', function ($row) {
                $count = (int)$row->limit_exceeded_count;
                return $count > 0 ? '<span class="badge badge-danger">' . $count . ' dealers</span>' : '<span class="badge badge-success">None</span>';
            })
            ->rawColumns(['salesperson_name', 'assigned_dealers', 'total_outstanding', 'limit_exceeded'])
            ->addIndexColumn();
    }

    /**
     * Get query source of dataTable.
     */
    public function query(User $model)
    {
        $request = $this->request();
        $companyId = company() ? company()->id : null;

        $query = $model->withoutGlobalScopes([ActiveScope::class])
            ->join('client_details', 'client_details.salesperson_id', '=', 'users.id')
            ->join('dealer_aging_snapshots', 'dealer_aging_snapshots.dealer_id', '=', 'client_details.user_id');

        if ($companyId) {
            $query->where('users.company_id', $companyId);
        }

        if (!in_array('admin', user_roles())) {
            $query->where('users.id', user()->id);
        }

        $query->select([
            'users.id',
            'users.name as salesperson_name',
            DB::raw('count(client_details.user_id) as assigned_dealers'),
            DB::raw('sum(dealer_aging_snapshots.outstanding) as total_outstanding'),
            DB::raw('sum(dealer_aging_snapshots.current) as current_bal'),
            DB::raw('sum(dealer_aging_snapshots.bucket_1_15) as bucket_1_15_bal'),
            DB::raw('sum(dealer_aging_snapshots.bucket_16_30) as bucket_16_30_bal'),
            DB::raw('sum(dealer_aging_snapshots.bucket_31_60) as bucket_31_60_bal'),
            DB::raw('sum(dealer_aging_snapshots.bucket_61_90) as bucket_61_90_bal'),
            DB::raw('sum(dealer_aging_snapshots.bucket_91_plus) as bucket_91_plus_bal'),
            DB::raw('sum(CASE WHEN dealer_aging_snapshots.outstanding > client_details.credit_limit THEN 1 ELSE 0 END) as limit_exceeded_count')
        ]);

        // Filter by Salesperson
        if ($request->salespersonId && $request->salespersonId !== 'all') {
            $query->where('users.id', $request->salespersonId);
        }

        // Filter by City
        if ($request->city && $request->city !== 'all') {
            $query->where('client_details.city', $request->city);
        }

        $query->groupBy('users.id', 'users.name');

        return $query;
    }

    /**
     * Optional method if you want to use html builder.
     */
    public function html()
    {
        return $this->setBuilder('salesperson-aging-table', 2)
            ->parameters([
                'initComplete' => 'function () {
                   window.LaravelDataTables["salesperson-aging-table"].buttons().container()
                    .appendTo("#table-actions")
                }',
            ])
            ->buttons(
                Button::make(['extend' => 'excel', 'text' => '<i class="fa fa-file-export"></i> ' . trans('app.exportExcel')])
            );
    }

    /**
     * Get columns.
     */
    protected function getColumns()
    {
        return [
            '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => false, 'title' => '#'],
            'salesperson_name' => ['data' => 'salesperson_name', 'title' => 'Salesperson Name'],
            'assigned_dealers' => ['data' => 'assigned_dealers', 'title' => 'Dealers Assigned', 'orderable' => false, 'searchable' => false],
            'total_outstanding' => ['data' => 'total_outstanding', 'title' => 'Total Outstanding'],
            'current' => ['data' => 'current', 'title' => 'Current (0 d)', 'searchable' => false],
            'bucket_1_15' => ['data' => 'bucket_1_15', 'title' => '1-15 Days', 'searchable' => false],
            'bucket_16_30' => ['data' => 'bucket_16_30', 'title' => '16-30 Days', 'searchable' => false],
            'bucket_31_60' => ['data' => 'bucket_31_60', 'title' => '31-60 Days', 'searchable' => false],
            'bucket_61_90' => ['data' => 'bucket_61_90', 'title' => '61-90 Days', 'searchable' => false],
            'bucket_91_plus' => ['data' => 'bucket_91_plus', 'title' => '90+ Days', 'searchable' => false],
            'limit_exceeded' => ['data' => 'limit_exceeded', 'title' => 'Credit Exceeded', 'orderable' => false, 'searchable' => false]
        ];
    }
}
