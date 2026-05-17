<?php

namespace Modules\WorkOrder\Entities;

use App\Models\BaseModel;
use App\Models\User;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorPayment extends BaseModel
{
    use HasCompany;

    protected $table = 'vendor_payments';

    protected $fillable = [
        'company_id',
        'work_order_id',
        'vendor_id',
        'amount',
        'payment_date',
        'payment_method',
        'reference_no',
        'notes',
        'added_by',
        'last_updated_by',
    ];

    protected $casts = [
        'payment_date' => 'datetime',
        'amount'       => 'decimal:2',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class, 'work_order_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
