<?php

namespace App\Listeners;

use App\Events\NewProductPurchaseEvent;
use App\Notifications\NewProductPurchaseRequest;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

use App\Traits\HasNotificationRecipients;

class NewProductPurchaseListener
{
    use HasNotificationRecipients;

    /**
     * NewProductPurchaseListener constructor.
     */
    public function __construct()
    {
        //
    }

    /**
     * @param NewProductPurchaseEvent $event
     */

    public function handle(NewProductPurchaseEvent $event)
    {
        $admins = $this->getAdminRecipients('new-product-purchase-request', $event->invoice->company->id, $event->invoice);
        Notification::send($admins, new NewProductPurchaseRequest($event->invoice));
    }

}
