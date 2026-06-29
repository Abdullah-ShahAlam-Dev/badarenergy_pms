<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WmsSetting extends BaseModel
{
    use HasCompany;

    protected $table = 'wms_settings';

    protected $fillable = [
        'company_id',
        'transfer_approval_required',
    ];

    protected $casts = [
        'transfer_approval_required' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
