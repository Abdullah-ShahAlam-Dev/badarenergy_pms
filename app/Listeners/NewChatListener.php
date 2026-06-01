<?php

namespace App\Listeners;

use App\Events\NewChatEvent;
use App\Models\User;
use App\Notifications\NewChat;
use App\Scopes\ActiveScope;
use Illuminate\Support\Facades\Notification;

class NewChatListener
{

    /**
     * Handle the event.
     *
     * @param NewChatEvent $event
     * @return void
     */

    public function handle(NewChatEvent $event)
    {
        $userChat = $event->userChat;

        try {
            if ($userChat->message_group_id) {
                $group = $userChat->messageGroup;
                if ($group) {
                    $members = $group->members;
                    $notifyUsers = $members->filter(function ($user) use ($userChat) {
                        return $user->id != $userChat->from;
                    });
                    
                    if ($notifyUsers->isNotEmpty()) {
                        Notification::send($notifyUsers, new NewChat($userChat));
                    }
                }
            } else {
                $notifyUser = User::withoutGlobalScope(ActiveScope::class)->findOrFail($userChat->user_id);
                Notification::send($notifyUser, new NewChat($userChat));
            }
        } catch (\Exception $e) {
            logger()->error('NewChat notification sending failed: ' . $e->getMessage());
        }
    }

}
