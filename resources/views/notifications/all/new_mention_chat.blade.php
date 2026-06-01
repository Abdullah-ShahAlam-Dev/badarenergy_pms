@php
$notificationUser = \App\Models\User::find($notification->data['user_one']);
$isGroup = isset($notification->data['message_group_id']) && $notification->data['message_group_id'] !== null;
if (!isset($notification->data['from_name'])) {
    $chat = \App\Models\UserChat::with('fromUser')->find($notification->data['id']);
    $fromName = $chat ? $chat->fromUser->name : '';
} else {
    $fromName = $notification->data['from_name'];
}
@endphp

@if ($notificationUser)
    @if ($isGroup)
        <x-cards.notification :notification="$notification" :link="route('messages.index') . '?group=' . $notification->data['message_group_id']"
            :image="$notificationUser->image_url" :title="__('email.newChat.mentionSubject') . ' in ' . $notification->data['group_name']" :text="$fromName"
            :time="$notification->created_at" />
    @else
        <x-cards.notification :notification="$notification" :link="route('messages.index') . '?user=' . $notification->data['user_one']"
            :image="$notificationUser->image_url" :title="__('email.newChat.mentionSubject')" :text="$notificationUser->name"
            :time="$notification->created_at" />
    @endif
@endif
