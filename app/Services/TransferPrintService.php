<?php

namespace App\Services;

use App\Models\StockTransfer;

class TransferPrintService
{
    /**
     * Gather Challan printing context data.
     */
    public function getChallanData(int $id): array
    {
        $transfer = StockTransfer::with([
            'sourceWarehouse',
            'destinationWarehouse',
            'creator',
            'items.product',
            'items.serials.serial'
        ])->findOrFail($id);

        return [
            'transfer' => $transfer,
            'company' => company()
        ];
    }

    /**
     * Gather Goods Received Note (GRN) context data.
     */
    public function getGRNData(int $id): array
    {
        $transfer = StockTransfer::with([
            'sourceWarehouse',
            'destinationWarehouse',
            'receiver',
            'items.product',
            'items.serials.serial'
        ])->findOrFail($id);

        return [
            'transfer' => $transfer,
            'company' => company()
        ];
    }
}
