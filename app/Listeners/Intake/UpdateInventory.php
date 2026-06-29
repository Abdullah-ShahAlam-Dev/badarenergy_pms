<?php

namespace App\Listeners\Intake;

use App\Events\StockIntakeApproved;
use App\Models\Inventory;
use Illuminate\Support\Facades\DB;

class UpdateInventory
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
            $unitCost = (float) $item->unit_cost;

            if ($qty <= 0) {
                continue;
            }

            $inventory = Inventory::where('product_id', $productId)
                ->where('warehouse_id', $warehouseId)
                ->lockForUpdate()
                ->first();

            if (!$inventory) {
                $inventory = new Inventory();
                $inventory->company_id = $companyId;
                $inventory->product_id = $productId;
                $inventory->warehouse_id = $warehouseId;
                $inventory->quantity = 0.00;
                $inventory->quantity_faulty = 0.00;
                $inventory->quantity_in_transit = 0.00;
                $inventory->average_cost = $unitCost;
            }

            // Weighted Average Cost (WAC) formula (Amendment 3 from Phase 3: renamed inventories.unit_cost to average_cost)
            $currentQty = (float) $inventory->quantity;
            $currentWac = (float) $inventory->average_cost;

            $newQty = $currentQty + $qty;
            if ($newQty > 0) {
                $totalVal = ($currentQty * $currentWac) + ($qty * $unitCost);
                $inventory->average_cost = $totalVal / $newQty;
            } else {
                $inventory->average_cost = $unitCost;
            }

            $inventory->quantity = $newQty;
            $inventory->save();
        }
    }
}
