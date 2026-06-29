<?php

namespace App\Listeners;

use App\Events\DeliveryOrderDispatched;
use App\Events\DeliveryOrderCancelled;
use App\Models\SerialTransaction;
use App\Enums\SerialStatus;

class LogSerialTransaction
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
     * Create SerialTransaction rows on dispatch.
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
                    SerialTransaction::create([
                        'product_serial_id' => $serial->id,
                        'serial_number' => $serial->serial_number,
                        'event_type' => $do->invoice_id ? 'sale' : 'dispatch',
                        'source_document_type' => 'delivery_order',
                        'source_document_id' => $do->id,
                        'warehouse_id' => $serial->warehouse_id,
                        'user_id' => $event->userId,
                        'previous_status' => SerialStatus::RESERVED->value,
                        'new_status' => $targetStatus,
                        'remarks' => "Dispatched via DO #{$do->id}",
                    ]);
                }
            }
        }
    }

    /**
     * Create SerialTransaction rows on cancellation.
     */
    protected function handleCancel(DeliveryOrderCancelled $event): void
    {
        $do = $event->deliveryOrder;
        $do->load(['lines.lineSerials.serial']);

        foreach ($do->lines as $line) {
            foreach ($line->lineSerials as $lineSerial) {
                $serial = $lineSerial->serial;
                if ($serial) {
                    SerialTransaction::create([
                        'product_serial_id' => $serial->id,
                        'serial_number' => $serial->serial_number,
                        'event_type' => 'dispatch_cancel',
                        'source_document_type' => 'delivery_order',
                        'source_document_id' => $do->id,
                        'warehouse_id' => $serial->warehouse_id,
                        'user_id' => $event->userId,
                        'previous_status' => $serial->status,
                        'new_status' => SerialStatus::AVAILABLE->value,
                        'remarks' => "Cancelled dispatch of DO #{$do->id}, serial set to available",
                    ]);
                }
            }
        }
    }
}
