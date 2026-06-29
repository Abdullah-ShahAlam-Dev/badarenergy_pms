<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shipment extends BaseModel
{
    use HasCompany;

    protected $fillable = [
        'company_id',
        'shipment_number',
        'container_number',
        'bill_of_lading',
        'manufacturing_ref',
        'port_of_origin',
        'port_of_discharge',
        'eta',
        'arrival_date',
        'status',
        'remarks',
    ];

    protected $casts = [
        'eta' => 'date',
        'arrival_date' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function stockIntakeVouchers(): HasMany
    {
        return $this->hasMany(StockIntakeVoucher::class, 'shipment_id');
    }
}
