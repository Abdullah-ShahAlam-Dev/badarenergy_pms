<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;

class StockAdjustmentService
{
    /**
     * Adjust the stock level of a product in a warehouse.
     *
     * @param int $productId
     * @param int $warehouseId
     * @param float $quantity
     * @param string $type 'in' or 'out'
     * @param string|null $referenceType
     * @param int|null $referenceId
     * @param string|null $remarks
     * @return void
     * @throws \Exception
     */
    public function adjustStock(
        int $productId,
        int $warehouseId,
        float $quantity,
        string $type,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $remarks = null
    ): void {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be greater than zero.');
        }

        if (!in_array($type, ['in', 'out'])) {
            throw new \InvalidArgumentException('Invalid adjustment type. Must be "in" or "out".');
        }

        $warehouse = Warehouse::findOrFail($warehouseId);
        $companyId = $warehouse->company_id;

        DB::transaction(function () use ($productId, $warehouseId, $quantity, $type, $referenceType, $referenceId, $remarks, $companyId) {
            // Find existing or lock for update
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
            }

            $currentQty = (float) $inventory->quantity;

            if ($type === 'in') {
                $newQty = $currentQty + $quantity;
            } else {
                $newQty = $currentQty - $quantity;
                if ($newQty < 0) {
                    throw new \Exception(__('messages.negativeStockError') ?: 'Negative stock is not allowed.');
                }
            }

            $inventory->quantity = $newQty;
            $inventory->save();

            // Record stock movement
            $movement = new StockMovement();
            $movement->company_id = $companyId;
            $movement->product_id = $productId;
            $movement->warehouse_id = $warehouseId;
            $movement->quantity = $quantity;
            $movement->balance_after = $newQty;
            $movement->type = $type;
            $movement->reference_type = $referenceType;
            $movement->reference_id = $referenceId;
            $movement->remarks = $remarks;
            $movement->save();
        });
    }
}
