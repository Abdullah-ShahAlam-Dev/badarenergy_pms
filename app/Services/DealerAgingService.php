<?php

namespace App\Services;

use App\Models\User;
use App\Models\DealerAgingSnapshot;
use App\Models\DealerAgingBucket;
use App\Models\DealerLedger;
use App\Models\InvoiceSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DealerAgingService
{
    /**
     * Calculate and return the aging buckets for a single dealer.
     * Optionally saves the result into the dealer_aging_snapshots table.
     */
    public function calculateAging(int $dealerId, bool $saveToSnapshot = true): array
    {
        $dealer = User::with(['clientDetails'])->find($dealerId);
        if (!$dealer || !$dealer->clientDetails) {
            return [];
        }

        $companyId = $dealer->company_id;

        // 1. Get aging basis setting
        $invoiceSettings = InvoiceSetting::where('company_id', $companyId)->first();
        $basis = $invoiceSettings ? $invoiceSettings->aging_basis : 'due_date';

        // 2. Get aging buckets
        $buckets = DealerAgingBucket::where('company_id', $companyId)
            ->orderBy('sequence', 'asc')
            ->get();

        if ($buckets->isEmpty()) {
            // Fallback default buckets
            $buckets = collect([
                new DealerAgingBucket(['name' => '1-15 Days', 'min_days' => 1, 'max_days' => 15, 'sequence' => 1]),
                new DealerAgingBucket(['name' => '16-30 Days', 'min_days' => 16, 'max_days' => 30, 'sequence' => 2]),
                new DealerAgingBucket(['name' => '31-60 Days', 'min_days' => 31, 'max_days' => 60, 'sequence' => 3]),
                new DealerAgingBucket(['name' => '61-90 Days', 'min_days' => 61, 'max_days' => 90, 'sequence' => 4]),
                new DealerAgingBucket(['name' => '90+ Days', 'min_days' => 91, 'max_days' => null, 'sequence' => 5]),
            ]);
        }

        // 3. Retrieve all active (non-reversed) ledger entries
        $ledgerEntries = DealerLedger::where('dealer_id', $dealerId)
            ->where('is_reversed', false)
            ->orderBy('date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // Separate debits and credits
        $debits = [];
        $totalCredits = 0.00;

        foreach ($ledgerEntries as $entry) {
            if ((float)$entry->debit > 0) {
                // Determine transaction / calculation date based on setting
                $calcDate = $entry->date;
                if ($entry->invoice_id && $entry->invoice) {
                    $calcDate = ($basis === 'invoice_date') 
                        ? $entry->invoice->issue_date 
                        : $entry->invoice->due_date;
                }

                $debits[] = [
                    'debit' => (float)$entry->debit,
                    'calc_date' => Carbon::parse($calcDate)->startOfDay(),
                ];
            }

            if ((float)$entry->credit > 0) {
                $totalCredits += (float)$entry->credit;
            }
        }

        // Initialize bucket sums
        $bucketData = [
            'current' => 0.00,
            'bucket_1_15' => 0.00,
            'bucket_16_30' => 0.00,
            'bucket_31_60' => 0.00,
            'bucket_61_90' => 0.00,
            'bucket_91_plus' => 0.00,
        ];

        $today = Carbon::now()->startOfDay();

        // 4. Perform FIFO Allocation
        foreach ($debits as $deb) {
            $amount = $deb['debit'];
            $calcDate = $deb['calc_date'];

            // Clear against credit pool
            if ($totalCredits > 0) {
                if ($totalCredits >= $amount) {
                    $totalCredits -= $amount;
                    $amount = 0.00;
                } else {
                    $amount -= $totalCredits;
                    $totalCredits = 0.00;
                }
            }

            if ($amount > 0) {
                // Calculate age in days
                if ($calcDate->greaterThanOrEqualTo($today)) {
                    $bucketData['current'] += $amount;
                } else {
                    $daysOverdue = $calcDate->diffInDays($today);

                    // Allocate to buckets
                    $allocated = false;
                    foreach ($buckets as $b) {
                        $min = $b->min_days;
                        $max = $b->max_days;

                        if ($daysOverdue >= $min && (is_null($max) || $daysOverdue <= $max)) {
                            $key = $this->getBucketKey($b->sequence);
                            $bucketData[$key] += $amount;
                            $allocated = true;
                            break;
                        }
                    }

                    // Fallback to current if not matched (should not happen)
                    if (!$allocated) {
                        $bucketData['current'] += $amount;
                    }
                }
            }
        }

        // Handle negative credit pool (if dealer has overall credit balance)
        if ($totalCredits > 0) {
            $bucketData['current'] -= $totalCredits;
        }

        // Compute total outstanding
        $outstanding = array_sum($bucketData);

        // Credit utilization calculation
        $creditLimit = (float)($dealer->clientDetails->credit_limit ?? 0.00);
        $creditUtilization = 0.00;
        if ($creditLimit > 0) {
            // Cap utilization calculation between 0 and 999.99 for database safety
            $creditUtilization = round(($outstanding / $creditLimit) * 100, 2);
            $creditUtilization = max(0.00, min(999.99, $creditUtilization));
        }

        // Get last payment date
        $lastPayment = DealerLedger::where('dealer_id', $dealerId)
            ->where('credit', '>', 0)
            ->orderBy('date', 'desc')
            ->first();
        
        $lastPaymentDate = $lastPayment ? $lastPayment->date : null;
        $daysSincePayment = $lastPaymentDate ? Carbon::parse($lastPaymentDate)->startOfDay()->diffInDays(now()->startOfDay()) : null;

        $result = [
            'outstanding' => $outstanding,
            'current' => $bucketData['current'],
            'bucket_1_15' => $bucketData['bucket_1_15'],
            'bucket_16_30' => $bucketData['bucket_16_30'],
            'bucket_31_60' => $bucketData['bucket_31_60'],
            'bucket_61_90' => $bucketData['bucket_61_90'],
            'bucket_91_plus' => $bucketData['bucket_91_plus'],
            'credit_utilization' => $creditUtilization,
            'last_payment_date' => $lastPaymentDate,
            'days_since_payment' => $daysSincePayment,
        ];

        // 5. Save to snapshot table
        if ($saveToSnapshot) {
            DealerAgingSnapshot::updateOrCreate(
                ['dealer_id' => $dealerId],
                [
                    'company_id' => $companyId,
                    'outstanding' => $result['outstanding'],
                    'current' => $result['current'],
                    'bucket_1_15' => $result['bucket_1_15'],
                    'bucket_16_30' => $result['bucket_16_30'],
                    'bucket_31_60' => $result['bucket_31_60'],
                    'bucket_61_90' => $result['bucket_61_90'],
                    'bucket_91_plus' => $result['bucket_91_plus'],
                    'credit_utilization' => $result['credit_utilization'],
                    'last_payment_date' => $result['last_payment_date'],
                    'days_since_payment' => $result['days_since_payment'],
                    'last_updated_at' => now(),
                ]
            );
        }

        return $result;
    }

    /**
     * Loop through all dealers and regenerate their aging snapshots.
     */
    public function regenerateAllSnapshots(): void
    {
        // Chunk process all users with the client role
        User::withoutGlobalScope(ActiveScope::class)
            ->whereHas('roles', function ($q) {
                $q->where('name', 'client');
            })
            ->chunk(100, function ($dealers) {
                foreach ($dealers as $dealer) {
                    try {
                        $this->calculateAging($dealer->id, true);
                    } catch (\Exception $e) {
                        Log::error("Failed to calculate aging snapshot for dealer ID {$dealer->id}: " . $e->getMessage());
                    }
                }
            });
    }

    /**
     * Map bucket sequence index to the snapshot DB columns.
     */
    protected function getBucketKey(int $sequence): string
    {
        switch ($sequence) {
            case 1: return 'bucket_1_15';
            case 2: return 'bucket_16_30';
            case 3: return 'bucket_31_60';
            case 4: return 'bucket_61_90';
            default: return 'bucket_91_plus';
        }
    }
}
