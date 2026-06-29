<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SerialGenerationLog extends BaseModel
{
    use HasCompany;

    protected $fillable = [
        'company_id',
        'prefix',
        'year_prefix',
        'last_sequence',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
