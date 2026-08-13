<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\GatePass\Entities\GatePassRequest;

class CareOfLedger extends BaseModel
{
    use HasCompany;

    protected $table = 'care_of_ledgers';

    protected $fillable = [
        'company_id',
        'care_of_id',
        'order_id',
        'delivery_order_id',
        'gate_pass_request_id',
        'date',
        'transaction_type',
        'reference_number',
        'description',
        'item_details',
        'debit',
        'credit',
        'balance',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'date' => 'datetime',
        'debit' => 'float',
        'credit' => 'float',
        'balance' => 'float',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function careOfUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'care_of_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function deliveryOrder(): BelongsTo
    {
        return $this->belongsTo(DeliveryOrder::class, 'delivery_order_id');
    }

    public function gatePassRequest(): BelongsTo
    {
        return $this->belongsTo(GatePassRequest::class, 'gate_pass_request_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
