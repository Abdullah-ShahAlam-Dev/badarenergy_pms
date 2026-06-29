<?php

namespace App\Events;

use App\Models\DeliveryOrder;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeliveryOrderCancelled
{
    use Dispatchable, SerializesModels;

    public DeliveryOrder $deliveryOrder;
    public ?int $userId;

    public function __construct(DeliveryOrder $deliveryOrder, ?int $userId = null)
    {
        $this->deliveryOrder = $deliveryOrder;
        $this->userId = $userId ?? (auth()->user() ? auth()->user()->id : null);
    }
}
