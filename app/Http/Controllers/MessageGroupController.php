<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\MessageGroup;
use App\Models\MessageGroupMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MessageGroupController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.messages';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('messages', $this->user->modules));

            // Check if group creation is restricted to specific users
            $messageSettings = message_setting();
            if ($messageSettings->allow_create_group) {
                $allowedUsers = $messageSettings->allow_create_group;
                if (is_string($allowedUsers)) {
                    $allowedUsers = json_decode($allowedUsers, true);
                }
                if (is_array($allowedUsers) && count($allowedUsers) > 0) {
                    $allowedUserIds = array_map('strval', $allowedUsers);
                    $currentUser = auth()->user();
                    if ($currentUser) {
                        $currentUserRoles = $currentUser->roles->pluck('name')->toArray();
                        if (!in_array((string)$currentUser->id, $allowedUserIds) && !in_array('admin', $currentUserRoles)) {
                            abort_403(true);
                        }
                    }
                }
            }

            return $next($request);
        });
    }

    /**
     * Show the form for creating a new message group.
     */
    public function create()
    {
        $this->employees = User::allEmployees(user()->id, true, 'all');

        return view('messages.create_group', $this->data);
    }

    /**
     * Store a newly created message group in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'group_name' => 'required|max:255',
            'user_ids' => 'required|array|min:1',
        ], [
            'group_name.required' => 'Group title is mandatory.',
            'user_ids.required' => 'Please select at least one member to join the group.',
            'user_ids.min' => 'Please select at least one member to join the group.',
        ]);

        DB::beginTransaction();

        try {
            // Create group
            $group = new MessageGroup();
            $group->group_name = $request->group_name;
            $group->created_by = user()->id;
            $group->company_id = company() ? company()->id : null;
            $group->save();

            // Creator automatically becomes member
            $members = [user()->id];
            
            // Add selected members (ignoring duplicates and the creator)
            foreach ($request->user_ids as $userId) {
                if ($userId != user()->id && !in_array($userId, $members)) {
                    $members[] = $userId;
                }
            }

            // Sync members
            $group->members()->sync($members);

            // Create initial group message
            $message = new \App\Models\UserChat();
            $message->message         = 'Group created by ' . user()->name;
            $message->user_one        = user()->id;
            $message->user_id         = user()->id;
            $message->from            = user()->id;
            $message->to              = null;
            $message->message_group_id = $group->id;
            $message->notification_sent = 1;
            $message->save();

            DB::commit();

            // We can return a success reply
            return Reply::success('Group created successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return Reply::error('Error creating group: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the group members.
     */
    public function edit($id)
    {
        $this->group = MessageGroup::findOrFail($id);

        // Authorization check: Only the group creator or an administrator can edit the group
        $currentUser = auth()->user();
        if ($this->group->created_by !== $currentUser->id && !in_array('admin', $currentUser->roles->pluck('name')->toArray())) {
            if (request()->ajax()) {
                return view('messages.unauthorized_group', $this->data);
            }
            abort_403(true);
        }

        $this->employees = User::allEmployees(user()->id, true, 'all');
        $this->currentMemberIds = $this->group->members->pluck('id')->toArray();

        return view('messages.edit_group', $this->data);
    }

    /**
     * Update the group members in storage.
     */
    public function update(Request $request, $id)
    {
        $group = MessageGroup::findOrFail($id);

        // Authorization check: Only the group creator or an administrator can edit the group
        $currentUser = auth()->user();
        if ($group->created_by !== $currentUser->id && !in_array('admin', $currentUser->roles->pluck('name')->toArray())) {
            abort_403(true);
        }

        $request->validate([
            'user_ids' => 'required|array|min:1',
        ], [
            'user_ids.required' => 'Please select at least one member to join the group.',
            'user_ids.min' => 'Please select at least one member to join the group.',
        ]);

        DB::beginTransaction();

        try {
            // Group creator must remain in the group
            $members = [$group->created_by];

            // Add selected members (ignoring duplicates and the creator)
            foreach ($request->user_ids as $userId) {
                if ($userId != $group->created_by && !in_array($userId, $members)) {
                    $members[] = $userId;
                }
            }

            // Sync members
            $group->members()->sync($members);

            // Create a system message in the group chat to inform everyone
            $message = new \App\Models\UserChat();
            $message->message         = 'Group members updated by ' . user()->name;
            $message->user_one        = user()->id;
            $message->user_id         = user()->id;
            $message->from            = user()->id;
            $message->to              = null;
            $message->message_group_id = $group->id;
            $message->notification_sent = 1;
            $message->save();

            DB::commit();

            return Reply::success('Group members updated successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return Reply::error('Error updating group: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified message group from storage.
     */
    public function destroy($id)
    {
        $group = MessageGroup::findOrFail($id);

        // Authorization check: Only the group creator or an administrator can delete the group
        $currentUser = auth()->user();
        if ($group->created_by !== $currentUser->id && !in_array('admin', $currentUser->roles->pluck('name')->toArray())) {
            abort_403(true);
        }

        DB::beginTransaction();

        try {
            // 1. Find all messages in the group and delete their file attachments from storage/db
            $messages = \App\Models\UserChat::where('message_group_id', $group->id)->get();
            foreach ($messages as $msg) {
                foreach ($msg->files as $file) {
                    \App\Helper\Files::deleteFile($file->hashname, 'message-files/' . $file->users_chat_id);
                    $file->delete();
                }
                $msg->delete();
            }

            // 2. Detach all members
            $group->members()->detach();

            // 3. Delete the group itself
            $group->delete();

            DB::commit();

            return Reply::success('Group deleted successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return Reply::error('Error deleting group: ' . $e->getMessage());
        }
    }
}
