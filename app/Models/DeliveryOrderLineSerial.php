<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryOrderLineSerial extends BaseModel
{
    protected $fillable = [
        'delivery_order_line_id',
        'product_serial_id',
        'status',
        'remarks',
    ];

    public function line(): BelongsTo
    {
        return $this->belongsTo(DeliveryOrderLine::class, 'delivery_order_line_id');
    }

    public function serial(): BelongsTo
    {
        return $this->belongsTo(ProductSerial::class, 'product_serial_id');
    }
}
