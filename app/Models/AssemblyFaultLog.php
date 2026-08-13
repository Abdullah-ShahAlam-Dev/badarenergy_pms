<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssemblyFaultLog extends BaseModel
{
    use HasCompany;

    protected $table = 'assembly_fault_logs';

    protected $fillable = [
        'company_id',
        'assembly_order_id',
        'product_id',
        'warehouse_id',
        'fault_quantity',
        'reason',
        'user_id',
    ];

    protected $casts = [
        'fault_quantity' => 'float',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function assemblyOrder(): BelongsTo
    {
        return $this->belongsTo(AssemblyOrder::class, 'assembly_order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
