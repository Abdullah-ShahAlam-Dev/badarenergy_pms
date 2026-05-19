<?php

namespace Modules\GatePass\Entities;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GatePassItem extends BaseModel
{
    protected $table = 'gate_pass_items';

    protected $fillable = [
        'gate_pass_request_id',
        'product_id',
        'item_name',
        'quantity',
        'returned_quantity',
        'settled_quantity',
        'unit',
        'serial_number',
        'asset_tag',
        'condition',
        'remarks'
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(GatePassRequest::class, 'gate_pass_request_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Product::class, 'product_id');
    }
}
