<?php

namespace App\Models;

use App\Models\PermissionType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\SubTask
 *
 * @property int $id
 * @property int $task_id
 * @property string $title
 * @property \Illuminate\Support\Carbon|null $due_date
 * @property string|null $start_date
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property int|null $department_id
 * @property-read mixed $icon
 * @property-read \App\Models\Task $task
 * @method static \Illuminate\Database\Eloquent\Builder|SubTask newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SubTask newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SubTask query()
 * @method static \Illuminate\Database\Eloquent\Builder|SubTask whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubTask whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubTask whereDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubTask whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubTask whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubTask whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubTask whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubTask whereTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubTask whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubTask whereUpdatedAt($value)
 * @property string|null $description
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\SubTaskFile[] $files
 * @property-read int|null $files_count
 * @method static \Illuminate\Database\Eloquent\Builder|SubTask whereDescription($value)
 * @property int|null $assigned_to
 * @property-read \App\Models\User|null $assignedTo
 * @method static \Illuminate\Database\Eloquent\Builder|SubTask whereAssignedTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubTask departmentScope()
 * @mixin \Eloquent
 */
class SubTask extends BaseModel
{

    protected $casts = [
        'start_date' => 'datetime',
        'due_date'   => 'datetime',
    ];

    protected $with = ['assignedTo'];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function files(): HasMany
    {
        return $this->hasMany(SubTaskFile::class, 'sub_task_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'department_id');
    }

    /**
     * Department-scoped filter — LOCAL SCOPE ONLY (no Global Scope).
     *
     * Hierarchy (must be applied in this order):
     *  1. Admin → full bypass
     *  2. PermissionType::ALL → full bypass
     *  3. Others → grouped department filter
     *
     * NULL department_id = Global record: visible ONLY to Admin / ALL.
     *
     * Call: SubTask::departmentScope()->where(...) in controllers only.
     */
    public function scopeDepartmentScope(Builder $query): Builder
    {
        $currentUser = user();

        // Rule 1: Admin gets full bypass (uses system-native pattern)
        if (in_array('admin', user_roles())) {
            return $query;
        }

        // Rule 2: PermissionType::ALL gets full bypass (includes NULL/Global records)
        if ($currentUser->permissionTypeId('view_sub_tasks') == PermissionType::ALL) {
            return $query;
        }

        // Rule 3: All other users need department filtering
        $employeeDetail = $currentUser->employeeDetail;

        // Rule 7: No silent failure — if user has no department, deny explicitly
        if (is_null($employeeDetail) || is_null($employeeDetail->department_id)) {
            // Return a query that always yields zero rows (explicit deny)
            return $query->whereRaw('1 = 0');
        }

        $departmentId = $employeeDetail->department_id;

        // Rule 3 + Rule 4: Grouped filter — department match only.
        // NULL records are Global — only Admin/ALL can see them (handled in bypasses above).
        return $query->where(function (Builder $q) use ($departmentId) {
            $q->where('department_id', $departmentId);
            // NOTE: orWhereNull intentionally NOT included here.
            // NULL = Global, only Admin/ALL see those.
        });
    }
}
