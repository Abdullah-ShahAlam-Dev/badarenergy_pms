<?php

namespace Modules\CRMEmail\Entities;

use App\Models\BaseModel;
use App\Models\User;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends BaseModel
{
    use HasCompany;

    protected $table = 'crm_email_campaigns';

    protected $fillable = [
        'company_id',
        'name',
        'template_id',
        'email_body',
        'segment_id',
        'status',
        'scheduled_at',
        'launched_at',
        'launched_by',
        'added_by',
        'batch_id',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'launched_at' => 'datetime',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailMarketingTemplate::class, 'template_id');
    }

    public function segment(): BelongsTo
    {
        return $this->belongsTo(EmailSegment::class, 'segment_id');
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function launchedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'launched_by');
    }

    public function emails(): HasMany
    {
        return $this->hasMany(CampaignEmail::class, 'campaign_id');
    }
}
