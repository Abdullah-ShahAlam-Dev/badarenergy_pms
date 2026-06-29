<?php

namespace App\Models;

use App\Traits\HasCompany;
use App\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DealerLedgerAuditLog extends BaseModel
{
    use HasCompany;

    protected $table = 'dealer_ledger_audit_logs';

    public $timestamps = false;

    protected $fillable = [
        'company_id',
        'ledger_id',
        'user_id',
        'ip_address',
        'action',
        'old_values',
        'new_values',
        'reason',
        'created_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Get the company associated with this audit log.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * Get the user who triggered the ledger mutation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withoutGlobalScope(ActiveScope::class);
    }
}
