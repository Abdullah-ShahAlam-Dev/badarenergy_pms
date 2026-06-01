@php
$isGroup = $message->message_group_id ? true : false;
if ($isGroup) {
    $chatName = $message->messageGroup ? $message->messageGroup->group_name : 'Group Chat';
    $unreadMessageCount = 0;
    $chatId = $message->message_group_id;
    $chatType = 'group';
} else {
    $user = $message->from != user()->id ? $message->fromUser : $message->toUser;
    $chatName = $user->name;
    $chatId = $user->id;
    $chatType = 'user';
    if ($message->toUser->unread_messages_count > 0) {
        $unreadMessageCount = $message->toUser->unread_messages_count;
    } else {
        $unreadMessageCount = $message->fromUser->unread_messages_count;
    }
}
@endphp

<div class="card rounded-0 border-top-0 border-left-0 border-right-0 user_list_box position-relative" id="{{ $isGroup ? 'group-no-' . $chatId : 'user-no-' . $chatId }}">
    <a @class([
        'tablinks',
        'show-user-messages',
        'unread-message' => $unreadMessageCount > 0,
        'd-block',
        'w-100',
    ]) href="javascript:;" data-name="{{ $chatName }}"
        data-user-id="{{ $chatId }}"
        data-chat-type="{{ $chatType }}"
        data-unread-message-count="{{ $unreadMessageCount }}">
        <div class="card-horizontal user-message pr-4 w-100">
            <div class="card-img">
                @if ($isGroup)
                    <div class="group-avatar-circle d-flex align-items-center justify-content-center" style="width:38px; height:38px; border-radius:50%; background: linear-gradient(135deg, #6366f1, #4f46e5); color:#fff; font-size:14px; box-shadow: 0 2px 4px rgba(79, 70, 229, 0.2);">
                        <i class="fa fa-users"></i>
                    </div>
                @else
                    <img class="" src="{{ $user->image_url }}" alt="{{ $user->name }}">
                @endif
            </div>
            <div class="card-body border-0 pl-0" style="min-width: 0;">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="card-title f-12 f-w-500 text-dark-grey mb-0 text-truncate" style="max-width: 55%;">{{ $chatName }}</h4>
                    <div class="d-flex align-items-center">
                        <p @class([
                            'card-date',
                            'f-11',
                            'text-dark-grey',
                            'mb-0',
                            'pr-4' => $isGroup,
                            'pr-2' => !$isGroup
                        ])>
                            {{ \Carbon\Carbon::parse($message->created_at)->diffForHumans() }}</p>
                    </div>
                </div>
                <div @class([
                    'card-text',
                    'f-11',
                    'text-lightest',
                    'd-flex',
                    'justify-content-between',
                    'message-mention',
                    'text-dark' => $unreadMessageCount > 0,
                    'font-weight-bold' => $unreadMessageCount > 0,
                ]) style="min-width: 0;">
                    <div class="text-truncate" style="min-width: 0; flex: 1;"> 
                        <p class="mb-0 text-truncate">
                            @if ($isGroup)
                                <strong class="text-indigo">{{ $message->fromUser->name }}:</strong> 
                            @endif
                            @if (str_starts_with($message->message, '[deleted] '))
                                <span class="font-italic text-muted"><i class="fa fa-ban mr-1"></i>{{ substr($message->message, 10) }}</span>
                            @else
                                {{ strip_tags($message->message) }}
                            @endif
                        </p>
                    </div>

                    @if ($unreadMessageCount > 0)
                        <div class="pl-2">
                            <span class="badge badge-primary ml-1 unread-count">{{ $unreadMessageCount }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </a>

    @if ($isGroup && $message->messageGroup)
        <div class="dropdown btn-group-dropdown position-absolute" style="right: 12px; top: 12px; z-index: 10;">
            <button class="btn btn-xs p-0 border-0 text-dark-grey dropdown-toggle"
                type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="outline: none; box-shadow: none; background: transparent;">
                <i class="fa fa-ellipsis-v"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-2" style="min-width: 180px;">
                <h6 class="dropdown-header px-2 py-1 f-10 text-uppercase text-indigo font-weight-bold">
                    Members ({{ $message->messageGroup->members->count() }})
                </h6>
                <div class="dropdown-divider my-1"></div>
                <div style="max-height: 140px; overflow-y: auto;">
                    @foreach ($message->messageGroup->members as $member)
                        <div class="d-flex align-items-center px-2 py-1">
                            <img src="{{ $member->image_url }}" class="rounded-circle mr-2" style="width: 20px; height: 20px; object-fit: cover;" alt="{{ $member->name }}">
                            <span class="f-11 text-dark-grey text-truncate" style="max-width: 120px;" title="{{ $member->name }}">{{ $member->name }}</span>
                        </div>
                    @endforeach
                </div>
                @php
                $currentUser = auth()->user();
                $isCreatorOrAdmin = false;
                if ($currentUser) {
                    $currentUserRoles = $currentUser->roles->pluck('name')->toArray();
                    if ($message->messageGroup->created_by === $currentUser->id || in_array('admin', $currentUserRoles)) {
                        $isCreatorOrAdmin = true;
                    }
                }
                @endphp
                @if ($isCreatorOrAdmin)
                    <div class="dropdown-divider my-1"></div>
                    <a class="dropdown-item f-11 px-2 py-1 text-dark-grey manage-group-members" data-group-id="{{ $message->messageGroup->id }}" href="javascript:;"><i class="fa fa-cog mr-1"></i> Manage Members</a>
                    <a class="dropdown-item f-11 px-2 py-1 text-red delete-message-group" data-group-id="{{ $message->messageGroup->id }}" href="javascript:;"><i class="fa fa-trash-alt mr-1"></i> Delete Group</a>
                @endif
            </div>
        </div>
    @endif
</div><!-- card end -->
