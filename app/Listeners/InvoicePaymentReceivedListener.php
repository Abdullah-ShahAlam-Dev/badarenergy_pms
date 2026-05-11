<?php

namespace App\Listeners;

use App\Events\InvoicePaymentReceivedEvent;
use App\Notifications\InvoicePaymentReceived;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

class InvoicePaymentReceivedListener
{

    use \App\Traits\HasNotificationRecipients;

    /**
     * Handle the event.
     *
     * @param InvoicePaymentReceivedEvent $event
     * @return void
     */

    public function handle(InvoicePaymentReceivedEvent $event)
    {
        $admins = $this->getAdminRecipients('payment-notification', $event->payment->company->id, $event->payment);
        Notification::send($admins, new InvoicePaymentReceived($event->payment));
    }

}
