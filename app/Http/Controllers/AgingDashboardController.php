<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\DealerAgingSnapshot;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AgingDashboardController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'Outstanding & Recovery Dashboard';
    }

    public function index()
    {
        abort_403(user()->permission('view_outstanding_dashboard') == 'none');

        $companyId = company() ? company()->id : null;
        $isSalesperson = !in_array('admin', user_roles());

        // Base query for aging snapshots
        $snapshotQuery = DealerAgingSnapshot::query();
        if ($companyId) {
            $snapshotQuery->where('dealer_aging_snapshots.company_id', $companyId);
        }
        if ($isSalesperson) {
            $snapshotQuery->join('client_details', 'client_details.user_id', '=', 'dealer_aging_snapshots.dealer_id')
                ->where('client_details.salesperson_id', user()->id);
        }

        // 1. Calculations
        $this->totalOutstanding = (float)$snapshotQuery->sum('outstanding');
        $this->currentReceivables = (float)$snapshotQuery->sum('current');
        $this->overdueReceivables = $this->totalOutstanding - $this->currentReceivables;

        // Today's Collection
        $todayQuery = Payment::where('status', 'complete')->whereDate('paid_on', Carbon::today());
        if ($companyId) {
            $todayQuery->where('payments.company_id', $companyId);
        }
        if ($isSalesperson) {
            $todayQuery->join('client_details', 'client_details.user_id', '=', 'payments.customer_id')
                ->where('client_details.salesperson_id', user()->id);
        }
        $this->todayCollection = (float)$todayQuery->sum('amount');

        // This Month Collection
        $monthQuery = Payment::where('status', 'complete')
            ->whereMonth('paid_on', Carbon::today()->month)
            ->whereYear('paid_on', Carbon::today()->year);
        if ($companyId) {
            $monthQuery->where('payments.company_id', $companyId);
        }
        if ($isSalesperson) {
            $monthQuery->join('client_details', 'client_details.user_id', '=', 'payments.customer_id')
                ->where('client_details.salesperson_id', user()->id);
        }
        $this->monthCollection = (float)$monthQuery->sum('amount');

        // DSO / Average Collection Days
        $creditSalesQuery = Invoice::whereIn('status', ['unpaid', 'paid', 'partial'])
            ->where('issue_date', '>=', now()->subDays(30));
        if ($companyId) {
            $creditSalesQuery->where('invoices.company_id', $companyId);
        }
        if ($isSalesperson) {
            $creditSalesQuery->join('client_details', 'client_details.user_id', '=', 'invoices.client_id')
                ->where('client_details.salesperson_id', user()->id);
        }
        $creditSales = (float)$creditSalesQuery->sum('total');
        $this->averageCollectionDays = $creditSales > 0 ? round(($this->totalOutstanding / $creditSales) * 30) : 0;

        // Blocked / Exceeded Credit Limit Count
        $blockedQuery = DealerAgingSnapshot::join('client_details', 'client_details.user_id', '=', 'dealer_aging_snapshots.dealer_id');
        if ($companyId) {
            $blockedQuery->where('dealer_aging_snapshots.company_id', $companyId);
        }
        if ($isSalesperson) {
            $blockedQuery->where('client_details.salesperson_id', user()->id);
        }
        $this->creditExceededCount = $blockedQuery->whereRaw('dealer_aging_snapshots.outstanding > client_details.credit_limit')->count();

        // 2. Defaulters & Extreme Cases
        $defQuery = DealerAgingSnapshot::with(['dealer', 'dealer.clientDetails'])
            ->join('client_details', 'client_details.user_id', '=', 'dealer_aging_snapshots.dealer_id');
        if ($companyId) {
            $defQuery->where('dealer_aging_snapshots.company_id', $companyId);
        }
        if ($isSalesperson) {
            $defQuery->where('client_details.salesperson_id', user()->id);
        }
        $this->topDefaulters = $defQuery->where('dealer_aging_snapshots.outstanding', '>', 0)
            ->select('dealer_aging_snapshots.*')
            ->orderBy('dealer_aging_snapshots.outstanding', 'desc')
            ->limit(10)
            ->get();

        // Average Outstanding per active dealer
        $activeDealersCount = $snapshotQuery->where('dealer_aging_snapshots.outstanding', '>', 0)->count();
        $this->averageOutstanding = $activeDealersCount > 0 ? ($this->totalOutstanding / $activeDealersCount) : 0.00;

        // Oldest Outstanding Invoice
        $oldestQuery = Invoice::whereIn('status', ['unpaid', 'partial'])->with('client')
            ->join('client_details', 'client_details.user_id', '=', 'invoices.client_id');
        if ($companyId) {
            $oldestQuery->where('invoices.company_id', $companyId);
        }
        if ($isSalesperson) {
            $oldestQuery->where('client_details.salesperson_id', user()->id);
        }
        $this->oldestInvoice = $oldestQuery->select('invoices.*')->orderBy('invoices.issue_date', 'asc')->first();

        // Largest Outstanding Invoice
        $largestQuery = Invoice::whereIn('status', ['unpaid', 'partial'])->with('client')
            ->join('client_details', 'client_details.user_id', '=', 'invoices.client_id');
        if ($companyId) {
            $largestQuery->where('invoices.company_id', $companyId);
        }
        if ($isSalesperson) {
            $largestQuery->where('client_details.salesperson_id', user()->id);
        }
        $this->largestInvoice = $largestQuery->select('invoices.*')->orderBy('invoices.due_amount', 'desc')->first();

        // 3. Outstanding Trend (Past 6 Months)
        $trendData = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i)->endOfMonth();
            $monthName = $date->format('M Y');

            // Sum of latest ledger running balances as of that date
            if ($isSalesperson) {
                $q = "
                    SELECT SUM(balance) as total_bal 
                    FROM dealer_ledgers dl1
                    JOIN client_details cd ON cd.user_id = dl1.dealer_id
                    WHERE cd.salesperson_id = ?
                    AND dl1.id = (
                        SELECT id FROM dealer_ledgers dl2 
                        WHERE dl2.dealer_id = dl1.dealer_id 
                        AND dl2.date <= ? 
                        ORDER BY date DESC, id DESC LIMIT 1
                    )
                ";
                $bindings = [user()->id, $date->toDateTimeString()];

                if ($companyId) {
                    $q = "
                        SELECT SUM(balance) as total_bal 
                        FROM dealer_ledgers dl1
                        JOIN client_details cd ON cd.user_id = dl1.dealer_id
                        WHERE dl1.company_id = ?
                        AND cd.salesperson_id = ?
                        AND dl1.id = (
                            SELECT id FROM dealer_ledgers dl2 
                            WHERE dl2.dealer_id = dl1.dealer_id 
                            AND dl2.date <= ? 
                            AND dl2.company_id = ?
                            ORDER BY date DESC, id DESC LIMIT 1
                        )
                    ";
                    $bindings = [$companyId, user()->id, $date->toDateTimeString(), $companyId];
                }
            } else {
                $q = "
                    SELECT SUM(balance) as total_bal 
                    FROM dealer_ledgers dl1
                    WHERE dl1.id = (
                        SELECT id FROM dealer_ledgers dl2 
                        WHERE dl2.dealer_id = dl1.dealer_id 
                        AND dl2.date <= ? 
                        ORDER BY date DESC, id DESC LIMIT 1
                    )
                ";
                $bindings = [$date->toDateTimeString()];

                if ($companyId) {
                    $q = "
                        SELECT SUM(balance) as total_bal 
                        FROM dealer_ledgers dl1
                        WHERE dl1.company_id = ?
                        AND dl1.id = (
                            SELECT id FROM dealer_ledgers dl2 
                            WHERE dl2.dealer_id = dl1.dealer_id 
                            AND dl2.date <= ? 
                            AND dl2.company_id = ?
                            ORDER BY date DESC, id DESC LIMIT 1
                        )
                    ";
                    $bindings = [$companyId, $date->toDateTimeString(), $companyId];
                }
            }

            $bal = DB::select($q, $bindings);
            $trendData[$monthName] = (float)($bal[0]->total_bal ?? 0.00);
        }
        $this->trendData = $trendData;

        return view('reports.aging.dashboard', $this->data);
    }
}
