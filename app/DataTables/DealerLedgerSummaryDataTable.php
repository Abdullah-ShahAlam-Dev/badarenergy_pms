<?php

namespace App\DataTables;

use App\Models\User;
use App\Scopes\ActiveScope;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DealerLedgerSummaryDataTable extends BaseDataTable
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
                return '<a href="' . route('ledgers.show', $row->id) . '" class="text-primary font-weight-bold">' . $row->name . '</a>';
            })
            ->editColumn('location', function ($row) {
                $location = [];
                if ($row->city) $location[] = $row->city;
                if ($row->area) $location[] = $row->area;
                return !empty($location) ? implode(' / ', $location) : '--';
            })
            ->editColumn('salesperson', function ($row) {
                return $row->salesperson_name ?? '--';
            })
            ->editColumn('credit_limit', function ($row) {
                return currency_format($row->credit_limit ?? 0.00, company()->currency_id);
            })
            ->addColumn('credit_utilization', function ($row) {
                $limit = (float)($row->credit_limit ?? 0.00);
                $outstanding = (float)($row->outstanding ?? 0.00);
                if ($limit <= 0) {
                    return $outstanding > 0 ? '<span class="badge badge-danger">Exceeded</span>' : '0%';
                }
                $pct = round(($outstanding / $limit) * 100);
                $badgeClass = $pct > 100 ? 'badge-danger' : ($pct > 80 ? 'badge-warning' : 'badge-success');
                return '<span class="badge ' . $badgeClass . '">' . $pct . '%</span>';
            })
            ->editColumn('outstanding', function ($row) {
                $outstanding = (float)($row->outstanding ?? 0.00);
                $class = $outstanding > 0 ? 'text-danger font-weight-bold' : 'text-success font-weight-bold';
                return '<span class="' . $class . '">' . currency_format($outstanding, company()->currency_id) . '</span>';
            })
            ->editColumn('last_payment_date', function ($row) {
                return $row->last_payment_date ? Carbon::parse($row->last_payment_date)->format(company()->date_format) : '--';
            })
            ->addColumn('days_since_payment', function ($row) {
                if (!$row->last_payment_date) return '--';
                $days = Carbon::parse($row->last_payment_date)->diffInDays(now());
                $badge = $days > 60 ? 'badge-danger' : ($days > 30 ? 'badge-warning' : 'badge-success');
                return '<span class="badge ' . $badge . '">' . $days . ' days</span>';
            })
            ->rawColumns(['dealer_name', 'credit_utilization', 'outstanding', 'days_since_payment'])
            ->addIndexColumn();
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\User $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(User $model)
    {
        $request = $this->request();
        $companyId = company() ? company()->id : null;

        $endDate = $request->endDate ? Carbon::createFromFormat(company()->date_format, $request->endDate)->endOfDay() : null;

        // Subqueries to fetch outstanding balance and last payment date
        $outstandingSub = 'SELECT balance FROM dealer_ledgers WHERE dealer_ledgers.dealer_id = users.id';
        if ($companyId) {
            $outstandingSub .= ' AND dealer_ledgers.company_id = ' . (int)$companyId;
        }
        if ($endDate) {
            $outstandingSub .= ' AND dealer_ledgers.date <= "' . $endDate->toDateTimeString() . '"';
        }
        $outstandingSub .= ' ORDER BY date DESC, id DESC LIMIT 1';

        $lastPaymentSub = 'SELECT date FROM dealer_ledgers WHERE dealer_ledgers.dealer_id = users.id AND credit > 0';
        if ($companyId) {
            $lastPaymentSub .= ' AND dealer_ledgers.company_id = ' . (int)$companyId;
        }
        if ($endDate) {
            $lastPaymentSub .= ' AND dealer_ledgers.date <= "' . $endDate->toDateTimeString() . '"';
        }
        $lastPaymentSub .= ' ORDER BY date DESC, id DESC LIMIT 1';

        $query = $model->withoutGlobalScopes([ActiveScope::class])
            ->join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->leftJoin('client_details', 'client_details.user_id', '=', 'users.id')
            ->leftJoin('users as salespersons', 'salespersons.id', '=', 'client_details.salesperson_id')
            ->where('roles.name', 'client');

        if ($companyId) {
            $query->where('users.company_id', $companyId);
        }

        if (!in_array('admin', user_roles())) {
            $query->where('client_details.salesperson_id', user()->id);
        }

        $query->select([
            'users.id',
            'users.name',
            'client_details.city',
            'client_details.area',
            'client_details.credit_limit',
            'salespersons.name as salesperson_name',
            DB::raw('(' . $outstandingSub . ') as outstanding'),
            DB::raw('(' . $lastPaymentSub . ') as last_payment_date')
        ]);

        // Filters
        if ($request->dealerId && $request->dealerId !== 'all') {
            $query->where('users.id', $request->dealerId);
        }

        if ($request->salespersonId && $request->salespersonId !== 'all') {
            $query->where('client_details.salesperson_id', $request->salespersonId);
        }

        if ($request->city && $request->city !== 'all') {
            $query->where('client_details.city', $request->city);
        }

        if ($request->outstandingOnly === 'yes') {
            $query->having('outstanding', '>', 0);
        }

        if ($request->creditExceeded === 'yes') {
            $query->havingRaw('outstanding > client_details.credit_limit');
        }

        return $query;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->setBuilder('dealer-ledger-summary-table', 2)
            ->parameters([
                'initComplete' => 'function () {
                   window.LaravelDataTables["dealer-ledger-summary-table"].buttons().container()
                    .appendTo("#table-actions")
                }',
            ])
            ->buttons(
                Button::make(['extend' => 'excel', 'text' => '<i class="fa fa-file-export"></i> ' . trans('app.exportExcel')])
            );
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
            '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => false, 'title' => '#'],
            __('app.id') => ['data' => 'id', 'name' => 'id', 'visible' => false, 'exportable' => false, 'title' => __('app.id')],
            'name' => ['data' => 'dealer_name', 'name' => 'users.name', 'title' => 'Dealer Name'],
            'location' => ['data' => 'location', 'name' => 'client_details.city', 'title' => 'Location (City/Area)', 'orderable' => false],
            'salesperson' => ['data' => 'salesperson', 'name' => 'salespersons.name', 'title' => 'Salesperson', 'orderable' => false],
            'credit_limit' => ['data' => 'credit_limit', 'name' => 'client_details.credit_limit', 'title' => 'Credit Limit'],
            'credit_utilization' => ['data' => 'credit_utilization', 'title' => 'Credit Util.', 'orderable' => false, 'searchable' => false],
            'outstanding' => ['data' => 'outstanding', 'title' => 'Net Outstanding', 'searchable' => false],
            'last_payment_date' => ['data' => 'last_payment_date', 'title' => 'Last Payment Date', 'searchable' => false],
            'days_since_payment' => ['data' => 'days_since_payment', 'title' => 'Days Since Payment', 'orderable' => false, 'searchable' => false]
        ];
    }
}
