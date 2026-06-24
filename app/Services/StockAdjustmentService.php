<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\ProductSerial;
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
     * @param string $category 'available', 'faulty', or 'in_transit'
     * @param string|null $referenceType
     * @param int|null $referenceId
     * @param string|null $remarks
     * @param array $serialNumbers
     * @return void
     * @throws \Exception
     */
    public function adjustStock(
        int $productId,
        int $warehouseId,
        float $quantity,
        string $type,
        string $category = 'available',
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $remarks = null,
        array $serialNumbers = []
    ): void {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be greater than zero.');
        }

        if (!in_array($type, ['in', 'out'])) {
            throw new \InvalidArgumentException('Invalid adjustment type. Must be "in" or "out".');
        }

        if (!in_array($category, ['available', 'faulty', 'in_transit'])) {
            throw new \InvalidArgumentException('Invalid stock category. Must be "available", "faulty", or "in_transit".');
        }

        $product = Product::findOrFail($productId);
        $warehouse = Warehouse::findOrFail($warehouseId);
        $companyId = $warehouse->company_id;

        $cleanedSerials = array_filter(array_map('trim', $serialNumbers));
        $snCount = count($cleanedSerials);

        if ($product->is_serialized) {
            if ($snCount != (int)$quantity) {
                throw new \Exception("The number of scanned serial numbers ({$snCount}) must match the adjustment quantity (" . (int)$quantity . ") for serialized products.");
            }
        }

        DB::transaction(function () use ($product, $productId, $warehouseId, $quantity, $type, $category, $referenceType, $referenceId, $remarks, $companyId, $cleanedSerials, $snCount) {
            // Validate serials existence and status on stock out
            if ($product->is_serialized && $type === 'out') {
                $statusMap = [
                    'available' => ProductSerial::STATUS_AVAILABLE,
                    'faulty' => ProductSerial::STATUS_FAULTY,
                    'in_transit' => ProductSerial::STATUS_IN_TRANSIT,
                ];
                $expectedStatus = $statusMap[$category] ?? ProductSerial::STATUS_AVAILABLE;

                $foundCount = ProductSerial::whereIn('serial_number', $cleanedSerials)
                    ->where('product_id', $productId)
                    ->where('warehouse_id', $warehouseId)
                    ->where('status', $expectedStatus)
                    ->count();

                if ($foundCount !== $snCount) {
                    throw new \Exception("One or more serial numbers do not exist at this location or are not in the '{$expectedStatus}' state.");
                }
            }

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
                $inventory->quantity_faulty = 0.00;
                $inventory->quantity_in_transit = 0.00;
            }

            $column = match ($category) {
                'faulty' => 'quantity_faulty',
                'in_transit' => 'quantity_in_transit',
                default => 'quantity',
            };

            $currentQty = (float) $inventory->$column;

            if ($type === 'in') {
                $newQty = $currentQty + $quantity;
            } else {
                $newQty = $currentQty - $quantity;
                if ($newQty < 0) {
                    throw new \Exception(__('messages.negativeStockError') ?: 'Negative stock is not allowed.');
                }
            }

            $inventory->$column = $newQty;
            $inventory->save();

            // Handle Serials database records
            if ($product->is_serialized && !empty($cleanedSerials)) {
                if ($type === 'in') {
                    $serialService = new SerialTrackingService();
                    $serialService->registerSerials($productId, $warehouseId, $cleanedSerials);
                } else {
                    // For manual stock out, delete the serial records to remove them from stock
                    ProductSerial::whereIn('serial_number', $cleanedSerials)->delete();
                }
            }

            // Record stock movement
            $movement = new StockMovement();
            $movement->company_id = $companyId;
            $movement->product_id = $productId;
            $movement->warehouse_id = $warehouseId;
            $movement->quantity = $quantity;
            $movement->balance_after = $newQty;
            $movement->type = $type;
            $movement->stock_category = $category;
            $movement->reference_type = $referenceType;
            $movement->reference_id = $referenceId;
            $movement->remarks = $remarks;
            $movement->save();
        });
    }
}
