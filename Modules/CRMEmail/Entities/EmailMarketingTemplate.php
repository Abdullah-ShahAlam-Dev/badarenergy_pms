<?php

namespace Modules\CRMEmail\Entities;

use App\Models\BaseModel;
use App\Models\User;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailMarketingTemplate extends BaseModel
{
    use HasCompany;

    protected $table = 'crm_email_marketing_templates';

    protected $fillable = [
        'company_id',
        'title',
        'subject',
        'from_name',
        'from_email',
        'content',
        'status',
        'added_by',
        'last_updated_by',
    ];

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function lastUpdatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_updated_by');
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class, 'template_id');
    }
}
