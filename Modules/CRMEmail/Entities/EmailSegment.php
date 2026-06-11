<?php

namespace Modules\CRMEmail\Entities;

use App\Models\BaseModel;
use App\Models\User;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailSegment extends BaseModel
{
    use HasCompany;

    protected $table = 'crm_email_segments';

    protected $fillable = [
        'company_id',
        'name',
        'sources',
        'criteria',
        'added_by',
    ];

    protected $casts = [
        'sources' => 'array',
        'criteria' => 'array',
    ];

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class, 'segment_id');
    }
}
