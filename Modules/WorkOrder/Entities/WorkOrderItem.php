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
        'item_name',
        'description',
        'quantity',
        'unit',
        'rate',
        'tax_id',
        'tax_type',
        'tax_percent',
        'tax_amount',
        'total',
    ];

    protected $casts = [
        'quantity'    => 'decimal:2',
        'rate'        => 'decimal:2',
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

    /**
     * Calculate total for this item based on tax_type.
     * Exclusive: total = (quantity * rate) + tax_amount
     * Inclusive: total = quantity * rate  (tax already inside rate)
     */
    public static function calculateTotal(float $qty, float $rate, float $taxPercent, string $taxType): array
    {
        $subtotal = $qty * $rate;

        if ($taxType === 'exclusive') {
            $taxAmount = $subtotal * ($taxPercent / 100);
            $total     = $subtotal + $taxAmount;
        } else {
            // Inclusive: tax is already embedded in rate
            $taxAmount = $subtotal - ($subtotal / (1 + $taxPercent / 100));
            $total     = $subtotal;
        }

        return [
            'tax_amount' => round($taxAmount, 2),
            'total'      => round($total, 2),
        ];
    }
}
