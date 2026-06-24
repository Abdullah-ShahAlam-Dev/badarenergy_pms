<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductSerial;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;

class SerialTrackingService
{
    /**
     * Register new serial numbers for a product in a warehouse.
     */
    public function registerSerials(int $productId, int $warehouseId, array $serialNumbers): void
    {
        $product = Product::findOrFail($productId);
        if (!$product->is_serialized) {
            return;
        }

        $warehouse = Warehouse::findOrFail($warehouseId);
        $companyId = $warehouse->company_id;

        DB::transaction(function () use ($productId, $warehouseId, $serialNumbers, $companyId) {
            foreach ($serialNumbers as $sn) {
                $sn = trim($sn);
                if (empty($sn)) {
                    continue;
                }

                // Check duplicate
                $exists = ProductSerial::where('serial_number', $sn)->exists();
                if ($exists) {
                    throw new \Exception("Serial number '{$sn}' is already registered in the system.");
                }

                ProductSerial::create([
                    'company_id' => $companyId,
                    'product_id' => $productId,
                    'warehouse_id' => $warehouseId,
                    'serial_number' => $sn,
                    'status' => ProductSerial::STATUS_AVAILABLE,
                ]);
            }
        });
    }

    /**
     * Update the warehouse location of serial numbers (e.g. during inter-warehouse transfer).
     */
    public function transferSerials(array $serialNumbers, int $toWarehouseId): void
    {
        $warehouse = Warehouse::findOrFail($toWarehouseId);

        DB::transaction(function () use ($serialNumbers, $toWarehouseId) {
            foreach ($serialNumbers as $sn) {
                $sn = trim($sn);
                if (empty($sn)) {
                    continue;
                }

                $serial = ProductSerial::where('serial_number', $sn)->first();
                if (!$serial) {
                    throw new \Exception("Serial number '{$sn}' does not exist.");
                }

                if ($serial->status !== ProductSerial::STATUS_AVAILABLE && $serial->status !== ProductSerial::STATUS_IN_TRANSIT) {
                    throw new \Exception("Serial number '{$sn}' cannot be transferred because its status is '{$serial->status}'.");
                }

                $serial->warehouse_id = $toWarehouseId;
                $serial->status = ProductSerial::STATUS_AVAILABLE;
                $serial->save();
            }
        });
    }

    /**
     * Mark serial numbers as sold and record the sales invoice association.
     */
    public function sellSerials(array $serialNumbers, int $invoiceId): void
    {
        DB::transaction(function () use ($serialNumbers, $invoiceId) {
            foreach ($serialNumbers as $sn) {
                $sn = trim($sn);
                if (empty($sn)) {
                    continue;
                }

                $serial = ProductSerial::where('serial_number', $sn)->first();
                if (!$serial) {
                    throw new \Exception("Serial number '{$sn}' does not exist.");
                }

                if ($serial->status !== ProductSerial::STATUS_AVAILABLE) {
                    throw new \Exception("Serial number '{$sn}' is not available for sale (current status: '{$serial->status}').");
                }

                $serial->status = ProductSerial::STATUS_SOLD;
                $serial->invoice_id = $invoiceId;
                $serial->warranty_expires_at = now()->addYear(); // Default 1 year warranty
                $serial->save();
            }
        });
    }

    /**
     * Mark a serial number as faulty/damaged (e.g. during a warranty claim).
     */
    public function markSerialFaulty(string $serialNumber): void
    {
        $serialNumber = trim($serialNumber);
        $serial = ProductSerial::where('serial_number', $serialNumber)->first();

        if (!$serial) {
            throw new \Exception("Serial number '{$serialNumber}' does not exist.");
        }

        $serial->status = ProductSerial::STATUS_FAULTY;
        $serial->save();
    }

    /**
     * Mark a faulty serial number as repaired and make it available again.
     */
    public function repairSerial(string $serialNumber): void
    {
        $serialNumber = trim($serialNumber);
        $serial = ProductSerial::where('serial_number', $serialNumber)->first();

        if (!$serial) {
            throw new \Exception("Serial number '{$serialNumber}' does not exist.");
        }

        if ($serial->status !== ProductSerial::STATUS_FAULTY) {
            throw new \Exception("Serial number '{$serialNumber}' is not marked as faulty (current status: '{$serial->status}').");
        }

        $serial->status = ProductSerial::STATUS_AVAILABLE;
        $serial->save();
    }
}
