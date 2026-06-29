<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransferLog extends Model
{
    protected $table = 'stock_transfer_logs';

    public $timestamps = false;

    protected $fillable = [
        'transfer_id',
        'user_id',
        'action',
        'ip_address',
        'payload',
        'created_at'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'payload' => 'array'
    ];

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(StockTransfer::class, 'transfer_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
