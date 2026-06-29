<?php

namespace App\Services;

use App\Models\Warehouse;
use App\Models\Inventory;
use App\Models\ProductSerial;

class TransferValidationService
{
    /**
     * Validate warehouse states and connections.
     */
    public function validateWarehouseAvailability(int $sourceId, int $destId): void
    {
        if ($sourceId === $destId) {
            throw new \Exception("Source and destination warehouses cannot be the same.");
        }

        $source = Warehouse::findOrFail($sourceId);
        $dest = Warehouse::findOrFail($destId);

        if (!$source->is_active) {
            throw new \Exception("Source warehouse '{$source->name}' is inactive.");
        }

        if (!$dest->is_active) {
            throw new \Exception("Destination warehouse/outlet '{$dest->name}' is inactive.");
        }
    }

    /**
     * Validate product stock levels in source warehouse.
     */
    public function validateProductStock(int $productId, int $warehouseId, float $qty): void
    {
        $inventory = Inventory::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->first();

        $available = $inventory ? (float)$inventory->quantity : 0.00;

        if ($available < $qty) {
            throw new \Exception("Insufficient stock in source warehouse. Available: {$available}, Requested: {$qty}.");
        }
    }

    /**
     * Validate list of unique serial numbers.
     */
    public function validateSerials(int $productId, int $warehouseId, array $serialNumbers): void
    {
        if (count($serialNumbers) !== count(array_unique($serialNumbers))) {
            throw new \Exception("Duplicate serial numbers are not allowed in the transfer list.");
        }

        foreach ($serialNumbers as $sn) {
            $sn = trim($sn);
            if (empty($sn)) {
                continue;
            }

            $serial = ProductSerial::where('serial_number', $sn)
                ->where('product_id', $productId)
                ->first();

            if (!$serial) {
                throw new \Exception("Serial number '{$sn}' does not exist for the selected product.");
            }

            if ($serial->warehouse_id != $warehouseId) {
                throw new \Exception("Serial number '{$sn}' does not belong to the source warehouse.");
            }

            if ($serial->status !== ProductSerial::STATUS_AVAILABLE) {
                throw new \Exception("Serial number '{$sn}' is currently not available (current status: '{$serial->status}').");
            }
        }
    }
}
