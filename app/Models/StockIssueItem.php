<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockIssueItem extends BaseModel
{
    protected $fillable = [
        'stock_issue_voucher_id',
        'product_id',
        'quantity',
    ];

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(StockIssueVoucher::class, 'stock_issue_voucher_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
