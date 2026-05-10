<?php

namespace Modules\DailyReports\Entities;

use App\Models\BaseModel;
use App\Traits\HasCompany;

class DailyReportSetting extends BaseModel
{
    use HasCompany;

    protected $table = 'daily_report_settings';

    protected $fillable = ['company_id', 'reporter_ids'];

    protected $casts = [
        'reporter_ids' => 'array'
    ];

    public static function getReporterIds()
    {
        $setting = self::where('company_id', company()->id)->first();
        return $setting ? ($setting->reporter_ids ?? []) : [];
    }
}
