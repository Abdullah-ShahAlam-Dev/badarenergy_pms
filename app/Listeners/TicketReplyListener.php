<?php

namespace App\Listeners;

use App\Events\TicketReplyEvent;
use App\Notifications\NewTicketReply;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

class TicketReplyListener
{

    use \App\Traits\HasNotificationRecipients;

    /**
     * Handle the event.
     *
     * @param TicketReplyEvent $event
     * @return void
     */

    public function handle(TicketReplyEvent $event)
    {
        if (!is_null($event->notifyUser)) {
            Notification::send($event->notifyUser, new NewTicketReply($event->ticketReply));
        }
        else {
            $admins = $this->getAdminRecipients('agent-ticket', $event->ticketReply->ticket->company->id, $event->ticketReply->ticket);
            Notification::send($admins, new NewTicketReply($event->ticketReply));
        }
    }

}
