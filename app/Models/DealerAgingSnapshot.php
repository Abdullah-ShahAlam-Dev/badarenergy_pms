<?php

namespace App\Models;

use App\Traits\HasCompany;
use App\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DealerAgingSnapshot extends BaseModel
{
    use HasCompany;

    protected $table = 'dealer_aging_snapshots';

    protected $fillable = [
        'company_id',
        'dealer_id',
        'outstanding',
        'current',
        'bucket_1_15',
        'bucket_16_30',
        'bucket_31_60',
        'bucket_61_90',
        'bucket_91_plus',
        'credit_utilization',
        'last_payment_date',
        'days_since_payment',
        'last_updated_at',
    ];

    protected $casts = [
        'outstanding' => 'float',
        'current' => 'float',
        'bucket_1_15' => 'float',
        'bucket_16_30' => 'float',
        'bucket_31_60' => 'float',
        'bucket_61_90' => 'float',
        'bucket_91_plus' => 'float',
        'credit_utilization' => 'float',
        'last_payment_date' => 'date',
        'days_since_payment' => 'integer',
        'last_updated_at' => 'datetime',
    ];

    /**
     * Get the dealer associated with this snapshot.
     */
    public function dealer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dealer_id')->withoutGlobalScope(ActiveScope::class);
    }

    /**
     * Get the company associated with this snapshot.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
