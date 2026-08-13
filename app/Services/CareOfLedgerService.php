<?php

namespace App\Services;

use App\Models\CareOfLedger;
use Illuminate\Support\Facades\DB;

class CareOfLedgerService
{
    /**
     * Post a financial credit / settlement adjustment entry to a Care Of ledger.
     */
    public function postSettlement(int $careOfId, float $amount, ?string $referenceNumber = null, ?string $remarks = null): CareOfLedger
    {
        return DB::transaction(function () use ($careOfId, $amount, $referenceNumber, $remarks) {
            $companyId = company() ? company()->id : 1;

            $lastLedger = CareOfLedger::where('company_id', $companyId)
                ->where('care_of_id', $careOfId)
                ->lockForUpdate()
                ->orderBy('id', 'desc')
                ->first();

            $prevBalance = $lastLedger ? (float) $lastLedger->balance : 0.00;
            $newBalance = round($prevBalance - $amount, 2);

            return CareOfLedger::create([
                'company_id' => $companyId,
                'care_of_id' => $careOfId,
                'date' => now(),
                'transaction_type' => 'settlement',
                'reference_number' => $referenceNumber ?: ('SETTLE-' . date('Ymd-His')),
                'description' => 'Care Of Account Settlement / Adjustment Payment',
                'debit' => 0.00,
                'credit' => round($amount, 2),
                'balance' => $newBalance,
                'remarks' => $remarks,
                'created_by' => auth()->id(),
            ]);
        });
    }
}
