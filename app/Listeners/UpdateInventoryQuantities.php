<?php

namespace App\Listeners;

use App\Events\DeliveryOrderDispatched;
use App\Events\DeliveryOrderCancelled;
use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class UpdateInventoryQuantities
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
     * Deduct inventory quantities on dispatch.
     */
    protected function handleDispatch(DeliveryOrderDispatched $event): void
    {
        $do = $event->deliveryOrder;
        $companyId = $do->company_id;
        
        // We look up the warehouse from the DO source document or warehouse link
        // Delivery orders have a warehouse/outlet context (linked to company settings)
        // Let's resolve the warehouse ID: it can be stored on the DO or derived from source
        $warehouseId = $do->warehouse_id ?? $this->resolveWarehouseId($do);
        if (!$warehouseId) {
            return;
        }

        $do->load(['lines']);

        foreach ($do->lines as $line) {
            $productId = $line->product_id;
            $qty = (float) $line->quantity_requested;

            if ($qty <= 0) {
                continue;
            }

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

            $inventory->quantity = (float) $inventory->quantity - $qty;
            $inventory->save();

            // Store updated balance on line for movement log references
            $line->update(['quantity_dispatched' => $qty]);
        }
    }

    /**
     * Return inventory quantities on cancellation.
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
            $productId = $line->product_id;
            $qty = (float) $line->quantity_dispatched;

            if ($qty <= 0) {
                continue;
            }

            $inventory = Inventory::where('product_id', $productId)
                ->where('warehouse_id', $warehouseId)
                ->lockForUpdate()
                ->first();

            if ($inventory) {
                $inventory->quantity = (float) $inventory->quantity + $qty;
                $inventory->save();
            }

            $line->update(['quantity_dispatched' => 0.00]);
        }
    }

    /**
     * Resolve warehouse associated with this delivery order.
     */
    protected function resolveWarehouseId($do): ?int
    {
        // Try to read from DO model first (if column was added)
        if (isset($do->warehouse_id) && $do->warehouse_id) {
            return $do->warehouse_id;
        }

        // Derives warehouse from the source document (Invoice or Stock Transfer)
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
