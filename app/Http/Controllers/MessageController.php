<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\ChatStoreRequest;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use App\Models\UserChat;
use App\Models\MessageGroup;
use Client;
use Illuminate\Support\Facades\Session;

class MessageController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.messages';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('messages', $this->user->modules));
            return $next($request);
        });
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        session()->forget('message_setting');
        session()->forget('pusher_settings');

        abort_403(message_setting()->allow_client_admin == 'no' && message_setting()->allow_client_employee == 'no' && in_array('client', user_roles()));

        if (request()->ajax() && request()->has('term')) {
            $term = (request('term') != '') ? request('term') : null;
            $this->userLists = $this->getMixedUserList($term);

            $userList = view('messages.user_list', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'userList' => $userList]);
        }

        if(request()->clientId) {
            $this->client = User::findOrFail(request()->clientId);
        }

        $this->userLists = $this->getMixedUserList();

        $this->employees = User::allEmployees(null, true, 'all');

        $userData = [];

        $usersData = $this->employees;

        foreach ($usersData as $user) {

            $url = route('employees.show', [$user->id]);

            $userData[] = ['id' => $user->id, 'value' => $user->name, 'image' => $user->image_url, 'link' => $url];

        }

        $this->userData = $userData;

        // To show particular user's chat using it's user_id
        Session::flash('message_user_id', request()->user);

        return view('messages.index', $this->data);
    }

    /**
     * XXXXXXXXXXXx`
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->messageSetting = message_setting();
        $this->project_id = Project::where('client_id', user()->id)->pluck('id');
        $this->employee_project_id = ProjectMember::where('user_id', user()->id)->pluck('project_id');
        $this->employee_user_id = ProjectMember::whereIn('project_id', $this->employee_project_id)->pluck('user_id');
        $this->employee_client_id = Project::whereIn('id', $this->employee_project_id )->pluck('client_id');

        $this->user_id = ProjectMember::whereIn('project_id', $this->project_id)->pluck('user_id');

        if (!in_array('client', user_roles())) {
            $this->employees = User::allEmployees($this->user->id, true, 'all');

            if (in_array('admin', user_roles())) {
                $this->clients = User::allClients();

            } elseif (($this->messageSetting->allow_client_employee == 'yes' && $this->messageSetting->restrict_client == 'no')) {
                $this->clients = User::allClients();

            } else if($this->messageSetting->allow_client_employee == 'yes' && $this->messageSetting->restrict_client == 'yes') {
                $this->clients = User::whereIn('id', $this->employee_client_id)->get();
            }
        }

        // This will return true if message button from projects overview button is clicked
        if(request()->clientId) {
            $this->clientId = request()->clientId;
            $this->client = User::findOrFail(request()->clientId);
        }

        if ($this->messageSetting->allow_client_employee == 'yes' && $this->messageSetting->restrict_client == 'no' && in_array('client', user_roles())) {
            $this->employees = User::allEmployees();
        }
        else if($this->messageSetting->allow_client_employee == 'yes' && $this->messageSetting->restrict_client == 'yes' && in_array('client', user_roles()))
        {
            $this->employees = User::whereIn('id', $this->user_id)->get();
        }
        else if ($this->messageSetting->allow_client_admin == 'yes' && in_array('client', user_roles())) {
            $this->employees = User::allAdmins($this->messageSetting->company->id);
        }

        $this->employees = User::allEmployees(null, true, 'all');

        $userData = [];

        $usersData = $this->employees;

        foreach ($usersData as $user) {

            $url = route('employees.show', [$user->id]);

            $userData[] = ['id' => $user->id, 'value' => $user->name, 'image' => $user->image_url, 'link' => $url];

        }

        $this->userData = $userData;

        return view('messages.create', $this->data);
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function store(ChatStoreRequest $request)
    {
        if ($request->has('message_group_id') && $request->message_group_id != '') {
            $groupId = $request->message_group_id;
            $group = MessageGroup::findOrFail($groupId);
            abort_403(!$group->members->contains(user()->id));

            $message = new UserChat();
            $message->message         = $request->message;
            $message->user_one        = user()->id;
            $message->user_id         = user()->id;
            $message->from            = user()->id;
            $message->to              = null;
            $message->message_group_id = $groupId;
            $message->notification_sent = 0;
            $message->save();

            $this->userLists = $this->getMixedUserList();
            $userList = view('messages.user_list', $this->data)->render();

            $this->chatDetails = UserChat::with('fromUser', 'toUser', 'files')
                ->where('message_group_id', $groupId)
                ->orderBy('created_at', 'asc')->get();
            $messageList = view('messages.message_list', $this->data)->render();

            return Reply::dataOnly([
                'user_list' => $userList,
                'message_list' => $messageList,
                'message_id' => $message->id,
                'message_group_id' => $groupId,
                'userName' => $group->group_name
            ]);
        } else {
            if ($request->user_type == 'client') {
                $receiverID = $request->client_id;
            }
            else {
                $receiverID = $request->user_id;
            }

            $message = new UserChat();
            $message->message         = $request->message;
            $message->user_one        = user()->id;
            $message->user_id         = $receiverID;
            $message->from            = user()->id;
            $message->to              = $receiverID;
            $message->notification_sent = 0;
            $message->save();

            $this->userLists = $this->getMixedUserList();
            $userList = view('messages.user_list', $this->data)->render();

            $this->chatDetails = UserChat::chatDetail($receiverID, user()->id);
            $messageList = view('messages.message_list', $this->data)->render();

            return Reply::dataOnly(['user_list' => $userList, 'message_list' => $messageList, 'message_id' => $message->id, 'receiver_id' => $receiverID, 'userName' => $message->toUser->name]);
        }
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (request()->type == 'group') {
            $group = MessageGroup::findOrFail($id);
            abort_403(!$group->members->contains(user()->id));

            $this->chatDetails = UserChat::with('fromUser', 'toUser', 'files')
                ->where('message_group_id', $id)
                ->orderBy('created_at', 'asc')->get();

            $this->userId = null;
            $this->groupId = $id;

            $view = view('messages.message_list', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $view, 'unreadMessages' => 0, 'id' => null, 'groupId' => $id]);
        } else {
            $this->chatDetails = UserChat::chatDetail($id, user()->id);

            // Mark messages read
            $updateData = ['message_seen' => 'yes'];
            UserChat::messageSeenUpdate($this->user->id, $id, $updateData);
            $this->unreadMessage = (request()->unreadMessageCount > 0) ? 0 : 1;
            $this->userId = $id;
            $this->groupId = null;

            $view = view('messages.message_list', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $view, 'unreadMessages' => $this->unreadMessage, 'id' => $this->userId]);
        }
    }

    public function destroy($id)
    {
        $userChats = UserChat::findOrFail($id);

        // Authorization check: Only the sender of the message or an administrator can delete it.
        $currentUser = auth()->user();
        if ($userChats->from !== $currentUser->id && !in_array('admin', $currentUser->roles->pluck('name')->toArray())) {
            abort_403(true);
        }

        // Delete all attached files from database and storage
        foreach ($userChats->files as $file) {
            \App\Helper\Files::deleteFile($file->hashname, 'message-files/' . $file->users_chat_id);
            $file->delete();
        }

        // WhatsApp-like delete: update the message content instead of deleting the DB record completely
        $timestamp = now()->timezone(company()->timezone)->translatedFormat(company()->time_format);
        $userChats->message = '[deleted] This message was deleted by ' . $userChats->fromUser->name . ' at ' . $timestamp;
        $userChats->save();

        // Broadcast the update in real-time to trigger instant visual update for all recipients
        event(new \App\Events\NewChatEvent($userChats));

        $groupId = $userChats->message_group_id;

        if ($groupId) {
            $chatDetails = UserChat::with('fromUser', 'toUser', 'files')
                ->where('message_group_id', $groupId)
                ->orderBy('created_at', 'asc')->get();
        } else {
            $chatDetails = UserChat::chatDetail($userChats->from, $userChats->to);
        }

        return Reply::successWithData(__('messages.deleteSuccess'), ['chat_details' => $chatDetails]);
    }

    public function fetchUserListView()
    {
        $this->userLists = $this->getMixedUserList();

        // To show particular user's chat using it's user_id
        Session::flash('message_user_id', request()->user);
        $userList = view('messages.user_list', $this->data)->render();

        return Reply::dataOnly(['user_list' => $userList]);
    }

    public function fetchUserMessages($receiverID)
    {
        if (request()->type == 'group') {
            $group = MessageGroup::findOrFail($receiverID);
            abort_403(!$group->members->contains(user()->id));

            $this->chatDetails = UserChat::with('fromUser', 'toUser', 'files')
                ->where('message_group_id', $receiverID)
                ->orderBy('created_at', 'asc')->get();
        } else {
            $this->chatDetails = UserChat::chatDetail($receiverID, user()->id);
        }
        $messageList = view('messages.message_list', $this->data)->render();

        return Reply::dataOnly(['message_list' => $messageList]);
    }

    public function checkNewMessages()
    {
        $newMessageCount = UserChat::whereNull('message_group_id')->where('to', user()->id)->where('message_seen', 'no')->where('notification_sent', 0)->count();

        UserChat::whereNull('message_group_id')->where('to', user()->id)->update(['notification_sent' => 1]); // Mark notification as sent

        return Reply::dataOnly(['new_message_count' => $newMessageCount]);
    }

    private function getMixedUserList($term = null)
    {
        // 1-to-1 latest chats
        $userLists = UserChat::userListLatest(user()->id, $term);
        $oneToOneMessageIds = collect($userLists)->pluck('id');

        // Search groups by name
        $groupQuery = user()->messageGroups();
        if ($term) {
            $groupQuery->where('group_name', 'like', '%' . $term . '%');
        }
        $myGroups = $groupQuery->pluck('message_groups.id');

        $groupLatestMessageIds = UserChat::whereIn('message_group_id', $myGroups)
            ->groupBy('message_group_id')
            ->selectRaw('MAX(id) as id')
            ->pluck('id');

        $allMessageIds = $oneToOneMessageIds->merge($groupLatestMessageIds);

        return UserChat::with(['fromUser' => function ($q) {
            $q->withCount(['unreadMessages']);
        }, 'toUser' => function ($q) {
            $q->withCount(['unreadMessages']);
        }, 'messageGroup'])
        ->whereIn('id', $allMessageIds)->orderBy('id', 'desc')->get();
    }

}
