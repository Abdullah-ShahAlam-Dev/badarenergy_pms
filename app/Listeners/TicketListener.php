<?php

namespace App\Listeners;

use App\Events\TicketEvent;
use App\Notifications\NewTicket;
use App\Notifications\TicketAgent;
use App\Models\User;
use App\Notifications\MentionTicketAgent;
use Illuminate\Support\Facades\Notification;

use App\Traits\HasNotificationRecipients;

class TicketListener
{
    use HasNotificationRecipients;

    /**
     * Handle the event.
     *
     * @param TicketEvent $event
     * @return void
     */

    public function handle(TicketEvent $event)
    {

        if ($event->notificationName == 'NewTicket') {
            $admins = $this->getAdminRecipients('new-support-ticket-request', $event->ticket->company_id, $event->ticket);
            Notification::send($admins, new NewTicket($event->ticket));
        }
        elseif ($event->notificationName == 'TicketAgent') {
            Notification::send($event->ticket->agent, new TicketAgent($event->ticket));

        }
        elseif ($event->notificationName == 'MentionTicketAgent'){
            Notification::send($event->mentionUser, new MentionTicketAgent($event->ticket));

        }
    }

}
