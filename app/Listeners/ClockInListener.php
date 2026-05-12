<?php

namespace App\Listeners;

use App\Models\User;
use App\Events\ClockInEvent;
use App\Notifications\ClockIn;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

use App\Traits\HasNotificationRecipients;

class ClockInListener
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
     * @param  \App\Events\ClockInEvent  $event
     * @return void
     */
    public function handle(ClockInEvent $event)
    {
        $company = $event->attendance->company;
        $admins = $this->getAdminRecipients('clock-in-notification', $company->id, $event->attendance);
        Notification::send($admins, new ClockIn($event->attendance));

    }

}
