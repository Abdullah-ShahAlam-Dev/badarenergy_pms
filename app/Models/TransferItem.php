<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransferItem extends Model
{
    protected $table = 'transfer_items';

    protected $fillable = [
        'transfer_id',
        'product_id',
        'quantity',
        'quantity_received'
    ];

    protected $casts = [
        'quantity' => 'float',
        'quantity_received' => 'float'
    ];

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(StockTransfer::class, 'transfer_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function serials(): HasMany
    {
        return $this->hasMany(TransferSerial::class, 'transfer_item_id');
    }
}
