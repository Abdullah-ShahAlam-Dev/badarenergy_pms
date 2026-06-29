<?php

namespace App\Services;

use App\Models\IntakeGenerationLog;
use App\Facades\WorkflowConfig;
use Illuminate\Support\Facades\DB;

class IntakeNumberGeneratorService
{
    /**
     * Generate sequential stock intake voucher number.
     *
     * @param int $companyId
     * @return string Generated voucher number.
     */
    public function generate(int $companyId): string
    {
        $prefix = WorkflowConfig::get('intake', 'prefix', 'SIV', $companyId);
        $digitLength = (int) WorkflowConfig::get('intake', 'digit_length', 6, $companyId);
        $includeYear = (bool) WorkflowConfig::get('intake', 'include_year', true, $companyId);

        $yearPrefix = $includeYear ? date('y') : '';

        // Lock sequence inside short transaction
        $sequence = DB::transaction(function () use ($companyId, $prefix, $yearPrefix) {
            $log = IntakeGenerationLog::where('company_id', $companyId)
                ->where('prefix', $prefix)
                ->where('year_prefix', $yearPrefix)
                ->lockForUpdate()
                ->first();

            if (!$log) {
                $log = IntakeGenerationLog::create([
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
