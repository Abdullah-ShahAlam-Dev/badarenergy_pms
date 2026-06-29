<?php

namespace App\Services;

use App\Models\StockTransferLog;

class TransferHistoryService
{
    /**
     * Record a historical transition event in the stock_transfer_logs table.
     */
    public function logAction(int $transferId, int $userId, string $action, array $payload = []): bool
    {
        StockTransferLog::create([
            'transfer_id' => $transferId,
            'user_id' => $userId,
            'action' => $action,
            'ip_address' => request()->ip() ?: '127.0.0.1',
            'payload' => $payload,
            'created_at' => now()
        ]);

        return true;
    }
}
