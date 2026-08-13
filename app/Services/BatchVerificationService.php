<?php

namespace App\Services;

use App\Models\ProductBatch;
use App\Models\DeliveryOrderLine;
use App\Models\OrderItems;
use App\Models\StockIntakeItem;

class BatchVerificationService
{
    /**
     * Verify batch history, manufacturing/expiry details, and sales/dispatch history by batch number or supplier batch.
     */
    public function verifyBatch(string $batchQuery, ?int $companyId = null): array
    {
        $companyId = $companyId ?: (company() ? company()->id : 1);

        $batches = ProductBatch::where('company_id', $companyId)
            ->where(function ($q) use ($batchQuery) {
                $q->where('batch_number', $batchQuery)
                  ->orWhere('supplier_batch', $batchQuery)
                  ->orWhere('production_lot', $batchQuery);
            })
            ->with(['product', 'serials'])
            ->get();

        if ($batches->isEmpty()) {
            return [
                'found' => false,
                'message' => 'No matching batch record found for query: ' . $batchQuery,
                'data' => [],
            ];
        }

        $results = [];

        foreach ($batches as $batch) {
            $intakes = StockIntakeItem::where('batch_id', $batch->id)
                ->with('voucher')
                ->get();

            $dispatches = DeliveryOrderLine::where('batch_id', $batch->id)
                ->with('deliveryOrder')
                ->get();

            $orderItems = OrderItems::where('batch_id', $batch->id)
                ->with('order')
                ->get();

            $isExpired = $batch->expiry_date && $batch->expiry_date->isPast();

            $results[] = [
                'batch_id' => $batch->id,
                'batch_number' => $batch->batch_number,
                'supplier_batch' => $batch->supplier_batch,
                'production_lot' => $batch->production_lot,
                'product_id' => $batch->product_id,
                'product_name' => $batch->product ? $batch->product->name : null,
                'manufacturing_date' => $batch->manufacturing_date ? $batch->manufacturing_date->format('Y-m-d') : null,
                'expiry_date' => $batch->expiry_date ? $batch->expiry_date->format('Y-m-d') : null,
                'is_expired' => $isExpired,
                'intakes' => $intakes,
                'dispatches' => $dispatches,
                'order_items' => $orderItems,
                'warranty_valid' => !$isExpired,
            ];
        }

        return [
            'found' => true,
            'message' => 'Batch details retrieved successfully.',
            'data' => $results,
        ];
    }
}
