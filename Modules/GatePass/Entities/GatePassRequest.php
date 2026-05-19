<?php

namespace Modules\GatePass\Entities;

use App\Models\BaseModel;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GatePassRequest extends BaseModel
{
    use SoftDeletes;

    protected $table = 'gate_pass_requests';

    protected $fillable = [
        'company_id',
        'user_id',
        'department_id',
        'request_number',
        'request_date',
        'type',
        'return_type',
        'purpose',
        'from_location',
        'to_location',
        'vehicle_number',
        'driver_name',
        'expected_return_date',
        'status',
        'qr_code',
        'hod_id',
        'store_id',
        'security_id',
        'remarks',
        'hod_remarks',
        'store_remarks',
        'security_remarks'
    ];

    protected $casts = [
        'request_date' => 'datetime',
        'expected_return_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'department_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(GatePassItem::class, 'gate_pass_request_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(GatePassApprovalLog::class, 'gate_pass_request_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(GatePassAttachment::class, 'gate_pass_request_id');
    }

    public function returns(): HasMany
    {
        return $this->hasMany(GatePassItemReturn::class, 'gate_pass_request_id');
    }

    public function hod(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hod_id');
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(User::class, 'store_id');
    }

    public function security(): BelongsTo
    {
        return $this->belongsTo(User::class, 'security_id');
    }
}
