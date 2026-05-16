<?php

namespace Modules\GatePass\Entities;

use App\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GatePassApprovalLog extends BaseModel
{
    protected $table = 'gate_pass_approval_logs';

    protected $fillable = [
        'gate_pass_request_id',
        'user_id',
        'action',
        'remarks',
        'ip_address'
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(GatePassRequest::class, 'gate_pass_request_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
