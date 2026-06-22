<?php

namespace App\Observers;

use App\Models\StockMovement;

class StockMovementObserver
{
    public function creating(StockMovement $stockMovement)
    {
        if (company()) {
            $stockMovement->company_id = company()->id;
        }
    }
}
