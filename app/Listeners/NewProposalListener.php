<?php

namespace App\Listeners;

use App\Events\NewInvoiceEvent;
use App\Events\NewProposalEvent;
use App\Models\User;
use App\Notifications\NewProposal;
use App\Notifications\ProposalSigned;
use Illuminate\Support\Facades\Notification;

class NewProposalListener
{

    use \App\Traits\HasNotificationRecipients;

    /**
     * @param NewProposalEvent $event
     */

    public function handle(NewProposalEvent $event)
    {
        if ($event->type == 'signed' && $event->proposal->status != 'waiting') {
            $admins = $this->getAdminRecipients('new-proposal', $event->proposal->company->id, $event->proposal);
            // Notify admins
            Notification::send($admins, new ProposalSigned($event->proposal));
        }
        else {
            // Notify client
            Notification::send($event->proposal->lead, new NewProposal($event->proposal));
        }
    }

}
