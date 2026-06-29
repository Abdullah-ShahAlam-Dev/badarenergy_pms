<?php

namespace App\Listeners;

use App\Events\DeliveryOrderDispatched;
use App\Events\DeliveryOrderCancelled;
use App\Models\ProductSerial;
use App\Enums\SerialStatus;

class UpdateSerialStatus
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
     * Update serials status to DISPATCHED or SOLD on dispatch.
     */
    protected function handleDispatch(DeliveryOrderDispatched $event): void
    {
        $do = $event->deliveryOrder;
        $do->load(['lines.lineSerials.serial']);

        $targetStatus = $do->invoice_id ? SerialStatus::SOLD->value : SerialStatus::DISPATCHED->value;

        foreach ($do->lines as $line) {
            foreach ($line->lineSerials as $lineSerial) {
                $serial = $lineSerial->serial;
                if ($serial) {
                    $serial->lockForUpdate();
                    $serial->update([
                        'status' => $targetStatus,
                        'invoice_id' => $do->invoice_id,
                        'warranty_expires_at' => $do->invoice_id ? now()->addYear() : null,
                    ]);
                    $lineSerial->update(['status' => $targetStatus]);
                }
            }
        }
    }

    /**
     * Return serials status to AVAILABLE or RESERVED on cancellation.
     */
    protected function handleCancel(DeliveryOrderCancelled $event): void
    {
        $do = $event->deliveryOrder;
        $do->load(['lines.lineSerials.serial']);

        foreach ($do->lines as $line) {
            foreach ($line->lineSerials as $lineSerial) {
                $serial = $lineSerial->serial;
                if ($serial) {
                    $serial->lockForUpdate();
                    $serial->update([
                        'status' => SerialStatus::AVAILABLE->value,
                        'invoice_id' => null,
                        'warranty_expires_at' => null,
                    ]);
                    $lineSerial->update(['status' => SerialStatus::AVAILABLE->value]);
                }
            }
        }
    }
}
