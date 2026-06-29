<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShipmentGenerationLog extends BaseModel
{
    use HasCompany;

    protected $table = 'shipment_generation_logs';

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
