<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Builder;

/**
 * App\Models\Warehouse
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string $type  warehouse|outlet|assembly|transit
 * @property string|null $address
 * @property bool $is_active
 * @property int|null $company_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company|null $company
 * @method static Builder|Warehouse active()
 * @method static Builder|Warehouse newModelQuery()
 * @method static Builder|Warehouse newQuery()
 * @method static Builder|Warehouse query()
 * @mixin \Eloquent
 */
class Warehouse extends BaseModel
{
    use HasCompany;

    protected $table = 'warehouses';

    protected $fillable = [
        'name',
        'code',
        'type',
        'address',
        'is_active',
        'company_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Available warehouse types.
     */
    public const TYPES = ['warehouse', 'outlet', 'assembly', 'transit'];

    /**
     * Scope: only active warehouses (for use in transaction dropdowns).
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Human-readable type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'warehouse' => __('modules.warehouse.typeWarehouse'),
            'outlet'    => __('modules.warehouse.typeOutlet'),
            'assembly'  => __('modules.warehouse.typeAssembly'),
            'transit'   => __('modules.warehouse.typeTransit'),
            default     => ucfirst($this->type),
        };
    }
}
