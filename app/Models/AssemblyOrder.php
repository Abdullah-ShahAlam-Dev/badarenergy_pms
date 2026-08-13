<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssemblyOrder extends BaseModel
{
    use HasCompany;

    protected $table = 'assembly_orders';

    protected $fillable = [
        'company_id',
        'assembly_number',
        'target_product_id',
        'warehouse_id',
        'quantity_to_assemble',
        'status',
        'notes',
        'created_by',
        'completed_at',
    ];

    protected $casts = [
        'quantity_to_assemble' => 'float',
        'completed_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function targetProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'target_product_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(AssemblyOrderItem::class, 'assembly_order_id');
    }

    public function faultLogs(): HasMany
    {
        return $this->hasMany(AssemblyFaultLog::class, 'assembly_order_id');
    }

    public static function nextAssemblyNumber(): string
    {
        $max = static::max('id') ?? 0;
        return 'ASM-' . date('Y') . '-' . sprintf('%04d', $max + 1);
    }
}
