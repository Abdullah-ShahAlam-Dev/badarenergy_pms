<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\DealerLedger;
use Illuminate\Support\Facades\DB;

class DealerLedgerService
{
    /**
     * Sync invoice ledger entry (Debit).
     */
    public function syncInvoiceEntry(Invoice $invoice): void
    {
        // Only active/approved invoices (unpaid, paid, partial) create ledger entries.
        // Draft and canceled invoices should not have ledger entries.
        if (in_array($invoice->status, ['unpaid', 'paid', 'partial'])) {
            $dealerId = $invoice->client_id;
            if (!$dealerId && $invoice->project) {
                $dealerId = $invoice->project->client_id;
            }

            if (!$dealerId) {
                return; // No dealer associated
            }

            $date = $invoice->issue_date ?? now();
            $description = "Invoice #{$invoice->invoice_number} created";
            $debit = (float)$invoice->total;

            DB::transaction(function () use ($invoice, $dealerId, $date, $description, $debit) {
                DealerLedger::updateOrCreate(
                    [
                        'invoice_id' => $invoice->id,
                    ],
                    [
                        'company_id' => $invoice->company_id,
                        'dealer_id' => $dealerId,
                        'date' => $date,
                        'description' => $description,
                        'debit' => $debit,
                        'credit' => 0.00,
                    ]
                );

                $this->recalculateLedger($dealerId);
            });
        } else {
            // Delete ledger entry if it was previously active and now changed to draft/canceled
            $this->deleteInvoiceEntry($invoice->id);
        }
    }

    /**
     * Sync payment ledger entry (Credit).
     */
    public function syncPaymentEntry(Payment $payment): void
    {
        // Only complete payments create ledger entries.
        if ($payment->status === 'complete') {
            $dealerId = $payment->customer_id;
            if (!$dealerId && $payment->invoice) {
                $dealerId = $payment->invoice->client_id;
            }
            if (!$dealerId && $payment->project) {
                $dealerId = $payment->project->client_id;
            }

            if (!$dealerId) {
                return; // No dealer associated
            }

            $date = $payment->paid_on ?? now();
            $method = $payment->gateway ?? 'Offline';
            $description = "Payment received via {$method}" . ($payment->transaction_id ? " (Tx: {$payment->transaction_id})" : "");
            $credit = (float)$payment->amount;

            DB::transaction(function () use ($payment, $dealerId, $date, $description, $credit) {
                DealerLedger::updateOrCreate(
                    [
                        'payment_id' => $payment->id,
                    ],
                    [
                        'company_id' => $payment->company_id,
                        'dealer_id' => $dealerId,
                        'date' => $date,
                        'description' => $description,
                        'debit' => 0.00,
                        'credit' => $credit,
                    ]
                );

                $this->recalculateLedger($dealerId);
            });
        } else {
            // Delete ledger entry if payment status is not complete (e.g. pending/failed)
            $this->deletePaymentEntry($payment->id);
        }
    }

    /**
     * Delete invoice ledger entry.
     */
    public function deleteInvoiceEntry(int $invoiceId): void
    {
        $entry = DealerLedger::where('invoice_id', $invoiceId)->first();
        if ($entry) {
            $dealerId = $entry->dealer_id;
            DB::transaction(function () use ($entry, $dealerId) {
                $entry->delete();
                $this->recalculateLedger($dealerId);
            });
        }
    }

    /**
     * Delete payment ledger entry.
     */
    public function deletePaymentEntry(int $paymentId): void
    {
        $entry = DealerLedger::where('payment_id', $paymentId)->first();
        if ($entry) {
            $dealerId = $entry->dealer_id;
            DB::transaction(function () use ($entry, $dealerId) {
                $entry->delete();
                $this->recalculateLedger($dealerId);
            });
        }
    }

    /**
     * Recalculate ledger running balance chronologically for a dealer.
     */
    public function recalculateLedger(int $dealerId): void
    {
        DB::transaction(function () use ($dealerId) {
            $entries = DealerLedger::where('dealer_id', $dealerId)
                ->orderBy('date', 'asc')
                ->orderBy('id', 'asc')
                ->lockForUpdate()
                ->get();

            $runningBalance = 0.00;

            foreach ($entries as $entry) {
                $runningBalance = $runningBalance + (float)$entry->debit - (float)$entry->credit;
                
                DB::table('dealer_ledgers')
                    ->where('id', $entry->id)
                    ->update(['balance' => $runningBalance]);
            }
        });
    }
}
