<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\DeliveryOrder
 *
 * @property int $id
 * @property string $source_type
 * @property int|null $company_id
 * @property int|null $invoice_id
 * @property int|null $transfer_id
 * @property string $issue_date
 * @property int|null $dispatcher_id
 * @property string $status
 * @property string|null $vehicle_number
 * @property string|null $driver_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\Invoice|null $invoice
 * @property-read \App\Models\StockTransfer|null $stockTransfer
 * @property-read \App\Models\User|null $dispatcher
 */
class DeliveryOrder extends BaseModel
{
    use HasCompany;

    protected $fillable = [
        'company_id',
        'source_type',
        'invoice_id',
        'transfer_id',
        'stock_issue_voucher_id',
        'issue_date',
        'dispatcher_id',
        'status',
        'vehicle_number',
        'driver_name',
    ];

    protected $dates = [
        'issue_date',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function stockTransfer(): BelongsTo
    {
        return $this->belongsTo(StockTransfer::class, 'transfer_id');
    }

    public function stockIssueVoucher(): BelongsTo
    {
        return $this->belongsTo(StockIssueVoucher::class, 'stock_issue_voucher_id');
    }

    public function dispatcher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispatcher_id');
    }

    public function lines(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DeliveryOrderLine::class, 'delivery_order_id');
    }
}
