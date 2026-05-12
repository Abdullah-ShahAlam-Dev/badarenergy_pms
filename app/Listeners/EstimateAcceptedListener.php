<?php

namespace App\Listeners;

use App\Models\User;
use App\Events\EstimateAcceptedEvent;
use App\Notifications\EstimateAccepted;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

use App\Traits\HasNotificationRecipients;

class EstimateAcceptedListener
{
    use HasNotificationRecipients;

    /**
     * Create the event listener.
     *
     * @return void
     */

    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\EstimateAcceptedEvent  $event
     * @return void
     */
    public function handle(EstimateAcceptedEvent $event)
    {
        $company = $event->estimate->company;
        $admins = $this->getAdminRecipients('estimate-notification', $company->id, $event->estimate);
        Notification::send($admins, new EstimateAccepted($event->estimate));
    }

}
