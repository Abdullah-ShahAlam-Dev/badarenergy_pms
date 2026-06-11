<?php

namespace Modules\CRMEmail\Entities;

use App\Models\BaseModel;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CampaignEmail extends BaseModel
{
    use HasCompany;

    protected $table = 'crm_campaign_emails';

    protected $fillable = [
        'company_id',
        'campaign_id',
        'recipient_type',
        'recipient_id',
        'email',
        'message_id',
        'status',
        'error_message',
        'sent_at',
        'attempts',
        'next_retry_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'next_retry_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'campaign_id');
    }

    public function recipient(): MorphTo
    {
        return $this->morphTo('recipient', 'recipient_type', 'recipient_id');
    }
}
