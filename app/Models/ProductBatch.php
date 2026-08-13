<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductBatch extends BaseModel
{
    use HasCompany;

    protected $fillable = [
        'company_id',
        'product_id',
        'batch_number',
        'manufacturing_date',
        'production_lot',
        'expiry_date',
        'supplier_batch',
    ];

    protected $casts = [
        'manufacturing_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function serials(): HasMany
    {
        return $this->hasMany(ProductSerial::class, 'batch_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItems::class, 'batch_id');
    }

    public function deliveryOrderLines(): HasMany
    {
        return $this->hasMany(DeliveryOrderLine::class, 'batch_id');
    }

    /**
     * Auto-generate sequential batch number (1, 2, 3...) for OEM product per company.
     */
    public static function generateNextBatchNumber(int $companyId, int $productId): string
    {
        $lastBatch = static::where('company_id', $companyId)
            ->where('product_id', $productId)
            ->whereRaw("batch_number REGEXP '^[0-9]+$'")
            ->orderByRaw("CAST(batch_number AS UNSIGNED) DESC")
            ->first();

        if ($lastBatch && is_numeric($lastBatch->batch_number)) {
            return (string) ((int) $lastBatch->batch_number + 1);
        }

        $count = static::where('company_id', $companyId)
            ->where('product_id', $productId)
            ->count();

        return (string) ($count + 1);
    }
}

