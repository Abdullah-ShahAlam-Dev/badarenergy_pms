<?php

namespace Modules\WorkOrder\Entities;

use App\Models\BaseModel;
use App\Models\User;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalMapping extends BaseModel
{
    use HasCompany;

    protected $table = 'approval_mappings';

    protected $fillable = [
        'company_id',
        'creator_id',
        'approver_id',
        'is_active',
        'added_by',
        'last_updated_by',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    /**
     * Find the assigned approver for a given creator in the current company context.
     */
    public static function getApproverFor(int $creatorId): ?self
    {
        return static::where('company_id', company()->id)
            ->where('creator_id', $creatorId)
            ->where('is_active', 1)
            ->first();
    }
}
