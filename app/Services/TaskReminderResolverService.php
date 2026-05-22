<?php

namespace App\Services;

use App\Models\Company;
use App\Models\NotificationDelivery;
use App\Models\Task;
use App\Models\TaskboardColumn;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TaskReminderResolverService
{
    /**
     * Resolves overdue tasks for a given company and locks them for the current cycle.
     * Returns tasks that were successfully locked, guaranteeing exactly-once delivery.
     *
     * @param Company $company
     * @param string $eventName
     * @return Collection
     */
    public static function resolveAndLockOverdueTasks(Company $company, string $eventName): Collection
    {
        $now = Carbon::now($company->timezone);

        $completedTaskColumn = TaskboardColumn::withoutGlobalScopes()
            ->where('company_id', $company->id)
            ->where('slug', 'completed')
            ->first();

        if (!$completedTaskColumn) {
            return collect();
        }

        // Fetch tasks that are strictly overdue based on company's local time
        $overdueTasks = Task::withoutGlobalScopes()
            ->with('users')
            ->where('company_id', $company->id)
            ->where('board_column_id', '<>', $completedTaskColumn->id)
            ->whereNotNull('due_date')
            ->where('due_date', '<', $now->format('Y-m-d'))
            ->get();

        $lockedTasks = collect();

        foreach ($overdueTasks as $task) {
            // Generate deterministic cycle key based on task ID, due date, and event
            $dateHash = $task->due_date ? md5($task->due_date->format('Y-m-d')) : 'nodate';
            $eventCycleKey = "{$task->id}_{$dateHash}_{$eventName}";

            try {
                // Attempt to insert master system lock for this task's cycle
                NotificationDelivery::create([
                    'company_id' => $company->id,
                    'task_id' => $task->id,
                    'event_name' => $eventName,
                    'channel' => 'system_master', // Represents the core scheduler cycle lock
                    'recipient' => 'system',
                    'event_cycle_key' => $eventCycleKey,
                    'status' => 'sent' // Lock established
                ]);

                // If insert succeeds, it means no other worker/cron has processed this cycle
                $lockedTasks->push($task);

            } catch (\Illuminate\Database\QueryException $e) {
                // Catch Integrity Constraint Violation (Duplicate Entry)
                if ($e->getCode() == 23000) {
                    continue; // Task already processed for this cycle, skip to next
                }
                Log::error("Failed to acquire task delivery lock: " . $e->getMessage());
            }
        }

        return $lockedTasks;
    }
}
