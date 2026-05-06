<?php

namespace Modules\DailyReports\Entities;

use App\Models\BaseModel;
use App\Models\Company;
use App\Models\ProjectTimeLog;
use App\Models\User;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyReport extends BaseModel
{
    use HasCompany;

    protected $table = 'daily_reports';

    protected $guarded = ['id'];

    protected $casts = [
        'report_date' => 'date',
        'timelog_ids_snapshot' => 'json',
        'locked_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * Fetch timelogs for the report date
     */
    public function getTimelogs()
    {
        return ProjectTimeLog::where('user_id', $this->user_id)
            ->whereDate('start_time', $this->report_date)
            ->with('project', 'task')
            ->get();
    }

    /**
     * Calculate and return total hours formatted
     */
    public function getTotalHoursAttribute()
    {
        $hours = floor($this->total_logged_minutes / 60);
        $minutes = $this->total_logged_minutes % 60;
        
        return $hours . 'h ' . $minutes . 'm';
    }

    public function files(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DailyReportFile::class, 'daily_report_id');
    }
}
