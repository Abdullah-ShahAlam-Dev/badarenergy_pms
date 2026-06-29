<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DealerAgingBucket extends BaseModel
{
    use HasCompany;

    protected $table = 'dealer_aging_buckets';

    protected $fillable = [
        'company_id',
        'name',
        'min_days',
        'max_days',
        'sequence',
    ];

    protected $casts = [
        'min_days' => 'integer',
        'max_days' => 'integer',
        'sequence' => 'integer',
    ];

    /**
     * Get the company associated with this bucket.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
