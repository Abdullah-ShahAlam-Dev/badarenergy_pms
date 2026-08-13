<?php

namespace App\Services;

use App\Models\AssemblyOrder;
use App\Models\AssemblyOrderItem;
use App\Models\AssemblyFaultLog;
use App\Models\Inventory;
use App\Models\StockMovement;
use App\Models\StockIntakeVoucher;
use App\Models\StockIntakeItem;
use Illuminate\Support\Facades\DB;
use Exception;

class AssemblyLineService
{
    /**
     * Process direct Assembly Stock Withdrawal:
     * Immediately deduct raw parts inventory, record user audit & stock movements, and complete job.
     */
    public function processDirectWithdrawal(array $data, array $items): AssemblyOrder
    {
        return DB::transaction(function () use ($data, $items) {
            $companyId = company() ? company()->id : 1;
            $warehouseId = $data['warehouse_id'];

            // 1. Create Assembly Record
            $assembly = AssemblyOrder::create([
                'company_id' => $companyId,
                'assembly_number' => AssemblyOrder::nextAssemblyNumber(),
                'target_product_id' => $data['target_product_id'] ?? null,
                'warehouse_id' => $warehouseId,
                'quantity_to_assemble' => $data['quantity_to_assemble'] ?? 0.00,
                'status' => 'completed',
                'notes' => $data['notes'] ?? 'Assembly Stock Withdrawal',
                'created_by' => auth()->id(),
                'completed_at' => now(),
            ]);

            $userName = auth()->user() ? auth()->user()->name : 'User';

            // 2. Insert items & deduct inventory immediately
            foreach ($items as $itemData) {
                $rawId = (int) $itemData['raw_product_id'];
                $qty = (float) $itemData['quantity'];

                AssemblyOrderItem::create([
                    'assembly_order_id' => $assembly->id,
                    'raw_product_id' => $rawId,
                    'quantity_required' => $qty,
                    'quantity_used' => $qty,
                    'quantity_faulty' => 0,
                ]);

                if ($qty > 0) {
                    $inventory = Inventory::where('company_id', $companyId)
                        ->where('warehouse_id', $warehouseId)
                        ->where('product_id', $rawId)
                        ->lockForUpdate()
                        ->first();

                    if (!$inventory || $inventory->quantity < $qty) {
                        $prod = \App\Models\Product::find($rawId);
                        $prodName = $prod ? $prod->name : "Product #{$rawId}";
                        throw new Exception("Insufficient stock for assembly part '{$prodName}'. Requested: {$qty}, Available: " . ($inventory ? $inventory->quantity : 0));
                    }

                    $inventory->quantity -= $qty;
                    $inventory->save();

                    StockMovement::create([
                        'company_id' => $companyId,
                        'product_id' => $rawId,
                        'warehouse_id' => $warehouseId,
                        'quantity' => -$qty,
                        'balance_after' => $inventory->quantity,
                        'type' => 'out',
                        'stock_category' => 'raw_part_assembly',
                        'reference_type' => AssemblyOrder::class,
                        'reference_id' => $assembly->id,
                        'remarks' => "Assembly Stock Withdrawn ({$qty} pcs) by {$userName} for Job #{$assembly->assembly_number}",
                    ]);
                }
            }

            return $assembly;
        });
    }
    /**
     * Complete an assembly job: deduct raw parts from inventory, create StockMovement logs,
     * and trigger a pending StockIntakeVoucher for finished product warehouse intake.
     *
     * @param AssemblyOrder $assembly
     * @param array $actualUsage Array of [item_id => quantity_used]
     * @throws Exception
     */
    public function completeAssembly(AssemblyOrder $assembly, array $actualUsage = []): AssemblyOrder
    {
        return DB::transaction(function () use ($assembly, $actualUsage) {
            $assembly->lockForUpdate();

            if ($assembly->status === 'completed') {
                return $assembly; // Idempotent check
            }

            $companyId = $assembly->company_id ?: (company() ? company()->id : 1);

            // 1. Process Raw Parts Usage & Manual Stock Deduction
            foreach ($assembly->items as $item) {
                $usedQty = isset($actualUsage[$item->id]) ? (float) $actualUsage[$item->id] : (float) $item->quantity_required;
                $item->quantity_used = $usedQty;
                $item->save();

                if ($usedQty > 0) {
                    $inventory = Inventory::where('company_id', $companyId)
                        ->where('warehouse_id', $assembly->warehouse_id)
                        ->where('product_id', $item->raw_product_id)
                        ->lockForUpdate()
                        ->first();

                    if (!$inventory || $inventory->quantity < $usedQty) {
                        $rawProdName = $item->rawProduct ? $item->rawProduct->name : "Product #{$item->raw_product_id}";
                        throw new Exception("Insufficient stock for raw part '{$rawProdName}'. Requested {$usedQty}, available: " . ($inventory ? $inventory->quantity : 0));
                    }

                    $inventory->quantity -= $usedQty;
                    $inventory->save();

                    // Log StockMovement
                    StockMovement::create([
                        'company_id' => $companyId,
                        'product_id' => $item->raw_product_id,
                        'warehouse_id' => $assembly->warehouse_id,
                        'quantity' => -$usedQty,
                        'balance_after' => $inventory->quantity,
                        'type' => 'out',
                        'stock_category' => 'raw_part_assembly',
                        'reference_type' => AssemblyOrder::class,
                        'reference_id' => $assembly->id,
                        'remarks' => "Raw part deduction for Assembly Job #{$assembly->assembly_number}",
                    ]);
                }
            }

            // 2. Mark Assembly as Completed
            $assembly->status = 'completed';
            $assembly->completed_at = now();
            $assembly->save();

            // 3. Trigger Finished Product Stock Intake Voucher (Draft/Pending for Warehouse Intake Barcode Generation)
            $generator = app(IntakeNumberGeneratorService::class);
            $voucherNumber = $generator->generate($companyId);

            $creatorName = $assembly->creator ? $assembly->creator->name : 'User';
            $intakeVoucher = new StockIntakeVoucher();
            $intakeVoucher->company_id = $companyId;
            $intakeVoucher->voucher_number = $voucherNumber;
            $intakeVoucher->warehouse_id = $assembly->warehouse_id;
            $intakeVoucher->intake_date = now()->format('Y-m-d');
            $intakeVoucher->intake_type = 'assembly';
            $intakeVoucher->remarks = "Finished Product Intake from Assembly Job #{$assembly->assembly_number} (Assembled by {$creatorName})";
            $intakeVoucher->created_by = auth()->id() ?: $assembly->created_by;
            $intakeVoucher->status = 'pending'; // Requires warehouse intake approval to generate barcodes
            $intakeVoucher->save();

            StockIntakeItem::create([
                'intake_voucher_id' => $intakeVoucher->id,
                'product_id' => $assembly->target_product_id,
                'quantity_declared' => $assembly->quantity_to_assemble,
                'quantity_received' => $assembly->quantity_to_assemble,
                'unit_cost' => 0.00,
            ]);

            return $assembly;
        });
    }

