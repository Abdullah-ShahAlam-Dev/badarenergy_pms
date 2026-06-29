<?php

namespace App\Services;

use App\Models\StockTransfer;
use App\Models\TransferItem;
use App\Models\TransferSerial;
use App\Models\ProductSerial;
use App\Models\Inventory;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class TransferReceiptService
{
    /**
     * Confirm receipt of products at destination warehouse.
     */
    public function receive(int $id, int $userId, array $receiptData): bool
    {
        return DB::transaction(function () use ($id, $userId, $receiptData) {
            $transfer = StockTransfer::findOrFail($id);

            if (!in_array($transfer->status, [StockTransfer::STATUS_DISPATCHED, StockTransfer::STATUS_IN_TRANSIT, StockTransfer::STATUS_PARTIALLY_RECEIVED])) {
                throw new \Exception("Stock transfer is not in a receivable state.");
            }

            $totalDispatched = 0.00;
            $totalReceivedNew = 0.00;

            foreach ($transfer->items as $item) {
                $productId = $item->product_id;
                $totalDispatched += (float)$item->quantity;

                // Find received serials for this product inside post data
                $itemData = collect($receiptData['items'] ?? [])->firstWhere('product_id', $productId);
                $receivedSerials = $itemData['serials'] ?? [];
                $qtyReceivedThisTime = (float)count($receivedSerials);

                if ($qtyReceivedThisTime <= 0) {
                    // Update running sum with existing received items
                    $totalReceivedNew += (float)$item->quantity_received;
                    continue;
                }

                // Lock inventories
                $sourceInventory = Inventory::where('product_id', $productId)
                    ->where('warehouse_id', $transfer->source_warehouse_id)
                    ->lockForUpdate()
                    ->first();

                $destInventory = Inventory::where('product_id', $productId)
                    ->where('warehouse_id', $transfer->destination_warehouse_id)
                    ->lockForUpdate()
                    ->first();

                if (!$destInventory) {
                    $destInventory = Inventory::create([
                        'company_id' => $transfer->company_id,
                        'product_id' => $productId,
                        'warehouse_id' => $transfer->destination_warehouse_id,
                        'quantity' => 0.00,
                        'quantity_faulty' => 0.00,
                        'quantity_in_transit' => 0.00
                    ]);
                }

                // 1. Deduct transit stock from source warehouse
                $newTransitQty = (float)($sourceInventory->quantity_in_transit ?? 0) - $qtyReceivedThisTime;
                if ($newTransitQty < 0) {
                    $newTransitQty = 0.00;
                }
                $sourceInventory->quantity_in_transit = $newTransitQty;
                $sourceInventory->save();

                // 2. Add available stock to destination warehouse
                $newDestQty = (float)$destInventory->quantity + $qtyReceivedThisTime;
                $destInventory->quantity = $newDestQty;
                $destInventory->save();

                // 3. Process each scanned serial
                foreach ($receivedSerials as $sn) {
                    $serialModel = ProductSerial::where('serial_number', trim($sn))
                        ->where('product_id', $productId)
                        ->first();

                    if (!$serialModel) {
                        throw new \Exception("Serial number '{$sn}' does not exist.");
                    }

                    // Move location to destination and status to available
                    $serialModel->update([
                        'warehouse_id' => $transfer->destination_warehouse_id,
                        'status' => ProductSerial::STATUS_AVAILABLE
                    ]);

                    // Update transfer serial record
                    $ts = TransferSerial::where('transfer_item_id', $item->id)
                        ->where('product_serial_id', $serialModel->id)
                        ->first();

                    if ($ts) {
                        $ts->update([
                            'status' => 'received',
                            'received_at' => now(),
                            'received_by' => $userId
                        ]);
                    }
                }

                // 4. Update the line item received count
                $item->quantity_received = (float)$item->quantity_received + $qtyReceivedThisTime;
                $item->save();

                $totalReceivedNew += (float)$item->quantity_received;

                // 5. Save Stock Movements
                // Source warehouse transit deduction (out)
                $movementTransitOut = new StockMovement();
                $movementTransitOut->company_id = $transfer->company_id;
                $movementTransitOut->product_id = $productId;
                $movementTransitOut->warehouse_id = $transfer->source_warehouse_id;
                $movementTransitOut->quantity = $qtyReceivedThisTime;
                $movementTransitOut->balance_after = $newTransitQty;
                $movementTransitOut->type = 'out';
                $movementTransitOut->stock_category = 'transit';
                $movementTransitOut->reference_type = 'stock_transfers';
                $movementTransitOut->reference_id = $transfer->id;
                $movementTransitOut->remarks = "Deducted in-transit stock on receipt";
                $movementTransitOut->save();

                // Destination warehouse available addition (in)
                $movementDestIn = new StockMovement();
                $movementDestIn->company_id = $transfer->company_id;
                $movementDestIn->product_id = $productId;
                $movementDestIn->warehouse_id = $transfer->destination_warehouse_id;
                $movementDestIn->quantity = $qtyReceivedThisTime;
                $movementDestIn->balance_after = $newDestQty;
                $movementDestIn->type = 'in';
                $movementDestIn->stock_category = 'available';
                $movementDestIn->reference_type = 'stock_transfers';
                $movementDestIn->reference_id = $transfer->id;
                $movementDestIn->remarks = "Received stock from transfer #{$transfer->transfer_number}";
                $movementDestIn->save();
            }

            // Decide status: received or partially received
            $finalStatus = ($totalReceivedNew >= $totalDispatched)
                ? StockTransfer::STATUS_RECEIVED
                : StockTransfer::STATUS_PARTIALLY_RECEIVED;

            $transfer->update([
                'status' => $finalStatus,
                'received_by' => $userId,
                'received_at' => now(),
                'received_ip' => request()->ip()
            ]);

            return true;
        });
    }
}
