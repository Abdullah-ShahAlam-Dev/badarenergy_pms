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
        'batch_id',
        'intake_voucher_id',
        'stock_issue_voucher_id',
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
    public const STATUS_RECEIVING = 'receiving';
    public const STATUS_QC_PENDING = 'qc_pending';
    public const STATUS_RESERVED = 'reserved';
    public const STATUS_DISPATCHED = 'dispatched';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_WARRANTY_CLAIM = 'warranty_claim';
    public const STATUS_REPLACEMENT_ISSUED = 'replacement_issued';
    public const STATUS_RETURNED = 'returned';
    public const STATUS_REFURBISHED = 'refurbished';
    public const STATUS_SCRAPPED = 'scrapped';
    public const STATUS_INTERNAL_USE = 'internal_use';

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

    /**
     * Get the batch associated with this serial.
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductBatch::class, 'batch_id');
    }

    /**
     * Get the stock intake voucher associated with this serial.
     */
    public function intakeVoucher(): BelongsTo
    {
        return $this->belongsTo(StockIntakeVoucher::class, 'intake_voucher_id');
    }



    /**
     * Get the stock issue voucher associated with this serial.
     */
    public function stockIssueVoucher(): BelongsTo
    {
        return $this->belongsTo(StockIssueVoucher::class, 'stock_issue_voucher_id');
    }

    /**
     * Get the transaction history logs for this serial.
     */
    public function transactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SerialTransaction::class, 'product_serial_id');
    }
}