    /**
     * Log a defective/damaged part during assembly: Move stock from Available Stock to Fault Stock.
     *
     * @param AssemblyOrder $assembly
     * @param int $productId
     * @param float $faultQty
     * @param string|null $reason
     * @throws Exception
     */
    public function logFaultyPart(AssemblyOrder $assembly, int $productId, float $faultQty, ?string $reason = null): AssemblyFaultLog
    {
        return DB::transaction(function () use ($assembly, $productId, $faultQty, $reason) {
            $companyId = $assembly->company_id ?: (company() ? company()->id : 1);

            $inventory = Inventory::where('company_id', $companyId)
                ->where('warehouse_id', $assembly->warehouse_id)
                ->where('product_id', $productId)
                ->lockForUpdate()
                ->first();

            if (!$inventory || $inventory->quantity < $faultQty) {
                $prod = \App\Models\Product::find($productId);
                $prodName = $prod ? $prod->name : "Product #{$productId}";
                throw new Exception("Insufficient stock to log fault for '{$prodName}'. Required: {$faultQty}, Available: " . ($inventory ? $inventory->quantity : 0));
            }

            // Move stock: Available -> Faulty
            $inventory->quantity -= $faultQty;
            $inventory->quantity_faulty += $faultQty;
            $inventory->save();

            // Create Fault Log Record
            $faultLog = AssemblyFaultLog::create([
                'company_id' => $companyId,
                'assembly_order_id' => $assembly->id,
                'product_id' => $productId,
                'warehouse_id' => $assembly->warehouse_id,
                'fault_quantity' => $faultQty,
                'reason' => $reason ?: 'Defective part in assembly line',
                'user_id' => auth()->id(),
            ]);

            // Update AssemblyOrderItem faulty qty if applicable
            $item = AssemblyOrderItem::where('assembly_order_id', $assembly->id)
                ->where('raw_product_id', $productId)
                ->first();
            if ($item) {
                $item->quantity_faulty += $faultQty;
                $item->save();
            }

            // Log StockMovement audit
            StockMovement::create([
                'company_id' => $companyId,
                'product_id' => $productId,
                'warehouse_id' => $assembly->warehouse_id,
                'quantity' => -$faultQty,
                'balance_after' => $inventory->quantity,
                'type' => 'fault_transfer',
                'stock_category' => 'fault',
                'reference_type' => AssemblyFaultLog::class,
                'reference_id' => $faultLog->id,
                'remarks' => "Moved {$faultQty} to Fault Stock for Assembly #{$assembly->assembly_number}. Reason: {$reason}",
            ]);

            return $faultLog;
        });
    }
}
