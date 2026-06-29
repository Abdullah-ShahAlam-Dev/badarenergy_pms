<?php

namespace App\Services;

use App\Models\StockTransfer;
use App\Models\Inventory;
use App\Models\ProductSerial;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class TransferDispatchService
{
    /**
     * Dispatch products for transfer and set shipping details.
     */
    public function dispatch(int $id, int $userId, array $logisticData): bool
    {
        return DB::transaction(function () use ($id, $userId, $logisticData) {
            $transfer = StockTransfer::findOrFail($id);

            // If approval is not required, it can transition directly from Draft
            $wmsSetting = DB::table('wms_settings')->where('company_id', $transfer->company_id)->first();
            $approvalRequired = $wmsSetting ? $wmsSetting->transfer_approval_required : false;

            $validStatuses = [StockTransfer::STATUS_APPROVED];
            if (!$approvalRequired) {
                $validStatuses[] = StockTransfer::STATUS_DRAFT;
            }

            if (!in_array($transfer->status, $validStatuses)) {
                throw new \Exception("Stock transfer is not in an dispatchable state (current status: {$transfer->status}).");
            }

            foreach ($transfer->items as $item) {
                $productId = $item->product_id;
                $quantity = (float)$item->quantity;

                // 1. Lock and deduct available stock from source warehouse
                $inventory = Inventory::where('product_id', $productId)
                    ->where('warehouse_id', $transfer->source_warehouse_id)
                    ->lockForUpdate()
                    ->first();

                if (!$inventory || (float)$inventory->quantity < $quantity) {
                    throw new \Exception("Insufficient stock to dispatch product ID: {$productId}.");
                }

                $newQtyAvailable = (float)$inventory->quantity - $quantity;
                $newQtyTransit = (float)$inventory->quantity_in_transit + $quantity;

                $inventory->quantity = $newQtyAvailable;
                $inventory->quantity_in_transit = $newQtyTransit;
                $inventory->save();

                // 2. Lock and update product serials status to in_transit
                foreach ($item->serials as $ts) {
                    $serial = $ts->serial;
                    if ($serial->status !== 'reserved') {
                        throw new \Exception("Serial number '{$serial->serial_number}' is not reserved (status: {$serial->status}).");
                    }
                    $serial->status = ProductSerial::STATUS_IN_TRANSIT;
                    $serial->save();

                    $ts->update(['status' => 'dispatched']);
                }

                // 3. Write Stock Movements (out & transit)
                // Source warehouse deduction (out)
                $movementOut = new StockMovement();
                $movementOut->company_id = $transfer->company_id;
                $movementOut->product_id = $productId;
                $movementOut->warehouse_id = $transfer->source_warehouse_id;
                $movementOut->quantity = $quantity;
                $movementOut->balance_after = $newQtyAvailable;
                $movementOut->type = 'out';
                $movementOut->stock_category = 'available';
                $movementOut->reference_type = 'stock_transfers';
                $movementOut->reference_id = $transfer->id;
                $movementOut->remarks = "Stock transfer dispatch to warehouse ID: {$transfer->destination_warehouse_id}";
                $movementOut->save();

                // Source warehouse transit increase (in)
                $movementTransit = new StockMovement();
                $movementTransit->company_id = $transfer->company_id;
                $movementTransit->product_id = $productId;
                $movementTransit->warehouse_id = $transfer->source_warehouse_id;
                $movementTransit->quantity = $quantity;
                $movementTransit->balance_after = $newQtyTransit;
                $movementTransit->type = 'in';
                $movementTransit->stock_category = 'transit';
                $movementTransit->reference_type = 'stock_transfers';
                $movementTransit->reference_id = $transfer->id;
                $movementTransit->remarks = "Stock placed in-transit for transfer #{$transfer->transfer_number}";
                $movementTransit->save();
            }

            // Update transfer state
            $transfer->update([
                'status' => StockTransfer::STATUS_DISPATCHED,
                'vehicle_number' => $logisticData['vehicle_number'] ?? $transfer->vehicle_number,
                'driver_name' => $logisticData['driver_name'] ?? $transfer->driver_name,
                'dispatched_by' => $userId,
                'dispatched_at' => now(),
                'dispatched_ip' => request()->ip()
            ]);

            // Auto-create Gate Pass (Delivery Order) for this transfer
            \App\Models\DeliveryOrder::create([
                'company_id' => $transfer->company_id,
                'source_type' => 'transfer',
                'transfer_id' => $transfer->id,
                'issue_date' => now()->toDateString(),
                'dispatcher_id' => $userId,
                'status' => 'dispatched',
                'vehicle_number' => $transfer->vehicle_number,
                'driver_name' => $transfer->driver_name,
            ]);

            return true;
        });
    }
}
