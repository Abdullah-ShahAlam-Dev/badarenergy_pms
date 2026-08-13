<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\User;
use App\Models\CareOfLedger;
use App\Services\CareOfLedgerService;
use Illuminate\Http\Request;
use Exception;

class CareOfLedgerController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'Care Of / Owner Ledgers';
        $this->activeMenu = 'care-of-ledgers';

        $this->middleware(function ($request, $next) {
            abort_403(!in_array('admin', user_roles()) && user()->permission('view_order') == 'none');
            return $next($request);
        });
    }

    public function index()
    {
        $companyId = company() ? company()->id : 1;

        $this->careOfUsers = User::whereHas('careOfLedgers', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })
            ->with(['careOfLedgers' => function ($q) use ($companyId) {
                $q->where('company_id', $companyId)->orderBy('id', 'desc');
            }])
            ->get()
            ->map(function ($user) {
                $lastLedger = $user->careOfLedgers->first();
                $user->total_debit = $user->careOfLedgers->sum('debit');
                $user->total_credit = $user->careOfLedgers->sum('credit');
                $user->current_balance = $lastLedger ? $lastLedger->balance : 0.00;
                return $user;
            });

        return view('care-of-ledgers.index', $this->data);
    }

    public function show($careOfId)
    {
        $companyId = company() ? company()->id : 1;
        $this->careOfUser = User::findOrFail($careOfId);
        $this->ledgers = CareOfLedger::where('company_id', $companyId)
            ->where('care_of_id', $careOfId)
            ->with(['order', 'deliveryOrder', 'gatePassRequest', 'creator'])
            ->orderBy('id', 'asc')
            ->get();

        $this->currentBalance = $this->ledgers->last() ? $this->ledgers->last()->balance : 0.00;

        return view('care-of-ledgers.show', $this->data);
    }

    public function postSettlement(Request $request, $careOfId)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'reference_number' => 'nullable|string|max:100',
            'remarks' => 'nullable|string|max:500',
        ]);

        try {
            $service = app(CareOfLedgerService::class);
            $service->postSettlement((int) $careOfId, (float) $request->amount, $request->reference_number, $request->remarks);

            return Reply::success('Settlement / Adjustment posted to Care Of Ledger successfully.');
        } catch (Exception $e) {
            return Reply::error($e->getMessage());
        }
    }
}
