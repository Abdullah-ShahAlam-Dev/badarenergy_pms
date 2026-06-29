<?php

namespace App\Events;

use App\Models\StockIntakeVoucher;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StockIntakeApproved
{
    use Dispatchable, SerializesModels;

    public StockIntakeVoucher $voucher;

    public function __construct(StockIntakeVoucher $voucher)
    {
        $this->voucher = $voucher;
    }
}
