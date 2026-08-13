<?php

namespace App\Services;

use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderLine;
use App\Models\DeliveryOrderLineSerial;
use App\Models\ProductSerial;
use App\Models\SerialTransaction;
use App\Events\DeliveryOrderDispatched;
use App\Events\DeliveryOrderCancelled;
use App\Enums\SerialStatus;
use App\Enums\DeliveryOrderStatus;
use App\Facades\WorkflowConfig;
use Illuminate\Support\Facades\DB;
use Exception;

class DeliveryOrderService
{
    /**
     * Reserve serial numbers for a Delivery Order line.
     *
     * @param DeliveryOrder $do
     * @param array $lineSerialsMapping Array of [line_id => [serial_id_1, serial_id_2, ...]]
     * @throws Exception
     */
    public function reserveSerials(DeliveryOrder $do, array $lineSerialsMapping): void
    {
        DB::transaction(function () use ($do, $lineSerialsMapping) {
            foreach ($lineSerialsMapping as $lineId => $serialIds) {
                $line = DeliveryOrderLine::where('delivery_order_id', $do->id)
                    ->where('id', $lineId)
                    ->firstOrFail();

                foreach ($serialIds as $serialId) {
                    $serial = ProductSerial::lockForUpdate()->findOrFail($serialId);

                    if ($serial->status !== SerialStatus::AVAILABLE->value) {
                        throw new Exception("Serial [{$serial->serial_number}] is not available. Current status: {$serial->status}");
                    }

                    // Transition to reserved
                    $previousStatus = $serial->status;
                    $serial->update([
                        'status' => SerialStatus::RESERVED->value,
                    ]);

                    DeliveryOrderLineSerial::updateOrCreate([
                        'delivery_order_line_id' => $line->id,
                        'product_serial_id' => $serial->id,
                    ], [
                        'status' => SerialStatus::RESERVED->value,
                    ]);

                    SerialTransaction::create([
                        'product_serial_id' => $serial->id,
                        'serial_number' => $serial->serial_number,
                        'event_type' => 'reserve',
                        'source_document_type' => 'delivery_order',
                        'source_document_id' => $do->id,
                        'warehouse_id' => $serial->warehouse_id,
                        'user_id' => auth()->id(),
                        'previous_status' => $previousStatus,
                        'new_status' => SerialStatus::RESERVED->value,
                        'remarks' => "Reserved for DO #{$do->id}",
                    ]);
                }
            }
        });
    }

    /**
     * Release all reserved serials back to available.
     *
     * @param DeliveryOrder $do
     */
    public function releaseReservations(DeliveryOrder $do): void
    {
        DB::transaction(function () use ($do) {
            $do->load(['lines.lineSerials.serial']);

            foreach ($do->lines as $line) {
                foreach ($line->lineSerials as $lineSerial) {
                    $serial = $lineSerial->serial;
                    if ($serial && $serial->status === SerialStatus::RESERVED->value) {
                        $previousStatus = $serial->status;
                        $serial->update([
                            'status' => SerialStatus::AVAILABLE->value,
                        ]);

                        SerialTransaction::create([
                            'product_serial_id' => $serial->id,
                            'serial_number' => $serial->serial_number,
                            'event_type' => 'release_reserve',
                            'source_document_type' => 'delivery_order',
                            'source_document_id' => $do->id,
                            'warehouse_id' => $serial->warehouse_id,
                            'user_id' => auth()->id(),
                            'previous_status' => $previousStatus,
                            'new_status' => SerialStatus::AVAILABLE->value,
                            'remarks' => "Reservation released back to available",
                        ]);
                    }
                    $lineSerial->delete();
                }
            }
        });
    }

