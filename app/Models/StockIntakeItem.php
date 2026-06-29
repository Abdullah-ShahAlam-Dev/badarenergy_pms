<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockIntakeItem extends BaseModel
{
    protected $fillable = [
        'intake_voucher_id',
        'product_id',
        'quantity_declared',
        'quantity_received',
        'unit_cost',
        'batch_id',
    ];

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(StockIntakeVoucher::class, 'intake_voucher_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductBatch::class, 'batch_id');
    }
}
