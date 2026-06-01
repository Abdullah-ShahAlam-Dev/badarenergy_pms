<?php

namespace App\Listeners;

use App\Events\NewMentionChatEvent;
use App\Notifications\NewMentionChat;
use Illuminate\Support\Facades\Notification;

class NewMentionChatListener
{

    /**
     * Handle the event.
     *
     * @param NewMentionChatEvent $event
     * @return void
     */

    public function handle(NewMentionChatEvent $event)
    {
        try {
            Notification::send($event->notifyUser, new NewMentionChat($event->userChat));
        } catch (\Exception $e) {
            logger()->error('NewMentionChat notification sending failed: ' . $e->getMessage());
        }
    }

}
