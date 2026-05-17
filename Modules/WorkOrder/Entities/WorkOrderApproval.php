<?php

namespace Modules\WorkOrder\Entities;

use App\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkOrderApproval extends BaseModel
{
    protected $table = 'work_order_approvals';

    protected $fillable = [
        'work_order_id',
        'user_id',
        'action',
        'remarks',
        'ip_address',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class, 'work_order_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
