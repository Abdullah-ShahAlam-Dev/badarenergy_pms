<?php

namespace App\Observers;

use App\Models\Inventory;

class InventoryObserver
{
    public function creating(Inventory $inventory)
    {
        if (company()) {
            $inventory->company_id = company()->id;
        }
    }
}
