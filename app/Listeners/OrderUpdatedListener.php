<?php

namespace App\Listeners;

use App\Models\User;
use App\Events\OrderUpdatedEvent;
use App\Notifications\OrderUpdated;
use Illuminate\Support\Facades\Notification;

use App\Traits\HasNotificationRecipients;

class OrderUpdatedListener
{
    use HasNotificationRecipients;

    /**
     * Handle the event.
     *
     * @param OrderUpdatedEvent $event
     * @return void
     */
    public function handle(OrderUpdatedEvent $event)
    {
        Notification::send($event->notifyUser, new OrderUpdated($event->order));
        
        $excludeIds = ($event->notifyUser instanceof \Illuminate\Support\Collection) ? $event->notifyUser->pluck('id')->toArray() : [$event->notifyUser->id];
        $admins = $this->getAdminRecipients('order-createupdate-notification', $event->order->company->id, $event->order, $excludeIds);
        
        Notification::send($admins, new OrderUpdated($event->order));
    }
}
