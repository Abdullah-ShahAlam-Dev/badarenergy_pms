<?php

namespace Modules\WorkOrder\Entities;

use App\Models\BaseModel;
use App\Models\Tax;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkOrderItem extends BaseModel
{
    protected $table = 'work_order_items';

    protected $fillable = [
        'work_order_id',
        'product_id',
        'item_name',
        'description',
        'completion_date_time',
        'without_amount',
        'quantity',
        'unit',
        'rate',
        'sqm_from',
        'sqm_to',
        'tax_id',
        'tax_type',
        'tax_name',
        'tax_method',
        'tax_percent',
        'tax_amount',
        'total',
    ];

    protected $casts = [
        'completion_date_time' => 'datetime',
        'without_amount'  => 'boolean',
        'quantity'    => 'decimal:2',
        'rate'        => 'decimal:2',
        'sqm_from'    => 'decimal:2',
        'sqm_to'      => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'tax_amount'  => 'decimal:2',
        'total'       => 'decimal:2',
    ];

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class, 'work_order_id');
    }

    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class, 'tax_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Product::class, 'product_id');
    }

    /**
     * Calculate total for this item based on tax_type and tax_method.
     * Exclusive: total = (quantity * rate) + tax_amount
     * Amount: total = quantity * rate (tax tracked but not added to row total)
     */
    public static function calculateTotal(float $qty, float $rate, float $taxPercent, string $taxType, string $taxMethod = 'percent'): array
    {
        $subtotal = $qty * $rate;

        if ($taxMethod === 'fixed') {
            $taxAmount = $taxPercent;
        } else {
            $taxAmount = $subtotal * ($taxPercent / 100);
        }

        if ($taxType === 'exclusive') {
            $total = $subtotal + $taxAmount;
        } else {
            // 'amount' type: tax is tracked but not added to row total
            $total = $subtotal;
        }

        return [
            'tax_amount' => round($taxAmount, 2),
            'total'      => round($total, 2),
        ];
    }
}
