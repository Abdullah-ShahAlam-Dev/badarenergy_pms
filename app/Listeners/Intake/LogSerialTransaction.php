<?php

namespace App\Listeners\Intake;

use App\Events\StockIntakeApproved;
use App\Models\ProductSerial;
use App\Models\SerialTransaction;
use App\Enums\SerialStatus;

class LogSerialTransaction
{
    /**
     * Handle the event.
     */
    public function handle(StockIntakeApproved $event): void
    {
        $voucher = $event->voucher;
        $warehouseId = $voucher->warehouse_id;

        // Fetch serial numbers created for this voucher
        $serials = ProductSerial::where('intake_voucher_id', $voucher->id)->get();

        foreach ($serials as $serial) {
            SerialTransaction::create([
                'product_serial_id' => $serial->id,
                'serial_number' => $serial->serial_number,
                'event_type' => 'stock_intake',
                'source_document_type' => 'stock_intake_voucher',
                'source_document_id' => $voucher->id,
                'warehouse_id' => $warehouseId,
                'user_id' => auth()->id(),
                'previous_status' => null,
                'new_status' => SerialStatus::AVAILABLE->value,
                'remarks' => "Generated via Stock Intake Voucher #{$voucher->voucher_number}",
            ]);
        }
    }
}
