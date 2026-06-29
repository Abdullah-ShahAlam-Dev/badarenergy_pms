<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Inventory
 *
 * @property int $id
 * @property int|null $company_id
 * @property int $product_id
 * @property int $warehouse_id
 * @property float $quantity
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\Product $product
 * @property-read \App\Models\Warehouse $warehouse
 */
class Inventory extends BaseModel
{
    use HasCompany;

    protected $table = 'inventories';

    protected $fillable = [
        'company_id',
        'product_id',
        'warehouse_id',
        'quantity',
        'quantity_faulty',
        'quantity_in_transit',
        'average_cost',
    ];

    protected $casts = [
        'quantity' => 'float',
        'quantity_faulty' => 'float',
        'quantity_in_transit' => 'float',
        'average_cost' => 'float',
    ];

    /**
     * Get the product associated with the inventory.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Get the warehouse associated with the inventory.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }
}
