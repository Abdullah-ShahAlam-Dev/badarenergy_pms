<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * App\Models\StockMovement
 *
 * @property int $id
 * @property int|null $company_id
 * @property int $product_id
 * @property int $warehouse_id
 * @property float $quantity
 * @property float $balance_after
 * @property string $type
 * @property string|null $reference_type
 * @property int|null $reference_id
 * @property string|null $remarks
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\Product $product
 * @property-read \App\Models\Warehouse $warehouse
 */
class StockMovement extends BaseModel
{
    use HasCompany;

    protected $table = 'stock_movements';

    protected $fillable = [
        'company_id',
        'product_id',
        'warehouse_id',
        'quantity',
        'balance_after',
        'type',
        'reference_type',
        'reference_id',
        'remarks',
    ];

    protected $casts = [
        'quantity' => 'float',
        'balance_after' => 'float',
    ];

    /**
     * Get the product associated with the stock movement.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Get the warehouse associated with the stock movement.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    /**
     * Get the referencing model (e.g. invoice, order, manual, etc.).
     */
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
