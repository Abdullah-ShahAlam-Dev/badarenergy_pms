<?php

namespace Modules\DailyReports\Entities;

use App\Models\BaseModel;
use App\Traits\HasCompany;
use App\Traits\IconTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Asset;

class DailyReportFile extends BaseModel
{
    use IconTrait;

    protected $table = 'daily_report_files';

    protected $guarded = ['id'];

    protected $appends = ['file_url', 'icon'];

    public function getFileUrlAttribute()
    {
        return asset_url('daily-report-files/' . $this->hashname);
    }



    public function dailyReport(): BelongsTo
    {
        return $this->belongsTo(DailyReport::class, 'daily_report_id');
    }

}
