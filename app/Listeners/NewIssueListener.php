<?php

namespace App\Listeners;

use App\Events\NewIssueEvent;
use App\Notifications\NewIssue;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

use App\Traits\HasNotificationRecipients;

class NewIssueListener
{
    use HasNotificationRecipients;

    /**
     * Handle the event.
     *
     * @param NewIssueEvent $event
     * @return void
     */

    public function handle(NewIssueEvent $event)
    {
        $admins = $this->getAdminRecipients('new-support-ticket-request', $event->issue->company->id, $event->issue);
        Notification::send($admins, new NewIssue($event->issue));
    }
}
