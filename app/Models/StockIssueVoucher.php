<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockIssueVoucher extends BaseModel
{
    use HasCompany;

    protected $fillable = [
        'company_id',
        'voucher_number',
        'issue_date',
        'issue_type_key',
        'issued_to',
        'status',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'issue_date' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockIssueItem::class, 'stock_issue_voucher_id');
    }

    public function deliveryOrders(): HasMany
    {
        return $this->hasMany(DeliveryOrder::class, 'stock_issue_voucher_id');
    }

    public function serials(): HasMany
    {
        return $this->hasMany(ProductSerial::class, 'stock_issue_voucher_id');
    }
}
