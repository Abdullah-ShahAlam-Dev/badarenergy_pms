<?php

namespace App\DataTables;

use App\Models\DealerAgingSnapshot;
use App\Models\User;
use App\Scopes\ActiveScope;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DealerAgingDataTable extends BaseDataTable
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
            ->addColumn('dealer_name', function ($row) {
                $code = $row->dealer_code ? ' <span class="text-muted">(' . $row->dealer_code . ')</span>' : '';
                $tier = $row->dealer_tier ? ' <span class="badge badge-light">' . ucfirst($row->dealer_tier) . '</span>' : '';
                return '<a href="' . route('ledgers.show', $row->dealer_id) . '" class="text-primary font-weight-bold">' . $row->name . '</a>' . $code . $tier;
            })
            ->editColumn('location', function ($row) {
                $loc = [];
                if ($row->city) $loc[] = $row->city;
                if ($row->area) $loc[] = $row->area;
                return !empty($loc) ? implode(' / ', $loc) : '--';
            })
            ->editColumn('salesperson', function ($row) {
                return $row->salesperson_name ?? '--';
            })
            ->editColumn('credit_limit', function ($row) {
                return currency_format($row->credit_limit ?? 0.00, company()->currency_id);
            })
            ->editColumn('outstanding', function ($row) {
                return '<span class="font-weight-bold text-danger">' . currency_format($row->outstanding ?? 0.00, company()->currency_id) . '</span>';
            })
            ->editColumn('current', function ($row) {
                return currency_format($row->current ?? 0.00, company()->currency_id);
            })
            ->editColumn('bucket_1_15', function ($row) {
                return currency_format($row->bucket_1_15 ?? 0.00, company()->currency_id);
            })
            ->editColumn('bucket_16_30', function ($row) {
                return currency_format($row->bucket_16_30 ?? 0.00, company()->currency_id);
            })
            ->editColumn('bucket_31_60', function ($row) {
                return currency_format($row->bucket_31_60 ?? 0.00, company()->currency_id);
            })
            ->editColumn('bucket_61_90', function ($row) {
                return currency_format($row->bucket_61_90 ?? 0.00, company()->currency_id);
            })
            ->editColumn('bucket_91_plus', function ($row) {
                return currency_format($row->bucket_91_plus ?? 0.00, company()->currency_id);
            })
            ->editColumn('credit_utilization', function ($row) {
                $pct = (float)($row->credit_utilization ?? 0.00);
                $badgeClass = $pct > 100 ? 'badge-danger' : ($pct > 80 ? 'badge-warning' : 'badge-success');
                return '<span class="badge ' . $badgeClass . '">' . $pct . '%</span>';
            })
            ->editColumn('last_payment_date', function ($row) {
                return $row->last_payment_date ? Carbon::parse($row->last_payment_date)->format(company()->date_format) : '--';
            })
            ->editColumn('days_since_payment', function ($row) {
                if (is_null($row->days_since_payment)) return '--';
                $badge = $row->days_since_payment > 60 ? 'badge-danger' : ($row->days_since_payment > 30 ? 'badge-warning' : 'badge-success');
                return '<span class="badge ' . $badge . '">' . $row->days_since_payment . ' days</span>';
            })
            ->rawColumns(['dealer_name', 'outstanding', 'credit_utilization', 'days_since_payment'])
            ->addIndexColumn();
    }

    /**
     * Get query source of dataTable.
     */
    public function query(DealerAgingSnapshot $model)
    {
        $request = $this->request();
        $companyId = company() ? company()->id : null;

        $query = $model->withoutGlobalScopes([ActiveScope::class])
            ->join('users', 'users.id', '=', 'dealer_aging_snapshots.dealer_id')
            ->leftJoin('client_details', 'client_details.user_id', '=', 'users.id')
            ->leftJoin('users as salespersons', 'salespersons.id', '=', 'client_details.salesperson_id');

        if ($companyId) {
            $query->where('dealer_aging_snapshots.company_id', $companyId);
        }

        if (!in_array('admin', user_roles())) {
            $query->where('client_details.salesperson_id', user()->id);
        }

        $query->select([
            'dealer_aging_snapshots.*',
            'users.name',
            'client_details.dealer_code',
            'client_details.dealer_tier',
            'client_details.city',
            'client_details.area',
            'client_details.credit_limit',
            'salespersons.name as salesperson_name'
        ]);

        // Apply filters
        if ($request->dealerId && $request->dealerId !== 'all') {
            $query->where('users.id', $request->dealerId);
        }

        if ($request->salespersonId && $request->salespersonId !== 'all') {
            $query->where('client_details.salesperson_id', $request->salespersonId);
        }

        if ($request->city && $request->city !== 'all') {
            $query->where('client_details.city', $request->city);
        }

        if ($request->dealerTier && $request->dealerTier !== 'all') {
            $query->where('client_details.dealer_tier', $request->dealerTier);
        }

        if ($request->outstandingOnly === 'yes') {
            $query->where('dealer_aging_snapshots.outstanding', '>', 0);
        }

        if ($request->creditExceeded === 'yes') {
            $query->whereRaw('dealer_aging_snapshots.outstanding > client_details.credit_limit');
        }

        if ($request->zeroBalance === 'yes') {
            $query->where('dealer_aging_snapshots.outstanding', '=', 0);
        }

        return $query;
    }

    /**
     * Optional method if you want to use html builder.
     */
    public function html()
    {
        return $this->setBuilder('dealer-aging-table', 2)
            ->parameters([
                'initComplete' => 'function () {
                   window.LaravelDataTables["dealer-aging-table"].buttons().container()
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
            'name' => ['data' => 'dealer_name', 'name' => 'users.name', 'title' => 'Dealer Name'],
            'location' => ['data' => 'location', 'name' => 'client_details.city', 'title' => 'Location (City/Area)', 'orderable' => false],
            'salesperson' => ['data' => 'salesperson', 'name' => 'salespersons.name', 'title' => 'Salesperson', 'orderable' => false],
            'credit_limit' => ['data' => 'credit_limit', 'name' => 'client_details.credit_limit', 'title' => 'Credit Limit'],
            'outstanding' => ['data' => 'outstanding', 'title' => 'Outstanding'],
            'current' => ['data' => 'current', 'title' => 'Current (0 d)'],
            'bucket_1_15' => ['data' => 'bucket_1_15', 'title' => '1-15 Days'],
            'bucket_16_30' => ['data' => 'bucket_16_30', 'title' => '16-30 Days'],
            'bucket_31_60' => ['data' => 'bucket_31_60', 'title' => '31-60 Days'],
            'bucket_61_90' => ['data' => 'bucket_61_90', 'title' => '61-90 Days'],
            'bucket_91_plus' => ['data' => 'bucket_91_plus', 'title' => '90+ Days'],
            'credit_utilization' => ['data' => 'credit_utilization', 'title' => 'Utilization', 'searchable' => false],
            'last_payment_date' => ['data' => 'last_payment_date', 'title' => 'Last Payment', 'searchable' => false],
            'days_since_payment' => ['data' => 'days_since_payment', 'title' => 'Days Since Payment', 'searchable' => false]
        ];
    }
}
