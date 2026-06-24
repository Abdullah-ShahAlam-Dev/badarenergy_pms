<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\ProductSerial
 *
 * @property int $id
 * @property int|null $company_id
 * @property int $product_id
 * @property int $warehouse_id
 * @property string $serial_number
 * @property string $status  available|sold|faulty|in_transit
 * @property int|null $invoice_id
 * @property \Illuminate\Support\Carbon|null $warranty_expires_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\Product $product
 * @property-read \App\Models\Warehouse $warehouse
 * @property-read \App\Models\Invoice|null $invoice
 */
class ProductSerial extends BaseModel
{
    use HasCompany;

    protected $table = 'product_serials';

    protected $fillable = [
        'company_id',
        'product_id',
        'warehouse_id',
        'serial_number',
        'status',
        'invoice_id',
        'warranty_expires_at',
    ];

    protected $casts = [
        'warranty_expires_at' => 'datetime',
    ];

    /**
     * Available serial status values.
     */
    public const STATUS_AVAILABLE = 'available';
    public const STATUS_SOLD = 'sold';
    public const STATUS_FAULTY = 'faulty';
    public const STATUS_IN_TRANSIT = 'in_transit';

    public const STATUSES = [
        self::STATUS_AVAILABLE,
        self::STATUS_SOLD,
        self::STATUS_FAULTY,
        self::STATUS_IN_TRANSIT,
    ];

    /**
     * Get the product associated with this serial.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Get the warehouse/outlet associated with this serial.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    /**
     * Get the invoice associated with this serial (if sold).
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
}
