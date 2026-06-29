<?php

namespace App\Observers;

use App\Models\DealerLedger;
use App\Services\DealerAgingService;

class DealerLedgerObserver
{
    protected $agingService;

    public function __construct(DealerAgingService $agingService)
    {
        $this->agingService = $agingService;
    }

    /**
     * Handle the DealerLedger "saved" event.
     */
    public function saved(DealerLedger $ledger): void
    {
        $this->agingService->calculateAging($ledger->dealer_id, true);
    }

    /**
     * Handle the DealerLedger "deleted" event.
     */
    public function deleted(DealerLedger $ledger): void
    {
        $this->agingService->calculateAging($ledger->dealer_id, true);
    }
}
