<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\SubTask\StoreSubTask;
use App\Models\PermissionType;
use App\Models\SubTask;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SubTaskController extends AccountBaseController
{

    /**
     * Show the form for editing a sub-task.
     */
    public function edit($id)
    {
        $this->subTask = SubTask::with(['files', 'task', 'task.users'])->findOrFail($id);

        // --- SUB-TASK ASSIGNEE LIST (Departmental Isolation & Project Scope) ---
        // RULE: Admins or users with 'assign_sub_tasks' == 'all' see all project members.
        // All other users (e.g. HODs) see ONLY their department members who are ALSO project members.
        $assignSubTaskPermission = user()->permission('assign_sub_tasks');
        $canAssignToAll = in_array('admin', user_roles()) || $assignSubTaskPermission == 'all';

        if ($canAssignToAll) {
            // Admins/ALL see all people who are added in the project
            if ($this->subTask->task->project_id) {
                // Return all project members
                $this->assignees = $this->subTask->task->project->projectMembers;
            } else {
                // Fallback for global tasks without a project
                $this->assignees = $this->subTask->task->users;
            }
        } else {
            // All non-admin users see employees from the sub-task's original department BUT only those in the project
            $targetDeptId = $this->subTask->department_id;
            if ($targetDeptId) {
                $query = User::join('employee_details', 'employee_details.user_id', '=', 'users.id')
                    ->where('employee_details.department_id', $targetDeptId)
                    ->where('users.status', 'active');
                
                // Restrict to project-related people only
                if ($this->subTask->task->project_id) {
                    $query->join('project_members', 'project_members.user_id', '=', 'users.id')
                          ->where('project_members.project_id', $this->subTask->task->project_id);
                }

                $this->assignees = $query->select('users.*')->distinct()->get();
            } else {
                // Sub-task has no department (Global) - empty list
                $this->assignees = collect([]);
            }
        }
        // --------------------------------------------------------

        return view('tasks.sub_tasks.edit', $this->data);
    }

    /**
     * Show sub-task detail.
     */
    public function show($id)
    {
        $this->subTask = SubTask::with(['files'])->findOrFail($id);
        return view('tasks.sub_tasks.detail', $this->data);
    }

    /**
     * Store a new sub-task.
     * RULES:
     * - Snapshot creator's department_id
     * - If an assignee is provided, validate they are in the same department (zero-trust)
     */
    public function store(StoreSubTask $request)
    {
        $this->addPermission = user()->permission('add_sub_tasks');
        $task = Task::findOrFail($request->task_id);
        $taskUsers = $task->users->pluck('id')->toArray();

        $ccUsers = $task->ccUsers->pluck('id')->toArray();
        $isCcUser = in_array(user()->id, $ccUsers) && !in_array(user()->id, $taskUsers) && $task->added_by != user()->id && !in_array('admin', user_roles());

        abort_403($isCcUser || !(
            $this->addPermission == 'all'
            || ($this->addPermission == 'added' && $task->added_by == user()->id)
            || ($this->addPermission == 'owned' && in_array(user()->id, $taskUsers))
            || ($this->addPermission == 'added' && (in_array(user()->id, $taskUsers) || $task->added_by == user()->id))
        ));

        // --- DEPARTMENT VALIDATION (Zero Trust) ---
        $creatorDeptId = optional(user()->employeeDetail)->department_id;

        if ($request->user_id) {
            $this->validateAssignee((int) $request->user_id, $creatorDeptId);
        }
        // ------------------------------------------

        $subTask = new SubTask();
        $subTask->title = $request->title;
        $subTask->task_id = $request->task_id;
        $subTask->description = trim_editor($request->description);

        if ($request->start_date != '' && $request->due_date != '') {
            $subTask->start_date = Carbon::createFromFormat($this->company->date_format, $request->start_date)->format('Y-m-d');
            $subTask->due_date   = Carbon::createFromFormat($this->company->date_format, $request->due_date)->format('Y-m-d');
        }

        $subTask->assigned_to  = $request->user_id ?: null;
        // Snapshot department at creation time — NEVER recalculate later
        $subTask->department_id = $creatorDeptId;

        $subTask->save();

        // AUTO-ADD ASSIGNEE TO PARENT TASK MEMBERS
        // So the sub-task appears on the employee's task dashboard.
        // syncWithoutDetaching ensures no duplicates and never removes existing members.
        if ($request->user_id) {
            $task->users()->syncWithoutDetaching([$request->user_id]);
        }

        $task = $subTask->task;
        $this->logTaskActivity($task->id, $this->user->id, 'subTaskCreateActivity', $task->board_column_id, $subTask->id);
        return Reply::successWithData(__('messages.recordSaved'), ['subTaskID' => $subTask->id]);
    }

    /**
     * Remove the specified sub-task.
     */
    public function destroy($id)
    {
        $subTask = SubTask::findOrFail($id);
        SubTask::destroy($id);

        $this->task = Task::with(['subtasks', 'subtasks.files'])->findOrFail($subTask->task_id);
        $view = view('tasks.sub_tasks.show', $this->data)->render();

        return Reply::successWithData(__('messages.deleteSuccess'), ['view' => $view]);
    }

    /**
     * Toggle sub-task completion status.
     */
    public function changeStatus(Request $request)
    {
        $subTask = SubTask::findOrFail($request->subTaskId);
        $subTask->status = $request->status;
        $subTask->save();

        $this->task = Task::with(['subtasks', 'subtasks.files'])->findOrFail($subTask->task_id);
        $this->logTaskActivity($this->task->id, user()->id, 'subTaskUpdateActivity', $this->task->board_column_id, $subTask->id);

        $view = view('tasks.sub_tasks.show', $this->data)->render();

        return Reply::successWithData('messages.updateSuccess', ['view' => $view]);
    }

    /**
     * Update an existing sub-task.
     * RULES:
     * - Preserve original department_id (NEVER recalculate)
     * - If assignee changes, re-validate against original sub-task department
     */
    public function update(StoreSubTask $request, $id)
    {
        $subTask = SubTask::findOrFail($id);

        // --- DEPARTMENT VALIDATION (Zero Trust) ---
        // Use the sub-task's original department — NEVER override it
        if ($request->user_id) {
            $this->validateAssignee((int) $request->user_id, $subTask->department_id);
        }
        // ------------------------------------------

        $subTask->title       = $request->title;
        $subTask->description = trim_editor($request->description);
        $subTask->start_date  = ($request->start_date != '') ? Carbon::createFromFormat($this->company->date_format, $request->start_date)->format('Y-m-d') : null;
        $subTask->due_date    = ($request->due_date != '') ? Carbon::createFromFormat($this->company->date_format, $request->due_date)->format('Y-m-d') : null;
        $subTask->assigned_to = $request->user_id ?: null;
        // department_id intentionally NOT updated — preserve original snapshot (Rule 5)

        $subTask->save();

        // AUTO-ADD NEW ASSIGNEE TO PARENT TASK MEMBERS
        // Ensures the newly assigned employee can see the parent task on their dashboard.
        if ($request->user_id) {
            $task = $subTask->task;
            $task->users()->syncWithoutDetaching([$request->user_id]);
        }

        $task = $subTask->task;
        $this->logTaskActivity($task->id, $this->user->id, 'subTaskUpdateActivity', $task->board_column_id, $subTask->id);

        $this->task = Task::with(['subtasks', 'subtasks.files'])->findOrFail($subTask->task_id);
        $view = view('tasks.sub_tasks.show', $this->data)->render();

        return Reply::successWithData(__('messages.updateSuccess'), ['view' => $view]);
    }

    /**
     * Validate that the assignee exists, has employeeDetail, and belongs to the same department.
     * This is a hard backend validation — frontend cannot bypass this (Rule 6).
     *
     * @param int $assigneeId
     * @param int|null $requiredDeptId  The sub-task's fixed department
     */
    private function validateAssignee(int $assigneeId, ?int $requiredDeptId): void
    {
        $isAdmin = in_array('admin', user_roles());
        $canAssignToOthers = $isAdmin || user()->permission('assign_sub_tasks') == 'all';

        // DEAD-END RULE: If user cannot assign sub-tasks to others, they can only assign to themselves.
        if (!$canAssignToOthers && $assigneeId !== user()->id) {
            abort_403(true);
        }

        // Admin or users with 'all' permission bypass department restrictions
        if ($canAssignToOthers) {
            return;
        }

        $assignee = User::find($assigneeId);

        abort_403(is_null($assignee));
        abort_403(is_null($assignee->employeeDetail));

        // If the sub-task has no department (NULL = global), only admin/ALL should be assigning
        // Non-admin trying to assign to a Global sub-task should be denied
        if (is_null($requiredDeptId)) {
            abort_403(true);
        }

        // Hard check: assignee must be in the exact same department
        abort_403(
            $assignee->employeeDetail->department_id !== $requiredDeptId,
            __('messages.permissionDenied')
        );
    }

}
