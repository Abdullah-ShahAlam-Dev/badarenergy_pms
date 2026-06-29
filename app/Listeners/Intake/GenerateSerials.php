<?php

namespace App\Listeners\Intake;

use App\Events\StockIntakeApproved;
use App\Services\SerialGeneratorService;

class GenerateSerials
{
    protected SerialGeneratorService $serialGenerator;

    public function __construct(SerialGeneratorService $serialGenerator)
    {
        $this->serialGenerator = $serialGenerator;
    }

    /**
     * Handle the event.
     */
    public function handle(StockIntakeApproved $event): void
    {
        $voucher = $event->voucher;
        $warehouseId = $voucher->warehouse_id;
        $companyId = $voucher->company_id;

        $voucher->load(['items.product']);

        foreach ($voucher->items as $item) {
            $product = $item->product;
            if ($product && $product->is_serialized) {
                $qty = (int) $item->quantity_received;
                if ($qty <= 0) {
                    continue;
                }

                $this->serialGenerator->generate(
                    $item->product_id,
                    $warehouseId,
                    $companyId,
                    $qty,
                    $voucher->id,
                    $item->batch_id
                );
            }
        }
    }
}
