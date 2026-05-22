<?php

namespace Tests\Feature;

use App\Jobs\SendWhatsAppJob;
use App\Models\Company;
use App\Models\NotificationDelivery;
use App\Models\NotificationIntegration;
use App\Models\NotificationTemplate;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskboardColumn;
use App\Models\User;
use App\Notifications\TaskReminder;
use App\Services\NotificationChannelResolver;
use App\Services\TaskReminderResolverService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class WhatsAppNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected $company;
    protected $employee;
    protected $project;
    protected $incompleteColumn;
    protected $completedColumn;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create a test company
        $this->company = Company::create([
            'company_name' => 'Badar Expo Solutions',
            'app_name' => 'Badar Expo Solutions',
            'company_email' => 'company@email.com',
            'company_phone' => '1234567891',
            'address' => 'Test Address',
            'timezone' => 'Asia/Karachi',
        ]);

        // 2. Create a test user (employee)
        $this->employee = User::create([
            'company_id' => $this->company->id,
            'name' => 'John Doe',
            'email' => 'john@badarexpo.com',
            'password' => bcrypt('123456'),
            'gender' => 'male',
            'mobile' => '3001234567',
            'country_phonecode' => '92',
        ]);

        // 3. Create taskboard columns
        $this->incompleteColumn = TaskboardColumn::create([
            'company_id' => $this->company->id,
            'column_name' => 'Incomplete',
            'slug' => 'incomplete',
            'label_color' => '#3498db',
            'priority' => 1,
        ]);

        $this->completedColumn = TaskboardColumn::create([
            'company_id' => $this->company->id,
            'column_name' => 'Completed',
            'slug' => 'completed',
            'label_color' => '#2ecc71',
            'priority' => 2,
        ]);

        // 4. Create a test project
        $this->project = Project::create([
            'company_id' => $this->company->id,
            'project_name' => 'Test PMS Project',
            'start_date' => now()->subDays(10)->format('Y-m-d'),
            'deadline' => now()->addDays(10)->format('Y-m-d'),
            'calculate_task_progress' => 'false',
        ]);

        // 5. Setup WhatsApp integration
        NotificationIntegration::create([
            'company_id' => $this->company->id,
            'provider' => 'meta_cloud',
            'status' => 'active',
            'credentials' => [
                'access_token' => 'test-meta-access-token',
                'phone_number_id' => '1029384756',
            ],
        ]);

        // 6. Setup WABA template
        NotificationTemplate::create([
            'company_id' => $this->company->id,
            'event_name' => NotificationDelivery::EVENT_TASK_OVERDUE,
            'template_id' => 'task_overdue_alert',
            'language_code' => 'en',
            'parameter_mappings' => ['{employee_name}', '{task_name}', '{due_date}'],
            'is_active' => true,
        ]);
    }

    /** @test */
    public function it_resolves_and_locks_overdue_tasks()
    {
        // Create an overdue incomplete task
        $task = Task::create([
            'company_id' => $this->company->id,
            'heading' => 'Urgent Overdue Task',
            'due_date' => now($this->company->timezone)->subDays(2)->format('Y-m-d H:i:s'),
            'board_column_id' => $this->incompleteColumn->id,
            'project_id' => $this->project->id,
        ]);

        $task->users()->attach($this->employee->id);

        // Resolve and lock overdue tasks (should return the task ID as unresolved/needs notification)
        $tasksToNotify = TaskReminderResolverService::resolveAndLockOverdueTasks($this->company, NotificationDelivery::EVENT_TASK_REMINDER);

        $this->assertCount(1, $tasksToNotify);
        $this->assertEquals($task->id, $tasksToNotify->first()->id);

        // Running resolver a second time should return empty array because of lock
        $tasksToNotifySecond = TaskReminderResolverService::resolveAndLockOverdueTasks($this->company, NotificationDelivery::EVENT_TASK_REMINDER);
        $this->assertCount(0, $tasksToNotifySecond);
    }

    /** @test */
    public function it_does_not_resolve_completed_tasks_as_overdue()
    {
        // Create an overdue but completed task
        $task = Task::create([
            'company_id' => $this->company->id,
            'heading' => 'Finished Overdue Task',
            'due_date' => now($this->company->timezone)->subDays(2)->format('Y-m-d H:i:s'),
            'board_column_id' => $this->completedColumn->id,
            'project_id' => $this->project->id,
        ]);

        $task->users()->attach($this->employee->id);

        $tasksToNotify = TaskReminderResolverService::resolveAndLockOverdueTasks($this->company, NotificationDelivery::EVENT_TASK_REMINDER);

        $this->assertCount(0, $tasksToNotify);
    }

    /** @test */
    public function it_does_not_resolve_future_tasks_as_overdue()
    {
        // Create a future incomplete task
        $task = Task::create([
            'company_id' => $this->company->id,
            'heading' => 'Future Task',
            'due_date' => now($this->company->timezone)->addDays(2)->format('Y-m-d H:i:s'),
            'board_column_id' => $this->incompleteColumn->id,
            'project_id' => $this->project->id,
        ]);

        $task->users()->attach($this->employee->id);

        $tasksToNotify = TaskReminderResolverService::resolveAndLockOverdueTasks($this->company, NotificationDelivery::EVENT_TASK_REMINDER);

        $this->assertCount(0, $tasksToNotify);
    }

    /** @test */
    public function it_caches_channel_resolver_status()
    {
        $resolver = new NotificationChannelResolver();

        // First call should set cache
        $this->assertTrue($resolver->isWhatsAppEnabled($this->company->id, NotificationDelivery::EVENT_TASK_OVERDUE));

        // Let's modify DB directly to inactive
        NotificationIntegration::where('company_id', $this->company->id)->update(['status' => 'inactive']);

        // Since it is cached for 60 mins, it should still return true
        $this->assertTrue($resolver->isWhatsAppEnabled($this->company->id, NotificationDelivery::EVENT_TASK_OVERDUE));

        // Clear cache and check again
        Cache::forget("whatsapp_enabled_company_" . $this->company->id . "_" . NotificationDelivery::EVENT_TASK_OVERDUE);
        $this->assertFalse($resolver->isWhatsAppEnabled($this->company->id, NotificationDelivery::EVENT_TASK_OVERDUE));
    }

    /** @test */
    public function it_dispatches_queued_whatsapp_job_when_notified()
    {
        Queue::fake();

        $task = Task::create([
            'company_id' => $this->company->id,
            'heading' => 'Task for WhatsApp reminder',
            'due_date' => now($this->company->timezone)->subDays(1)->format('Y-m-d H:i:s'),
            'board_column_id' => $this->incompleteColumn->id,
            'project_id' => $this->project->id,
        ]);
        $task->users()->attach($this->employee->id);

        // Notify user via TaskReminder notification (which triggers WhatsApp channel)
        $this->employee->notify(new TaskReminder($task));

        // Queue should contain SendWhatsAppJob
        Queue::assertPushed(SendWhatsAppJob::class, function ($job) {
            return $job->queue === 'notifications_whatsapp';
        });
    }

    /** @test */
    public function it_respects_multi_tenant_isolation_on_overdue_tasks()
    {
        // Create second company
        $company2 = Company::create([
            'company_name' => 'Second Tenant',
            'app_name' => 'Second Tenant',
            'company_email' => 'company2@email.com',
            'company_phone' => '1234567892',
            'address' => 'Test Address 2',
            'timezone' => 'Asia/Karachi',
        ]);

        $task1 = Task::create([
            'company_id' => $this->company->id,
            'heading' => 'Company 1 Task',
            'due_date' => now($this->company->timezone)->subDays(1)->format('Y-m-d H:i:s'),
            'board_column_id' => $this->incompleteColumn->id,
            'project_id' => $this->project->id,
        ]);

        // Creating incomplete column for company2
        $incompleteColumn2 = TaskboardColumn::create([
            'company_id' => $company2->id,
            'column_name' => 'Incomplete',
            'slug' => 'incomplete',
            'label_color' => '#3498db',
            'priority' => 1,
        ]);

        // Creating project for company2
        $project2 = Project::create([
            'company_id' => $company2->id,
            'project_name' => 'Test PMS Project 2',
            'start_date' => now()->subDays(10)->format('Y-m-d'),
            'deadline' => now()->addDays(10)->format('Y-m-d'),
            'calculate_task_progress' => 'false',
        ]);

        $task2 = Task::create([
            'company_id' => $company2->id,
            'heading' => 'Company 2 Task',
            'due_date' => now($company2->timezone)->subDays(1)->format('Y-m-d H:i:s'),
            'board_column_id' => $incompleteColumn2->id,
            'project_id' => $project2->id,
        ]);

        // Resolving for Company 1 should only return Company 1's task
        $tasksToNotify = TaskReminderResolverService::resolveAndLockOverdueTasks($this->company, NotificationDelivery::EVENT_TASK_REMINDER);

        $this->assertCount(1, $tasksToNotify);
        $this->assertEquals($task1->id, $tasksToNotify->first()->id);
    }
}
