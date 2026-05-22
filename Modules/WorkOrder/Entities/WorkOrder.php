<?php

namespace Modules\WorkOrder\Entities;

use App\Models\BaseModel;
use App\Models\Event;
use App\Models\User;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkOrder extends BaseModel
{
    use SoftDeletes, HasCompany;

    protected $table = 'work_orders';

    protected $fillable = [
        'company_id',
        'wo_number',
        'wo_date',
        'completion_date_time',
        'event_id',
        'vendor_id',
        'work_category',
        'venue',
        'no_of_days',
        'priority',
        'description',
        'remarks',
        'terms_conditions',
        'special_instructions',
        'sub_total',
        'discount',
        'discount_type',
        'tax_amount',
        'grand_total',
        'status',
        'approval_required',
        'created_by',
        'approved_by',
        'approved_at',
        'added_by',
        'last_updated_by',
    ];

    protected $casts = [
        'wo_date'               => 'datetime',
        'completion_date_time'  => 'datetime',
        'approved_at'           => 'datetime',
        'created_at'            => 'datetime',
        'updated_at'            => 'datetime',
        'deleted_at'            => 'datetime',
    ];

    /**
     * Statuses available for a work order.
     */
    public const STATUSES = [
        'draft',
        'pending_approval',
        'approved',
        'rejected',
        'in_progress',
        'completed',
        'cancelled',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(WorkOrderItem::class, 'work_order_id');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(WorkOrderApproval::class, 'work_order_id')->orderBy('created_at', 'asc');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(VendorPayment::class, 'work_order_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ── Backward Compatibility ─────────────────────────────────────────────────

    /**
     * Accessor: allow reading $workOrder->delivery_date as an alias
     * for completion_date_time to avoid breaking existing code.
     */
    public function getDeliveryDateAttribute(): mixed
    {
        return $this->completion_date_time;
    }

    /**
     * Mutator: allow setting $workOrder->delivery_date as an alias
     * for completion_date_time to avoid breaking existing code.
     */
    public function setDeliveryDateAttribute(mixed $value): void
    {
        $this->attributes['completion_date_time'] = $value;
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function isLocked(): bool
    {
        return in_array($this->status, ['approved', 'completed']);
    }

    public function isPendingApproval(): bool
    {
        return $this->status === 'pending_approval';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Generate next WO number for the company (format: WO-2026-0001).
     */
    public static function nextWoNumber(): string
    {
        $max = static::withTrashed()->max('id') ?? 0;
        return 'WO-' . date('Y') . '-' . sprintf('%04d', $max + 1);
    }
}
