<?php

namespace App\Listeners\Intake;

use App\Events\StockIntakeApproved;
use App\Models\Inventory;
use App\Models\StockMovement;

class LogStockMovement
{
    /**
     * Handle the event.
     */
    public function handle(StockIntakeApproved $event): void
    {
        $voucher = $event->voucher;
        $warehouseId = $voucher->warehouse_id;
        $companyId = $voucher->company_id;

        $voucher->load(['items']);

        foreach ($voucher->items as $item) {
            $productId = $item->product_id;
            $qty = (float) $item->quantity_received;

            if ($qty <= 0) {
                continue;
            }

            // Get balance after update
            $inventory = Inventory::where('product_id', $productId)
                ->where('warehouse_id', $warehouseId)
                ->first();

            $balanceAfter = $inventory ? $inventory->quantity : 0.00;

            StockMovement::create([
                'company_id' => $companyId,
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'quantity' => $qty,
                'balance_after' => $balanceAfter,
                'type' => 'in',
                'stock_category' => 'available',
                'reference_type' => 'stock_intake_voucher',
                'reference_id' => $voucher->id,
                'remarks' => "Intaken via Voucher #{$voucher->voucher_number}",
            ]);
        }
    }
}
