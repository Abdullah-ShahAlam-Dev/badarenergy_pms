<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\CreditNotes;
use App\Models\DealerLedger;
use App\Models\DealerLedgerVoucher;
use App\Models\DealerLedgerAuditLog;
use App\Models\Currency;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DealerLedgerService
{
    /**
     * Helper to write audit log entry.
     */
    public function writeAuditLog(?int $ledgerId, string $action, ?array $oldValues = null, ?array $newValues = null, ?string $reason = null): void
    {
        try {
            DealerLedgerAuditLog::create([
                'company_id' => company() ? company()->id : (auth()->user() ? auth()->user()->company_id : null),
                'ledger_id' => $ledgerId,
                'user_id' => auth()->user() ? auth()->user()->id : null,
                'ip_address' => request()->ip(),
                'action' => $action,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'reason' => $reason,
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to write ledger audit log: " . $e->getMessage());
        }
    }

    /**
     * Retrieve currency exchange rate.
     */
    protected function getExchangeRate(int $currencyId): float
    {
        $currency = Currency::find($currencyId);
        return $currency ? (float)$currency->exchange_rate : 1.000000;
    }

    /**
     * Sync invoice ledger entry (Debit).
     */
    public function syncInvoiceEntry(Invoice $invoice): void
    {
        // Only active/approved invoices (unpaid, paid, partial) create ledger entries.
        if (in_array($invoice->status, ['unpaid', 'paid', 'partial'])) {
            $dealerId = $invoice->client_id;
            if (!$dealerId && $invoice->project) {
                $dealerId = $invoice->project->client_id;
            }

            if (!$dealerId) {
                return;
            }

            $date = $invoice->issue_date ?? now();
            $description = "Invoice #{$invoice->invoice_number} created";
            $debit = (float)$invoice->total;
            $currencyId = $invoice->currency_id ?? (company() ? company()->currency_id : 1);
            $exchangeRate = $this->getExchangeRate($currencyId);

            DB::transaction(function () use ($invoice, $dealerId, $date, $description, $debit, $currencyId, $exchangeRate) {
                $oldEntry = DealerLedger::where('invoice_id', $invoice->id)->first();
                $oldValues = $oldEntry ? $oldEntry->toArray() : null;

                $entry = DealerLedger::updateOrCreate(
                    [
                        'invoice_id' => $invoice->id,
                        'is_reversed' => false,
                    ],
                    [
                        'company_id' => $invoice->company_id,
                        'branch_id' => null, // extensible for branches
                        'financial_year_id' => null, // extensible for financial years
                        'dealer_id' => $dealerId,
                        'date' => $date,
                        'transaction_type' => 'invoice',
                        'reference_number' => $invoice->invoice_number,
                        'exchange_rate' => $exchangeRate,
                        'debit' => $debit,
                        'credit' => 0.00,
                        'debit_base' => $debit * $exchangeRate,
                        'credit_base' => 0.00,
                        'remarks' => $invoice->note,
                        'created_by' => auth()->user() ? auth()->user()->id : $invoice->added_by,
                    ]
                );

                $newValues = $entry->toArray();
                $action = $oldEntry ? 'update' : 'create';
                $this->writeAuditLog($entry->id, $action, $oldValues, $newValues, "Sync invoice ledger debit entry.");

                $this->recalculateBalance($dealerId, $date);
            });
        } else {
            // Revert entry if invoice is changed to draft or cancelled
            $this->deleteInvoiceEntry($invoice->id);
        }
    }

    /**
     * Sync payment ledger entry (Credit).
     */
    public function syncPaymentEntry(Payment $payment): void
    {
        // Skip payments generated via Credit Notes to avoid double entry
        if ($payment->gateway === 'Credit Note' || !is_null($payment->credit_notes_id)) {
            return;
        }

        // Only complete payments create ledger entries.
        if ($payment->status === 'complete') {
            if ($payment->invoice && $payment->invoice->status === 'pending_approval') {
                return;
            }

            $dealerId = $payment->customer_id;
            if (!$dealerId && $payment->invoice) {
                $dealerId = $payment->invoice->client_id;
            }
            if (!$dealerId && $payment->project) {
                $dealerId = $payment->project->client_id;
            }

            if (!$dealerId) {
                return;
            }

            $date = $payment->paid_on ?? now();
            $method = $payment->gateway ?? 'Offline';
            $ref = $payment->transaction_id ?? ('PMT-' . $payment->id);
            $description = "Payment received via {$method}" . ($payment->transaction_id ? " (Tx: {$payment->transaction_id})" : "");
            $credit = (float)$payment->amount;
            $currencyId = $payment->currency_id ?? (company() ? company()->currency_id : 1);
            $exchangeRate = $this->getExchangeRate($currencyId);

            DB::transaction(function () use ($payment, $dealerId, $date, $description, $credit, $currencyId, $exchangeRate, $ref) {
                $oldEntry = DealerLedger::where('payment_id', $payment->id)->first();
                $oldValues = $oldEntry ? $oldEntry->toArray() : null;

                $entry = DealerLedger::updateOrCreate(
                    [
                        'payment_id' => $payment->id,
                        'is_reversed' => false,
                    ],
                    [
                        'company_id' => $payment->company_id,
                        'branch_id' => null,
                        'financial_year_id' => null,
                        'dealer_id' => $dealerId,
                        'date' => $date,
                        'transaction_type' => 'payment',
                        'reference_number' => $ref,
                        'exchange_rate' => $exchangeRate,
                        'debit' => 0.00,
                        'credit' => $credit,
                        'debit_base' => 0.00,
                        'credit_base' => $credit * $exchangeRate,
                        'remarks' => $payment->remarks,
                        'created_by' => auth()->user() ? auth()->user()->id : $payment->added_by,
                    ]
                );

                $newValues = $entry->toArray();
                $action = $oldEntry ? 'update' : 'create';
                $this->writeAuditLog($entry->id, $action, $oldValues, $newValues, "Sync payment ledger credit entry.");

                $this->recalculateBalance($dealerId, $date);
            });
        } else {
            // Revert entry if payment is cancelled or failed
            $this->deletePaymentEntry($payment->id);
        }
    }

    /**
     * Sync credit note (Sales Return) ledger entry (Credit).
     */
    public function syncCreditNoteEntry(CreditNotes $creditNote): void
    {
        $dealerId = $creditNote->client_id;
        if (!$dealerId && $creditNote->invoice) {
            $dealerId = $creditNote->invoice->client_id;
        }

        if (!$dealerId) {
            return;
        }

        $date = $creditNote->issue_date ?? now();
        $description = "Credit Note #{$creditNote->cn_number} created";
        $credit = (float)$creditNote->total;
        $currencyId = $creditNote->currency_id ?? (company() ? company()->currency_id : 1);
        $exchangeRate = $this->getExchangeRate($currencyId);

        DB::transaction(function () use ($creditNote, $dealerId, $date, $description, $credit, $currencyId, $exchangeRate) {
            $oldEntry = DealerLedger::where('credit_note_id', $creditNote->id)->first();
            $oldValues = $oldEntry ? $oldEntry->toArray() : null;

            $entry = DealerLedger::updateOrCreate(
                [
                    'credit_note_id' => $creditNote->id,
                    'is_reversed' => false,
                ],
                [
                    'company_id' => $creditNote->company_id,
                    'branch_id' => null,
                    'financial_year_id' => null,
                    'dealer_id' => $dealerId,
                    'date' => $date,
                    'transaction_type' => 'credit_note',
                    'reference_number' => $creditNote->cn_number,
                    'exchange_rate' => $exchangeRate,
                    'debit' => 0.00,
                    'credit' => $credit,
                    'debit_base' => 0.00,
                    'credit_base' => $credit * $exchangeRate,
                    'remarks' => $creditNote->note,
                    'created_by' => auth()->user() ? auth()->user()->id : $creditNote->added_by,
                ]
            );

            $newValues = $entry->toArray();
            $action = $oldEntry ? 'update' : 'create';
            $this->writeAuditLog($entry->id, $action, $oldValues, $newValues, "Sync credit note ledger credit entry.");

            $this->recalculateBalance($dealerId, $date);
        });
    }

    /**
     * Sync opening balance / adjustment voucher ledger entry.
     */
    public function syncVoucherEntry(DealerLedgerVoucher $voucher): void
    {
        if ($voucher->is_voided) {
            $this->deleteVoucherEntry($voucher->id);
            return;
        }

        $dealerId = $voucher->dealer_id;
        $date = $voucher->date;
        $amount = (float)$voucher->amount;
        $debit = $voucher->entry_type === 'debit' ? $amount : 0.00;
        $credit = $voucher->entry_type === 'credit' ? $amount : 0.00;
        $currencyId = company() ? company()->currency_id : 1;
        $exchangeRate = $this->getExchangeRate($currencyId);

        DB::transaction(function () use ($voucher, $dealerId, $date, $debit, $credit, $currencyId, $exchangeRate) {
            $oldEntry = DealerLedger::where('voucher_id', $voucher->id)->first();
            $oldValues = $oldEntry ? $oldEntry->toArray() : null;

            $entry = DealerLedger::updateOrCreate(
                [
                    'voucher_id' => $voucher->id,
                    'is_reversed' => false,
                ],
                [
                    'company_id' => $voucher->company_id,
                    'branch_id' => $voucher->branch_id,
                    'financial_year_id' => null,
                    'dealer_id' => $dealerId,
                    'date' => $date,
                    'transaction_type' => $voucher->type,
                    'reference_number' => $voucher->voucher_number,
                    'exchange_rate' => $exchangeRate,
                    'debit' => $debit,
                    'credit' => $credit,
                    'debit_base' => $debit * $exchangeRate,
                    'credit_base' => $credit * $exchangeRate,
                    'remarks' => $voucher->remarks,
                    'created_by' => $voucher->created_by,
                ]
            );

            $newValues = $entry->toArray();
            $action = $oldEntry ? 'update' : 'create';
            $this->writeAuditLog($entry->id, $action, $oldValues, $newValues, "Sync ledger voucher entry.");

            $this->recalculateBalance($dealerId, $date);
        });
    }

    /**
     * Post reversal entry instead of deleting an invoice entry.
     */
    public function deleteInvoiceEntry(int $invoiceId): void
    {
        DB::transaction(function () use ($invoiceId) {
            $entry = DealerLedger::where('invoice_id', $invoiceId)
                ->where('is_reversed', false)
                ->first();

            if ($entry) {
                $this->reverseLedgerEntry($entry, "Reversal due to Invoice cancellation/deletion");
            }
        });
    }

    /**
     * Post reversal entry instead of deleting a payment entry.
     */
    public function deletePaymentEntry(int $paymentId): void
    {
        DB::transaction(function () use ($paymentId) {
            $entry = DealerLedger::where('payment_id', $paymentId)
                ->where('is_reversed', false)
                ->first();

            if ($entry) {
                $this->reverseLedgerEntry($entry, "Reversal due to Payment cancellation/deletion");
            }
        });
    }

    /**
     * Post reversal entry instead of deleting a credit note entry.
     */
    public function deleteCreditNoteEntry(int $creditNoteId): void
    {
        DB::transaction(function () use ($creditNoteId) {
            $entry = DealerLedger::where('credit_note_id', $creditNoteId)
                ->where('is_reversed', false)
                ->first();

            if ($entry) {
                $this->reverseLedgerEntry($entry, "Reversal due to Credit Note cancellation/deletion");
            }
        });
    }

    /**
     * Post reversal entry instead of deleting a voucher entry.
     */
    public function deleteVoucherEntry(int $voucherId): void
    {
        DB::transaction(function () use ($voucherId) {
            $entry = DealerLedger::where('voucher_id', $voucherId)
                ->where('is_reversed', false)
                ->first();

            if ($entry) {
                $this->reverseLedgerEntry($entry, "Reversal due to Voucher voiding/deletion");
            }
        });
    }

    /**
     * Helper to reverse a specific ledger entry.
     */
    protected function reverseLedgerEntry(DealerLedger $entry, string $reason): void
    {
        DB::transaction(function () use ($entry, $reason) {
            $oldValues = $entry->toArray();

            // Create Reversal row
            $reversal = DealerLedger::create([
                'company_id' => $entry->company_id,
                'branch_id' => $entry->branch_id,
                'financial_year_id' => $entry->financial_year_id,
                'dealer_id' => $entry->dealer_id,
                'invoice_id' => $entry->invoice_id,
                'payment_id' => $entry->payment_id,
                'credit_note_id' => $entry->credit_note_id,
                'voucher_id' => $entry->voucher_id,
                'date' => now(),
                'transaction_type' => 'reversal',
                'reference_number' => 'REV-' . $entry->reference_number,
                'exchange_rate' => $entry->exchange_rate,
                'debit' => $entry->credit, // reverse credit with debit
                'credit' => $entry->debit, // reverse debit with credit
                'debit_base' => $entry->credit_base,
                'credit_base' => $entry->debit_base,
                'balance' => 0.00, // will be computed in recalculateBalance
                'remarks' => $reason,
                'created_by' => auth()->user() ? auth()->user()->id : $entry->created_by,
            ]);

            // Mark original reversed
            $entry->is_reversed = true;
            $entry->reversal_entry_id = $reversal->id;
            $entry->save();

            $newValues = $entry->toArray();
            $this->writeAuditLog($entry->id, 'reverse', $oldValues, $newValues, $reason);

            $this->recalculateBalance($entry->dealer_id, $entry->date);
        });
    }

    /**
     * Optimized delta-based balance recalculation.
     */
    public function recalculateBalance(int $dealerId, $startDate = null): void
    {
        DB::transaction(function () use ($dealerId, $startDate) {
            $query = DealerLedger::where('dealer_id', $dealerId)
                ->orderBy('date', 'asc')
                ->orderBy('id', 'asc')
                ->lockForUpdate();

            if (!is_null($startDate)) {
                // Find balance of the immediately preceding row
                $prevRow = DealerLedger::where('dealer_id', $dealerId)
                    ->where('date', '<', $startDate)
                    ->orderBy('date', 'desc')
                    ->orderBy('id', 'desc')
                    ->first();

                $runningBalance = $prevRow ? (float)$prevRow->balance : 0.00;

                // Load all entries from the start date onwards
                $entries = $query->where('date', '>=', $startDate)->get();
            } else {
                $runningBalance = 0.00;
                $entries = $query->get();
            }

            foreach ($entries as $entry) {
                $runningBalance = $runningBalance + (float)$entry->debit - (float)$entry->credit;
                
                DB::table('dealer_ledgers')
                    ->where('id', $entry->id)
                    ->update(['balance' => $runningBalance]);
            }
        });
    }

    /**
     * Compute current outstanding balance for a dealer.
     */
    public function calculateOutstanding(int $dealerId): float
    {
        $lastRow = DealerLedger::where('dealer_id', $dealerId)
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        return $lastRow ? (float)$lastRow->balance : 0.00;
    }
}
