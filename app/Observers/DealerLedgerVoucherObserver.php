<?php

namespace App\Observers;

use App\Models\DealerLedgerVoucher;
use App\Services\DealerLedgerService;

class DealerLedgerVoucherObserver
{
    protected $ledgerService;

    public function __construct(DealerLedgerService $ledgerService)
    {
        $this->ledgerService = $ledgerService;
    }

    public function saving(DealerLedgerVoucher $voucher)
    {
        if (auth()->user()) {
            $voucher->created_by = auth()->user()->id;
        }
        if (company()) {
            $voucher->company_id = company()->id;
        }
    }

    public function saved(DealerLedgerVoucher $voucher)
    {
        $this->ledgerService->syncVoucherEntry($voucher);
    }

    public function deleting(DealerLedgerVoucher $voucher)
    {
        $this->ledgerService->deleteVoucherEntry($voucher->id);
    }
}
