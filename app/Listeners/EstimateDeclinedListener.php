<?php

namespace App\Listeners;

use App\Events\EstimateDeclinedEvent;
use App\Notifications\EstimateDeclined;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

use App\Traits\HasNotificationRecipients;

class EstimateDeclinedListener
{
    use HasNotificationRecipients;

    /**
     * Handle the event.
     *
     * @param EstimateDeclinedEvent $event
     * @return void
     */

    public function handle(EstimateDeclinedEvent $event)
    {
        $company = $event->estimate->company;
        $admins = $this->getAdminRecipients('estimate-notification', $company->id, $event->estimate);
        Notification::send($admins, new EstimateDeclined($event->estimate));
    }

}
