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
}
