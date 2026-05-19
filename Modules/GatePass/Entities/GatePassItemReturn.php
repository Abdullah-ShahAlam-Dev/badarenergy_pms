<?php

namespace Modules\GatePass\Entities;

use App\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GatePassItemReturn extends BaseModel
{
    protected $table = 'gate_pass_item_returns';

    protected $fillable = [
        'gate_pass_request_id',
        'gate_pass_item_id',
        'user_id',
        'quantity',
        'type',
        'status',
        'remarks',
        'settlement_note'
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(GatePassRequest::class, 'gate_pass_request_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(GatePassItem::class, 'gate_pass_item_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
