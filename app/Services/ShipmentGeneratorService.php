<?php

namespace App\Services;

use App\Models\ShipmentGenerationLog;
use App\Facades\WorkflowConfig;
use Illuminate\Support\Facades\DB;

class ShipmentGeneratorService
{
    /**
     * Generate sequential shipment number.
     *
     * @param int $companyId
     * @return string Generated shipment number.
     */
    public function generate(int $companyId): string
    {
        $prefix = WorkflowConfig::get('shipment', 'prefix', 'SHP', $companyId);
        $digitLength = (int) WorkflowConfig::get('shipment', 'digit_length', 6, $companyId);
        $includeYear = (bool) WorkflowConfig::get('shipment', 'include_year', false, $companyId);

        $yearPrefix = $includeYear ? date('y') : '';

        // Lock sequence inside short transaction
        $sequence = DB::transaction(function () use ($companyId, $prefix, $yearPrefix) {
            $log = ShipmentGenerationLog::where('company_id', $companyId)
                ->where('prefix', $prefix)
                ->where('year_prefix', $yearPrefix)
                ->lockForUpdate()
                ->first();

            if (!$log) {
                $log = ShipmentGenerationLog::create([
                    'company_id' => $companyId,
                    'prefix' => $prefix,
                    'year_prefix' => $yearPrefix,
                    'last_sequence' => 0,
                ]);
            }

            $currentSequence = $log->last_sequence;
            $log->increment('last_sequence', 1);

            return $currentSequence + 1;
        });

        $formattedSequence = str_pad((string) $sequence, $digitLength, '0', STR_PAD_LEFT);

        return $prefix . ($yearPrefix ? '-' . $yearPrefix : '') . '-' . $formattedSequence;
    }
}
