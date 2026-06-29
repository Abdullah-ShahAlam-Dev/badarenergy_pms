<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockIntakeVoucher extends BaseModel
{
    use HasCompany;

    protected $fillable = [
        'company_id',
        'shipment_id',
        'warehouse_id',
        'voucher_number',
        'intake_date',
        'status',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'intake_date' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class, 'shipment_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockIntakeItem::class, 'intake_voucher_id');
    }

    public function serials(): HasMany
    {
        return $this->hasMany(ProductSerial::class, 'intake_voucher_id');
    }
}
