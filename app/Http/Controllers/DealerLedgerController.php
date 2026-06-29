<?php

namespace App\Http\Controllers;

use App\DataTables\DealerLedgerSummaryDataTable;
use App\Helper\Reply;
use App\Http\Requests\Ledger\StoreAdjustmentRequest;
use App\Models\User;
use App\Models\ClientDetails;
use App\Models\DealerLedger;
use App\Models\DealerLedgerVoucher;
use App\Services\DealerLedgerService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DealerLedgerController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'Dealer Ledger & Receivables';
        $this->middleware(function ($request, $next) {
            abort_403(in_array('client', user_roles()));
            return $next($request);
        });
    }

    /**
     * Receivables Summary Dashboard.
     */
    public function index(DealerLedgerSummaryDataTable $dataTable)
    {
        $this->dealers = User::allClients();
        $this->salespersons = User::allEmployees();
        $this->cities = ClientDetails::whereNotNull('city')->groupBy('city')->pluck('city');
        $this->activeTab = 'summary';

        return $dataTable->render('dealer-ledgers.index', $this->data);
    }

    /**
     * Standalone Detailed Ledger View.
     */
    public function show($id, Request $request)
    {
        $this->dealer = User::with(['clientDetails', 'clientDetails.salesperson'])->findOrFail($id);
        
        $viewPermission = user()->permission('view_invoices');
        if ($viewPermission == 'none') {
            abort(403);
        }
        
        if ($viewPermission != 'all' && !in_array('admin', user_roles())) {
            abort_403(!$this->dealer->clientDetails || $this->dealer->clientDetails->salesperson_id !== user()->id);
        }
        
        $startDate = $request->startDate ? Carbon::createFromFormat(company()->date_format, $request->startDate)->startOfDay() : null;
        $endDate = $request->endDate ? Carbon::createFromFormat(company()->date_format, $request->endDate)->endOfDay() : null;

        // Opening Balance calculation
        $openingDebit = 0.00;
        $openingCredit = 0.00;
        if ($startDate) {
            $openingDebit = (float)DealerLedger::where('dealer_id', $id)->where('date', '<', $startDate)->sum('debit');
            $openingCredit = (float)DealerLedger::where('dealer_id', $id)->where('date', '<', $startDate)->sum('credit');
        }
        $this->openingBalance = $openingDebit - $openingCredit;

        // Transaction entries querying
        $query = DealerLedger::with(['invoice', 'payment', 'creditNote', 'voucher'])
            ->where('dealer_id', $id);

        if ($startDate) {
            $query->where('date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('date', '<=', $endDate);
        }

        $this->ledgerEntries = $query->orderBy('date', 'asc')->orderBy('id', 'asc')->get();

        // Totals within range
        $this->totalDebit = DealerLedger::where('dealer_id', $id);
        $this->totalCredit = DealerLedger::where('dealer_id', $id);
        if ($startDate) {
            $this->totalDebit->where('date', '>=', $startDate);
            $this->totalCredit->where('date', '>=', $startDate);
        }
        if ($endDate) {
            $this->totalDebit->where('date', '<=', $endDate);
            $this->totalCredit->where('date', '<=', $endDate);
        }
        $this->totalDebit = (float)$this->totalDebit->sum('debit');
        $this->totalCredit = (float)$this->totalCredit->sum('credit');

        // Net Outstanding
        $ledgerService = new DealerLedgerService();
        $this->currentBalance = $ledgerService->calculateOutstanding($id);

        // Last Payment date
        $lastPayment = DealerLedger::where('dealer_id', $id)->where('credit', '>', 0)->orderBy('date', 'desc')->first();
        $this->lastPaymentDate = $lastPayment ? $lastPayment->date : null;

        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->activeTab = 'detail';

        return view('dealer-ledgers.show', $this->data);
    }

    /**
     * Printable Detailed Ledger.
     */
    public function print($id, Request $request)
    {
        $this->dealer = User::with(['clientDetails', 'clientDetails.salesperson'])->findOrFail($id);

        $viewPermission = user()->permission('view_invoices');
        if ($viewPermission == 'none') {
            abort(403);
        }

        if ($viewPermission != 'all' && !in_array('admin', user_roles())) {
            abort_403(!$this->dealer->clientDetails || $this->dealer->clientDetails->salesperson_id !== user()->id);
        }

        $startDate = $request->startDate ? Carbon::createFromFormat(company()->date_format, $request->startDate)->startOfDay() : null;
        $endDate = $request->endDate ? Carbon::createFromFormat(company()->date_format, $request->endDate)->endOfDay() : null;

        $openingDebit = 0.00;
        $openingCredit = 0.00;
        if ($startDate) {
            $openingDebit = (float)DealerLedger::where('dealer_id', $id)->where('date', '<', $startDate)->sum('debit');
            $openingCredit = (float)DealerLedger::where('dealer_id', $id)->where('date', '<', $startDate)->sum('credit');
        }
        $this->openingBalance = $openingDebit - $openingCredit;

        $query = DealerLedger::with(['invoice', 'payment', 'creditNote', 'voucher'])
            ->where('dealer_id', $id);

        if ($startDate) {
            $query->where('date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('date', '<=', $endDate);
        }

        $this->ledgerEntries = $query->orderBy('date', 'asc')->orderBy('id', 'asc')->get();

        $this->totalDebit = DealerLedger::where('dealer_id', $id);
        $this->totalCredit = DealerLedger::where('dealer_id', $id);
        if ($startDate) {
            $this->totalDebit->where('date', '>=', $startDate);
            $this->totalCredit->where('date', '>=', $startDate);
        }
        if ($endDate) {
            $this->totalDebit->where('date', '<=', $endDate);
            $this->totalCredit->where('date', '<=', $endDate);
        }
        $this->totalDebit = (float)$this->totalDebit->sum('debit');
        $this->totalCredit = (float)$this->totalCredit->sum('credit');

        $ledgerService = new DealerLedgerService();
        $this->currentBalance = $ledgerService->calculateOutstanding($id);

        $lastPayment = DealerLedger::where('dealer_id', $id)->where('credit', '>', 0)->orderBy('date', 'desc')->first();
        $this->lastPaymentDate = $lastPayment ? $lastPayment->date : null;

        $this->startDate = $startDate;
        $this->endDate = $endDate;

        return view('dealer-ledgers.print', $this->data);
    }

    /**
     * Show Modal for creating adjustments.
     */
    public function createAdjustment()
    {
        $this->dealers = User::allClients();
        return view('dealer-ledgers.ajax.create_adjustment', $this->data);
    }

    /**
     * Store manual adjustment voucher.
     */
    public function storeAdjustment(StoreAdjustmentRequest $request)
    {
        // Standard user role checking or adjustment permission
        abort_403(!in_array('admin', user_roles()));

        DB::transaction(function () use ($request) {
            $lastId = DealerLedgerVoucher::count() + 1;
            $voucherNum = 'JV-' . now()->format('Ymd') . '-' . str_pad($lastId, 4, '0', STR_PAD_LEFT);

            DealerLedgerVoucher::create([
                'company_id' => company()->id,
                'dealer_id' => $request->dealer_id,
                'voucher_number' => $voucherNum,
                'type' => $request->type,
                'amount' => round($request->amount, 2),
                'entry_type' => $request->entry_type,
                'date' => Carbon::parse($request->date),
                'remarks' => strip_tags($request->remarks),
                'created_by' => user()->id,
            ]);
        });

        return Reply::success("Adjustment Voucher posted successfully.");
    }
}
