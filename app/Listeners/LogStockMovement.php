<?php

namespace App\Listeners;

use App\Events\DeliveryOrderDispatched;
use App\Events\DeliveryOrderCancelled;
use App\Models\StockMovement;
use App\Models\Inventory;
use App\Enums\MovementType;

class LogStockMovement
{
    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        if ($event instanceof DeliveryOrderDispatched) {
            $this->handleDispatch($event);
        } elseif ($event instanceof DeliveryOrderCancelled) {
            $this->handleCancel($event);
        }
    }

    /**
     * Create StockMovement 'out' record on dispatch.
     */
    protected function handleDispatch(DeliveryOrderDispatched $event): void
    {
        $do = $event->deliveryOrder;
        $warehouseId = $do->warehouse_id ?? $this->resolveWarehouseId($do);
        if (!$warehouseId) {
            return;
        }

        $do->load(['lines']);

        foreach ($do->lines as $line) {
            $inventory = Inventory::where('product_id', $line->product_id)
                ->where('warehouse_id', $warehouseId)
                ->first();

            $balanceAfter = $inventory ? (float) $inventory->quantity : 0.00;

            StockMovement::create([
                'company_id' => $do->company_id,
                'product_id' => $line->product_id,
                'warehouse_id' => $warehouseId,
                'quantity' => (float) $line->quantity_requested,
                'balance_after' => $balanceAfter,
                'type' => 'out',
                'movement_type' => MovementType::ISSUE->value,
                'stock_category' => 'available',
                'reference_type' => 'delivery_order',
                'reference_id' => $do->id,
                'remarks' => "Dispatched via DO #{$do->id}" . ($do->invoice_id ? " linked to Invoice #{$do->invoice_id}" : ""),
            ]);
        }
    }

    /**
     * Create contra StockMovement 'in' record on cancellation.
     */
    protected function handleCancel(DeliveryOrderCancelled $event): void
    {
        $do = $event->deliveryOrder;
        $warehouseId = $do->warehouse_id ?? $this->resolveWarehouseId($do);
        if (!$warehouseId) {
            return;
        }

        $do->load(['lines']);

        foreach ($do->lines as $line) {
            $inventory = Inventory::where('product_id', $line->product_id)
                ->where('warehouse_id', $warehouseId)
                ->first();

            $balanceAfter = $inventory ? (float) $inventory->quantity : 0.00;

            // Log contra return movement to maintain complete immutable audit trail
            StockMovement::create([
                'company_id' => $do->company_id,
                'product_id' => $line->product_id,
                'warehouse_id' => $warehouseId,
                'quantity' => (float) $line->quantity_dispatched,
                'balance_after' => $balanceAfter,
                'type' => 'in',
                'movement_type' => MovementType::RETURN->value,
                'stock_category' => 'available',
                'reference_type' => 'delivery_order',
                'reference_id' => $do->id,
                'remarks' => "Reverted dispatch of DO #{$do->id} due to cancellation",
            ]);
        }
    }

    /**
     * Resolve warehouse associated with this delivery order.
     */
    protected function resolveWarehouseId($do): ?int
    {
        if (isset($do->warehouse_id) && $do->warehouse_id) {
            return $do->warehouse_id;
        }

        if ($do->invoice_id && $do->invoice) {
            return $do->invoice->warehouse_id;
        }

        if ($do->transfer_id && $do->stockTransfer) {
            return $do->stockTransfer->from_warehouse_id;
        }

        // Fallback to first picked serial warehouse ID
        $firstLine = $do->lines()->first();
        if ($firstLine) {
            $firstSerialLink = $firstLine->lineSerials()->first();
            if ($firstSerialLink && $firstSerialLink->serial) {
                return $firstSerialLink->serial->warehouse_id;
            }
        }

        return null;
    }
}
