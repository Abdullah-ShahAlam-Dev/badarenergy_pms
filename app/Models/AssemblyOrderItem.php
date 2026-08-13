<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssemblyOrderItem extends BaseModel
{
    protected $table = 'assembly_order_items';

    protected $fillable = [
        'assembly_order_id',
        'raw_product_id',
        'quantity_required',
        'quantity_used',
        'quantity_faulty',
    ];

    protected $casts = [
        'quantity_required' => 'float',
        'quantity_used' => 'float',
        'quantity_faulty' => 'float',
    ];

    public function assemblyOrder(): BelongsTo
    {
        return $this->belongsTo(AssemblyOrder::class, 'assembly_order_id');
    }

    public function rawProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'raw_product_id');
    }
}
