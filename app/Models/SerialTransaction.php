<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SerialTransaction extends BaseModel
{
    public $timestamps = false; // Manually logged and immutable timestamp

    protected $fillable = [
        'product_serial_id',
        'serial_number',
        'event_type',
        'source_document_type',
        'source_document_id',
        'warehouse_id',
        'user_id',
        'previous_status',
        'new_status',
        'remarks',
    ];

    public function serial(): BelongsTo
    {
        return $this->belongsTo(ProductSerial::class, 'product_serial_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sourceDocument(): MorphTo
    {
        return $this->morphTo('source_document', 'source_document_type', 'source_document_id');
    }
}
