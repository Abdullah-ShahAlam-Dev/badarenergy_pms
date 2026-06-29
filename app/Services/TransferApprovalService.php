<?php

namespace App\Services;

use App\Models\StockTransfer;
use App\Models\ProductSerial;
use Illuminate\Support\Facades\DB;

class TransferApprovalService
{
    /**
     * Approve a pending transfer draft request.
     */
    public function approve(int $id, int $userId): bool
    {
        return DB::transaction(function () use ($id, $userId) {
            $transfer = StockTransfer::findOrFail($id);

            if (!in_array($transfer->status, [StockTransfer::STATUS_DRAFT, StockTransfer::STATUS_PENDING_APPROVAL])) {
                throw new \Exception("Only draft or pending transfers can be approved.");
            }

            $transfer->update([
                'status' => StockTransfer::STATUS_APPROVED,
                'approved_by' => $userId,
                'approved_at' => now(),
                'approved_ip' => request()->ip()
            ]);

            return true;
        });
    }

    /**
     * Reject a pending transfer draft request.
     */
    public function reject(int $id, int $userId, string $reason): bool
    {
        return DB::transaction(function () use ($id, $userId, $reason) {
            $transfer = StockTransfer::findOrFail($id);

            if (!in_array($transfer->status, [StockTransfer::STATUS_DRAFT, StockTransfer::STATUS_PENDING_APPROVAL])) {
                throw new \Exception("Only draft or pending transfers can be rejected.");
            }

            // Release all reserved serials back to available
            foreach ($transfer->items as $item) {
                foreach ($item->serials as $ts) {
                    $ts->serial->update(['status' => ProductSerial::STATUS_AVAILABLE]);
                }
            }

            $transfer->update([
                'status' => StockTransfer::STATUS_REJECTED,
                'approved_by' => $userId,
                'approved_at' => now(),
                'approved_ip' => request()->ip(),
                'reason' => $reason
            ]);

            return true;
        });
    }
}
