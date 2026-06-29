<?php

namespace App\Services;

use App\Models\SerialGenerationLog;
use App\Models\ProductSerial;
use App\Enums\SerialStatus;
use App\Facades\WorkflowConfig;
use Illuminate\Support\Facades\DB;

class SerialGeneratorService
{
    /**
     * Generate sequential serial numbers and persist them to the database.
     *
     * @param int $productId
     * @param int $warehouseId
     * @param int $companyId
     * @param int $count
     * @param int|null $intakeVoucherId
     * @param int|null $batchId
     * @return array Array of generated ProductSerial models.
     */
    public function generate(
        int $productId,
        int $warehouseId,
        int $companyId,
        int $count,
        ?int $intakeVoucherId = null,
        ?int $batchId = null
    ): array {
        if ($count <= 0) {
            return [];
        }

        // 1. Fetch settings from settings repository
        $prefix = WorkflowConfig::get('serial', 'prefix', 'BE', $companyId);
        $digitLength = (int) WorkflowConfig::get('serial', 'digit_length', 8, $companyId);
        $includeYear = (bool) WorkflowConfig::get('serial', 'include_year', true, $companyId);
        
        $yearPrefix = $includeYear ? date('y') : '';

        // 2. Lock generation logs inside a short transaction
        $startSequence = DB::transaction(function () use ($companyId, $prefix, $yearPrefix, $count) {
            $log = SerialGenerationLog::where('company_id', $companyId)
                ->where('prefix', $prefix)
                ->where('year_prefix', $yearPrefix)
                ->lockForUpdate()
                ->first();

            if (!$log) {
                $log = SerialGenerationLog::create([
                    'company_id' => $companyId,
                    'prefix' => $prefix,
                    'year_prefix' => $yearPrefix,
                    'last_sequence' => 0,
                ]);
            }

            $currentSequence = $log->last_sequence;
            $log->increment('last_sequence', $count);

            return $currentSequence + 1;
        });

        // 3. Create the serial rows in batch
        $serials = [];
        for ($i = 0; $i < $count; $i++) {
            $sequence = $startSequence + $i;
            $formattedSequence = str_pad((string) $sequence, $digitLength, '0', STR_PAD_LEFT);
            $serialNumber = $prefix . $yearPrefix . $formattedSequence;

            $serials[] = ProductSerial::create([
                'company_id' => $companyId,
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'serial_number' => $serialNumber,
                'status' => SerialStatus::AVAILABLE->value,
                'batch_id' => $batchId,
                'intake_voucher_id' => $intakeVoucherId,
            ]);
        }

        return $serials;
    }
}
