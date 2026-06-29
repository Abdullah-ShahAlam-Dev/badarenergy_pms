<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Inventory;
use App\Models\StockMovement;
use App\Models\Product;
use App\Models\ProductSerial;
use App\Models\DeliveryOrder;
use App\Models\SerialTransaction;
use App\Facades\WorkflowConfig;
use App\Enums\SerialStatus;
use App\Enums\MovementType;
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
            $companyId = $invoice->company_id;
            $stockOutTrigger = WorkflowConfig::get('inventory', 'stock_out_trigger', 'invoice_approval', $companyId);

            // 1. Revert previous stock deductions and serial states for this invoice
            $this->revertInvoiceInventory($invoice);

            // If the invoice status is draft, canceled, or pending_approval, we stop here (revert is sufficient)
            if (in_array($invoice->status, ['draft', 'canceled', 'pending_approval'])) {
                return;
            }

            $warehouseId = $invoice->warehouse_id;

            if ($stockOutTrigger === 'invoice_approval') {
                // ==========================================
                // LEGACY INVOICE-DRIVEN STOCK DEDUCTION
                // ==========================================
                if (!$warehouseId) {
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

                foreach ($itemsData as $itemData) {
                    $productId = $itemData['product_id'] ?? null;
                    $quantity = (float)($itemData['quantity'] ?? 0);
                    $serials = $itemData['serials'] ?? [];

                    if (!$productId || $quantity <= 0) {
                        continue;
                    }

                    $product = Product::findOrFail($productId);

                    // Pessimistic lock on inventory to prevent concurrent deduction issues
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
                        $inventory->average_cost = 0.00;
                    }

                    $newQty = (float)$inventory->quantity - $quantity;
                    if ($newQty < 0) {
                        throw new \Exception("Insufficient stock in the selected warehouse for product '{$product->name}'. Available: {$inventory->quantity}, Requested: {$quantity}");
                    }

                    $inventory->quantity = $newQty;
                    $inventory->save();

                    // If serialized, validate serial numbers and mark them as sold
                    if ($product->is_serialized) {
                        if (empty($serials)) {
                            throw new \Exception("At least one serial number is required for the serialized product '{$product->name}'.");
                        }

                        $snCount = count($serials);
                        if ($snCount !== (int)$quantity) {
                            throw new \Exception("The number of scanned serial numbers ({$snCount}) does not match the quantity (" . (int)$quantity . ") for product '{$product->name}'.");
                        }

                        // Fetch and lock serials for update
                        $foundSerials = ProductSerial::whereIn('serial_number', $serials)
                            ->where('product_id', $productId)
                            ->lockForUpdate()
                            ->get();

                        // Check existence
                        $foundNumbers = $foundSerials->pluck('serial_number')->toArray();
                        $notFoundNumbers = array_diff($serials, $foundNumbers);
                        if (!empty($notFoundNumbers)) {
                            throw new \Exception("The following serial numbers do not exist in the system for product '{$product->name}': " . implode(', ', $notFoundNumbers));
                        }

                        // Check warehouse match
                        $wrongWarehouseSerials = $foundSerials->filter(function ($item) use ($warehouseId) {
                            return $item->warehouse_id != $warehouseId;
                        });
                        if ($wrongWarehouseSerials->isNotEmpty()) {
                            $wrongNumbers = $wrongWarehouseSerials->pluck('serial_number')->toArray();
                            throw new \Exception("The following serial numbers do not belong to the selected warehouse for product '{$product->name}': " . implode(', ', $wrongNumbers));
                        }

                        // Check status availability (prevent duplicate sale)
                        $unavailableSerials = $foundSerials->filter(function ($item) {
                            return $item->status !== SerialStatus::AVAILABLE->value;
                        });
                        if ($unavailableSerials->isNotEmpty()) {
                            $wrongNumbers = $unavailableSerials->pluck('serial_number')->toArray();
                            throw new \Exception("The following serial numbers are already sold or unavailable for product '{$product->name}': " . implode(', ', $wrongNumbers));
                        }

                        // Update serials status to sold and record serial transactions
                        foreach ($foundSerials as $serial) {
                            $previousStatus = $serial->status;
                            $serial->update([
                                'status' => SerialStatus::SOLD->value,
                                'invoice_id' => $invoice->id,
                                'warranty_expires_at' => now()->addYear(),
                            ]);

                            SerialTransaction::create([
                                'product_serial_id' => $serial->id,
                                'serial_number' => $serial->serial_number,
                                'event_type' => 'sale',
                                'source_document_type' => 'invoice',
                                'source_document_id' => $invoice->id,
                                'warehouse_id' => $warehouseId,
                                'user_id' => auth()->id(),
                                'previous_status' => $previousStatus,
                                'new_status' => SerialStatus::SOLD->value,
                                'remarks' => "Sold via Invoice #{$invoice->invoice_number}",
                            ]);
                        }
                    }

                    // Record stock movement (out)
                    $movement = new StockMovement();
                    $movement->company_id = $companyId;
                    $movement->product_id = $productId;
                    $movement->warehouse_id = $warehouseId;
                    $movement->quantity = $quantity;
                    $movement->balance_after = $newQty;
                    $movement->type = 'out';
                    $movement->movement_type = MovementType::LEGACY_DEDUCTION->value;
                    $movement->stock_category = 'available';
                    $movement->reference_type = 'invoice';
                    $movement->reference_id = $invoice->id;
                    $movement->remarks = "Deducted via Invoice #{$invoice->invoice_number}";
                    $movement->save();
                }
            } else {
                // ==========================================
                // DECOUPLED LOGISTICS-DRIVEN STOCK DEDUCTION
                // ==========================================
                // Under the decoupled setting, stock quantity deductions occurred at DO dispatch.
                // At invoice approval, we only transition matching serials to 'sold' status.
                $deliveryOrders = DeliveryOrder::where('invoice_id', $invoice->id)
                    ->lockForUpdate()
                    ->get();

                foreach ($deliveryOrders as $do) {
                    $do->load(['lines.lineSerials.serial']);
                    foreach ($do->lines as $line) {
                        foreach ($line->lineSerials as $lineSerial) {
                            $serial = $lineSerial->serial;
                            if ($serial && $serial->status !== SerialStatus::SOLD->value) {
                                $previousStatus = $serial->status;
                                $serial->update([
                                    'status' => SerialStatus::SOLD->value,
                                    'invoice_id' => $invoice->id,
                                    'warranty_expires_at' => now()->addYear(),
                                ]);

                                SerialTransaction::create([
                                    'product_serial_id' => $serial->id,
                                    'serial_number' => $serial->serial_number,
                                    'event_type' => 'sale',
                                    'source_document_type' => 'invoice',
                                    'source_document_id' => $invoice->id,
                                    'warehouse_id' => $serial->warehouse_id,
                                    'user_id' => auth()->id(),
                                    'previous_status' => $previousStatus,
                                    'new_status' => SerialStatus::SOLD->value,
                                    'remarks' => "Decoupled invoice matching DO #{$do->id} transition to sold",
                                ]);
                            }
                        }
                    }
                }
            }
        });
    }

    /**
     * Revert all stock deductions and serial sales for an invoice (e.g. on delete, cancel, or edit).
     */
    public function revertInvoiceInventory(Invoice $invoice): void
    {
        DB::transaction(function () use ($invoice) {
            $companyId = $invoice->company_id;
            $stockOutTrigger = WorkflowConfig::get('inventory', 'stock_out_trigger', 'invoice_approval', $companyId);

            if ($stockOutTrigger === 'invoice_approval') {
                // Find all stock movements for this invoice
                $movements = StockMovement::where('reference_type', 'invoice')
                    ->where('reference_id', $invoice->id)
                    ->lockForUpdate()
                    ->get();

                foreach ($movements as $movement) {
                    $inventory = Inventory::where('product_id', $movement->product_id)
                        ->where('warehouse_id', $movement->warehouse_id)
                        ->lockForUpdate()
                        ->first();

                    if ($inventory && $movement->type === 'out') {
                        $inventory->quantity = (float)$inventory->quantity + (float)$movement->quantity;
                        $inventory->save();
                    }
                }

                // Lock and revert serial statuses to available
                $soldSerials = ProductSerial::where('invoice_id', $invoice->id)
                    ->lockForUpdate()
                    ->get();

                foreach ($soldSerials as $serial) {
                    $previousStatus = $serial->status;
                    $serial->update([
                        'status' => SerialStatus::AVAILABLE->value,
                        'invoice_id' => null,
                        'warranty_expires_at' => null,
                    ]);

                    SerialTransaction::create([
                        'product_serial_id' => $serial->id,
                        'serial_number' => $serial->serial_number,
                        'event_type' => 'sale_revert',
                        'source_document_type' => 'invoice',
                        'source_document_id' => $invoice->id,
                        'warehouse_id' => $serial->warehouse_id,
                        'user_id' => auth()->id(),
                        'previous_status' => $previousStatus,
                        'new_status' => SerialStatus::AVAILABLE->value,
                        'remarks' => "Invoice sale reverted, returned to available",
                    ]);
                }

                // Delete the stock movements
                StockMovement::where('reference_type', 'invoice')
                    ->where('reference_id', $invoice->id)
                    ->delete();
            } else {
                // Revert serial status under decoupled logistics path.
                // Serials return to the 'dispatched' or 'delivered' state since the dispatch was already executed.
                $soldSerials = ProductSerial::where('invoice_id', $invoice->id)
                    ->lockForUpdate()
                    ->get();

                foreach ($soldSerials as $serial) {
                    $previousStatus = $serial->status;
                    
                    // Default to dispatched if reverted
                    $revertStatus = SerialStatus::DISPATCHED->value;

                    $serial->update([
                        'status' => $revertStatus,
                        'invoice_id' => null,
                        'warranty_expires_at' => null,
                    ]);

                    SerialTransaction::create([
                        'product_serial_id' => $serial->id,
                        'serial_number' => $serial->serial_number,
                        'event_type' => 'sale_revert',
                        'source_document_type' => 'invoice',
                        'source_document_id' => $invoice->id,
                        'warehouse_id' => $serial->warehouse_id,
                        'user_id' => auth()->id(),
                        'previous_status' => $previousStatus,
                        'new_status' => $revertStatus,
                        'remarks' => "Decoupled invoice reverted, serial status restored to dispatched",
                    ]);
                }
            }
        });
    }
}