    /**
     * Dispatch the Delivery Order.
     *
     * @param DeliveryOrder $do
     * @param int|null $userId
     * @throws Exception
     */
    public function dispatch(DeliveryOrder $do, ?int $userId = null): void
    {
        DB::transaction(function () use ($do, $userId) {
            $do->lockForUpdate();

            if ($do->status === DeliveryOrderStatus::DISPATCHED->value) {
                return; // Already dispatched, idempotent check
            }

            // Check if approval is enabled and enforce status check
            $approvalRequired = WorkflowConfig::get('approvals', 'delivery_order', false, $do->company_id);
            if ($approvalRequired && $do->status !== DeliveryOrderStatus::APPROVED->value) {
                throw new Exception("Delivery Order #{$do->id} must be approved by supervisor before dispatch.");
            }

            // Update DO status
            $do->update([
                'status' => DeliveryOrderStatus::DISPATCHED->value,
                'issue_date' => now(),
                'dispatcher_id' => $userId ?? (auth()->user() ? auth()->user()->id : null),
            ]);

            // Sync linked Order status to completed
            $order = null;
            if ($do->source_type === 'order' && $do->source_id) {
                $order = \App\Models\Order::find($do->source_id);
            } elseif ($do->invoice && $do->invoice->order_id) {
                $order = \App\Models\Order::find($do->invoice->order_id);
            }

            if ($order) {
                // Generate Invoice automatically if it doesn't exist
                if (!$order->invoice) {
                    $invoice = new \App\Models\Invoice();
                    $invoice->order_id = $order->id;
                    $invoice->company_id = $order->company_id;
                    $invoice->client_id = $order->client_id;
                    $invoice->warehouse_id = $order->warehouse_id;
                    $invoice->sub_total = $order->sub_total;
                    $invoice->discount = $order->discount;
                    $invoice->discount_type = $order->discount_type;
                    $invoice->total = $order->total;
                    $invoice->currency_id = $order->currency_id;

                    // Credit Sale is 0, Cash Sale is 1
                    if ($order->sale_type == 0 || $order->sale_type === '0') {
                        $invoice->status = 'unpaid';
                        $invoice->due_amount = $order->total;
                    } else {
                        $invoice->status = 'paid';
                        $invoice->due_amount = 0;
                    }

                    $invoice->note = trim_editor($order->note);
                    $invoice->issue_date = now();
                    $invoice->send_status = 1;
                    $invoice->invoice_number = \App\Models\Invoice::lastInvoiceNumber() + 1;
                    $invoice->hash = md5(microtime());
                    $invoice->added_by = $order->added_by;
                    $invoice->save();

                    /* Make invoice items */
                    $orderItems = \App\Models\OrderItems::where('order_id', $order->id)->get();

                    foreach ($orderItems as $item) {
                        $invoiceItem = new \App\Models\InvoiceItems();
                        $invoiceItem->invoice_id = $invoice->id;
                        $invoiceItem->item_name = $item->item_name;
                        $invoiceItem->item_summary = $item->item_summary;
                        $invoiceItem->type = 'item';
                        $invoiceItem->quantity = $item->quantity;
                        $invoiceItem->unit_price = $item->unit_price;
                        $invoiceItem->amount = $item->amount;
                        $invoiceItem->taxes = $item->taxes;
                        $invoiceItem->product_id = $item->product_id;
                        $invoiceItem->unit_id = $item->unit_id;
                        $invoiceItem->saveQuietly();

                        // Save invoice item image
                        if ($item->orderItemImage) {
                            $invoiceItemImage = new \App\Models\InvoiceItemImage();
                            $invoiceItemImage->invoice_item_id = $invoiceItem->id;
                            $invoiceItemImage->external_link = $item->orderItemImage->external_link;
                            $invoiceItemImage->save();
                        }
                    }

                    // Link DO to the newly created invoice
                    $do->invoice_id = $invoice->id;
                    $do->save();
                }

                $order->status = 'completed';
                $order->save();
            }

            // Fire event - Listeners will execute inventory increments and log audit history inside the transaction
            event(new DeliveryOrderDispatched($do, $userId));
        });
    }

    /**
     * Cancel the Delivery Order dispatch (Rollback).
     *
     * @param DeliveryOrder $do
     * @param int|null $userId
     * @throws Exception
     */
    public function cancelDispatch(DeliveryOrder $do, ?int $userId = null): void
    {
        DB::transaction(function () use ($do, $userId) {
            $do->lockForUpdate();

            if ($do->status !== DeliveryOrderStatus::DISPATCHED->value) {
                throw new Exception("Only dispatched Delivery Orders can be cancelled.");
            }

            // Update status back to pending (or draft depending on trigger settings)
            $do->update([
                'status' => DeliveryOrderStatus::CANCELLED->value,
            ]);

            // Sync linked Order status back to processing
            $order = null;
            if ($do->source_type === 'order' && $do->source_id) {
                $order = \App\Models\Order::find($do->source_id);
            } elseif ($do->invoice && $do->invoice->order_id) {
                $order = \App\Models\Order::find($do->invoice->order_id);
            }

            if ($order) {
                $order->status = 'processing';
                $order->save();
            }

            if ($do->invoice) {
                $do->invoice->delete();
            }

            // Fire cancellation event to reverse inventory and serial status
            event(new DeliveryOrderCancelled($do, $userId));
        });
    }
}
