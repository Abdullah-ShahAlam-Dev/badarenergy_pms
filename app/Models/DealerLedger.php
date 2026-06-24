<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\DealerLedger
 *
 * @property int $id
 * @property int|null $company_id
 * @property int $dealer_id
 * @property int|null $invoice_id
 * @property int|null $payment_id
 * @property \Illuminate\Support\Carbon $date
 * @property string $description
 * @property float $debit
 * @property float $credit
 * @property float $balance
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\User $dealer
 * @property-read \App\Models\Invoice|null $invoice
 * @property-read \App\Models\Payment|null $payment
 */
class DealerLedger extends BaseModel
{
    use HasCompany;

    protected $table = 'dealer_ledgers';

    protected $fillable = [
        'company_id',
        'dealer_id',
        'invoice_id',
        'payment_id',
        'date',
        'description',
        'debit',
        'credit',
        'balance',
    ];

    protected $casts = [
        'date' => 'datetime',
        'debit' => 'float',
        'credit' => 'float',
        'balance' => 'float',
    ];

    /**
     * Get the dealer associated with this ledger entry.
     */
    public function dealer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dealer_id');
    }

    /**
     * Get the invoice associated with this ledger entry.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    /**
     * Get the payment associated with this ledger entry.
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }
}
