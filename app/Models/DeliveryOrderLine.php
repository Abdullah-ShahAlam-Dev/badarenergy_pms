<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryOrderLine extends BaseModel
{
    protected $fillable = [
        'delivery_order_id',
        'product_id',
        'batch_id',
        'quantity_requested',
        'quantity_dispatched',
        'quantity_delivered',
        'quantity_damaged',
        'quantity_short',
    ];

    public function deliveryOrder(): BelongsTo
    {
        return $this->belongsTo(DeliveryOrder::class, 'delivery_order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductBatch::class, 'batch_id');
    }

    public function lineSerials(): HasMany
    {
        return $this->hasMany(DeliveryOrderLineSerial::class, 'delivery_order_line_id');
    }
}

