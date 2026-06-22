<?php

namespace App\Observers;

use App\Models\Warehouse;

class WarehouseObserver
{
    public function creating(Warehouse $warehouse)
    {
        if (company()) {
            $warehouse->company_id = company()->id;
        }
    }
}
