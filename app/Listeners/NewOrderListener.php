<?php

namespace App\Listeners;

use App\Models\User;
use App\Events\NewOrderEvent;
use App\Notifications\NewOrder;
use Illuminate\Support\Facades\Notification;

use App\Traits\HasNotificationRecipients;

class NewOrderListener
{
    use HasNotificationRecipients;

    /**
     * Handle the event.
     *
     * @param NewOrderEvent $event
     * @return void
     */

    public function handle(NewOrderEvent $event)
    {
        Notification::send($event->notifyUser, new NewOrder($event->order));

        $excludeIds = ($event->notifyUser instanceof \Illuminate\Support\Collection) ? $event->notifyUser->pluck('id')->toArray() : [$event->notifyUser->id];
        $admins = $this->getAdminRecipients('order-createupdate-notification', $event->order->company->id, $event->order, $excludeIds);

        Notification::send($admins, new NewOrder($event->order));
    }
}
