<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransferSerial extends Model
{
    protected $table = 'transfer_serials';

    protected $fillable = [
        'transfer_item_id',
        'product_serial_id',
        'status',
        'received_at',
        'received_by'
    ];

    protected $casts = [
        'received_at' => 'datetime'
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(TransferItem::class, 'transfer_item_id');
    }

    public function serial(): BelongsTo
    {
        return $this->belongsTo(ProductSerial::class, 'product_serial_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
