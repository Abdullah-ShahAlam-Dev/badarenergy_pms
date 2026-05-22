<?php

namespace App\Models;

use App\Traits\HasCompany;

class NotificationDelivery extends BaseModel
{
    use HasCompany;

    const EVENT_TASK_OVERDUE = 'task_overdue';
    const EVENT_AUTO_TASK_REMINDER = 'auto_task_reminder';
    const EVENT_TASK_REMINDER = 'task_reminder';

    protected $guarded = ['id'];
    
    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
