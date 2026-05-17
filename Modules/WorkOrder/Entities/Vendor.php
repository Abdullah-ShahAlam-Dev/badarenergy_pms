<?php

namespace Modules\WorkOrder\Entities;

use App\Models\BaseModel;
use App\Models\User;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends BaseModel
{
    use SoftDeletes, HasCompany;

    protected $table = 'vendors';

    protected $fillable = [
        'company_id',
        'vendor_name',
        'company_name',
        'designation',
        'mobile',
        'alternate_mobile',
        'email',
        'office_address',
        'cnic',
        'ntn',
        'bank_details',
        'category',
        'notes',
        'status',
        'added_by',
        'last_updated_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class, 'vendor_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(VendorPayment::class, 'vendor_id');
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
