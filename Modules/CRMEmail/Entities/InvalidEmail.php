<?php

namespace Modules\CRMEmail\Entities;

use App\Models\BaseModel;
use App\Models\User;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvalidEmail extends BaseModel
{
    use HasCompany;

    protected $table = 'crm_invalid_emails';

    protected $fillable = [
        'company_id',
        'email',
        'reason',
        'added_by',
    ];

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
