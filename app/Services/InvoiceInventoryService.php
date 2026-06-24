<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItems;
use App\Models\Inventory;
use App\Models\StockMovement;
use App\Models\Product;
use App\Models\ProductSerial;
use Illuminate\Support\Facades\DB;

class InvoiceInventoryService
{
    /**
     * Sync inventory levels and serial statuses for an invoice.
     *
     * @param Invoice $invoice
     * @param array $itemsData List of ['product_id' => int, 'quantity' => float, 'serials' => array]
     * @throws \Exception
     */
    public function syncInvoiceInventory(Invoice $invoice, array $itemsData = []): void
    {
        DB::transaction(function () use ($invoice, $itemsData) {
            // 1. Revert previous stock deductions and serial sales for this invoice
            $this->revertInvoiceInventory($invoice);

            // If the invoice status is draft or canceled, we stop here (revert is sufficient)
            if (in_array($invoice->status, ['draft', 'canceled'])) {
                return;
            }

            // 2. Apply new stock deductions and mark serials as sold
            $warehouseId = $invoice->warehouse_id;
            if (!$warehouseId) {
                // If there are inventory products but no warehouse is selected, throw an exception
                $hasInventoryProducts = false;
                foreach ($itemsData as $item) {
                    if (!empty($item['product_id'])) {
                        $hasInventoryProducts = true;
                        break;
                    }
                }
                if ($hasInventoryProducts) {
                    throw new \Exception("A warehouse must be selected for inventory invoices.");
                }
                return;
            }

            $companyId = $invoice->company_id;

            foreach ($itemsData as $itemData) {
                $productId = $itemData['product_id'] ?? null;
                $quantity = (float)($itemData['quantity'] ?? 0);
                $serials = $itemData['serials'] ?? [];

                if (!$productId || $quantity <= 0) {
                    continue; // Skip custom items or invalid quantities
                }

                $product = Product::findOrFail($productId);

                // Deduct stock from inventories table
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

                $newQty = (float)$inventory->quantity - $quantity;
                if ($newQty < 0) {
                    throw new \Exception("Insufficient stock in the selected warehouse for product '{$product->name}'. Available: {$inventory->quantity}, Requested: {$quantity}");
                }

                $inventory->quantity = $newQty;
                $inventory->save();

                // If serialized, mark serial numbers as sold and set warranty
                if ($product->is_serialized && !empty($serials)) {
                    $snCount = count($serials);
                    if ($snCount !== (int)$quantity) {
                        throw new \Exception("The number of scanned serial numbers ({$snCount}) does not match the quantity (" . (int)$quantity . ") for product '{$product->name}'.");
                    }

                    // Update serials status to sold
                    ProductSerial::whereIn('serial_number', $serials)
                        ->where('product_id', $productId)
                        ->update([
                            'status' => ProductSerial::STATUS_SOLD,
                            'invoice_id' => $invoice->id,
                            'warranty_expires_at' => now()->addYear(),
                        ]);
                }

                // Record stock movement (out)
                $movement = new StockMovement();
                $movement->company_id = $companyId;
                $movement->product_id = $productId;
                $movement->warehouse_id = $warehouseId;
                $movement->quantity = $quantity;
                $movement->balance_after = $newQty;
                $movement->type = 'out';
                $movement->stock_category = 'available';
                $movement->reference_type = 'invoice';
                $movement->reference_id = $invoice->id;
                $movement->remarks = "Deducted via Invoice #{$invoice->invoice_number}";
                $movement->save();
            }
        });
    }

    /**
     * Revert all stock deductions and serial sales for an invoice (e.g. on delete, cancel, or edit).
     */
    public function revertInvoiceInventory(Invoice $invoice): void
    {
        DB::transaction(function () use ($invoice) {
            // Find all stock movements for this invoice
            $movements = StockMovement::where('reference_type', 'invoice')
                ->where('reference_id', $invoice->id)
                ->lockForUpdate()
                ->get();

            foreach ($movements as $movement) {
                // Find inventory
                $inventory = Inventory::where('product_id', $movement->product_id)
                    ->where('warehouse_id', $movement->warehouse_id)
                    ->lockForUpdate()
                    ->first();

                if ($inventory) {
                    // Revert stock back (add it back since movement type was 'out')
                    if ($movement->type === 'out') {
                        $inventory->quantity = (float)$inventory->quantity + (float)$movement->quantity;
                        $inventory->save();
                    }
                }
            }

            // Reset serial statuses back to available (keep their current warehouse_id)
            ProductSerial::where('invoice_id', $invoice->id)
                ->update([
                    'status' => ProductSerial::STATUS_AVAILABLE,
                    'invoice_id' => null,
                    'warranty_expires_at' => null,
                ]);

            // Delete the stock movements
            StockMovement::where('reference_type', 'invoice')
                ->where('reference_id', $invoice->id)
                ->delete();
        });
    }
}
