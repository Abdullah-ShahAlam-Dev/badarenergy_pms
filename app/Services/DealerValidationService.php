<?php

namespace App\Services;

use App\Models\User;
use App\Models\Invoice;
use Carbon\Carbon;

class DealerValidationService
{
    /**
     * Check if the dealer has sufficient credit limit for a new order/invoice amount.
     *
     * @param User $user
     * @param float $newAmount
     * @return array
     */
    public function validateCreditLimit(User $user, float $newAmount): array
    {
        $clientDetails = $user->clientDetails;

        if (!$clientDetails) {
            return ['status' => true, 'message' => ''];
        }

        $creditLimit = (float) $clientDetails->credit_limit;

        // If credit limit is 0 (Tier C by default or custom), check if it permits cash/advance sales
        // Running balance from ledger or unpaid invoices:
        // For Release 1, outstanding balance = sum of unpaid invoices.
        // We will calculate this dynamically.
        $outstandingBalance = $this->getOutstandingBalance($user);

        if (($outstandingBalance + $newAmount) > $creditLimit) {
            return [
                'status' => false,
                'message' => sprintf(
                    'Transaction blocked. Dealer has exceeded the credit limit. Credit Limit: %s, Current Outstanding: %s, New Amount: %s',
                    number_format($creditLimit, 2),
                    number_format($outstandingBalance, 2),
                    number_format($newAmount, 2)
                )
            ];
        }

        return ['status' => true, 'message' => ''];
    }

    /**
     * Check if the dealer has any overdue invoices beyond their allowed credit days.
     *
     * @param User $user
     * @return array
     */
    public function validateCreditDays(User $user): array
    {
        $clientDetails = $user->clientDetails;

        if (!$clientDetails) {
            return ['status' => true, 'message' => ''];
        }

        $creditDays = (int) $clientDetails->credit_days;

        // Find any unpaid invoice that has been overdue for more than allowed credit days
        $overdueInvoice = Invoice::where('client_id', $user->id)
            ->whereIn('status', ['unpaid', 'partial'])
            ->where('due_date', '<', Carbon::now()->subDays($creditDays))
            ->first();

        if ($overdueInvoice) {
            return [
                'status' => false,
                'message' => sprintf(
                    'Transaction blocked. Dealer has unpaid invoices older than allowed credit days (%d days). Overdue Invoice: %s, Due Date: %s',
                    $creditDays,
                    $overdueInvoice->invoice_number,
                    $overdueInvoice->due_date->format('Y-m-d')
                )
            ];
        }

        return ['status' => true, 'message' => ''];
    }

    /**
     * Get the outstanding balance of a dealer.
     *
     * @param User $user
     * @return float
     */
    public function getOutstandingBalance(User $user): float
    {
        // First try to check if dealer_ledgers table exists (TSK-7.1)
        try {
            if (\Schema::hasTable('dealer_ledgers')) {
                $lastLedger = \DB::table('dealer_ledgers')
                    ->where('dealer_id', $user->id)
                    ->orderBy('id', 'desc')
                    ->first();
                if ($lastLedger) {
                    return (float) $lastLedger->balance;
                }
            }
        } catch (\Exception $e) {
            // Fall back to unpaid invoices
        }

        // Fallback to sum of due invoices
        return (float) Invoice::where('client_id', $user->id)
            ->whereIn('status', ['unpaid', 'partial'])
            ->sum('due_amount');
    }
}
