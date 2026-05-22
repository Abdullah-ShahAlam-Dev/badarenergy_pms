<?php

namespace App\Channels;

use App\Jobs\SendWhatsAppJob;
use App\Models\NotificationDelivery;
use App\Models\NotificationIntegration;
use App\Models\NotificationTemplate;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;

class WhatsAppChannel
{
    public function send($notifiable, Notification $notification)
    {
        if (!method_exists($notification, 'getEventName')) {
            return;
        }

        $eventName = $notification->getEventName();
        
        $to = method_exists($notifiable, 'routeNotificationForWhatsApp') 
            ? $notifiable->routeNotificationForWhatsApp($notification) 
            : null;

        if (!$to) {
            return;
        }

        // Using withoutGlobalScopes to ensure queue workers/commands can access company data
        $companyId = $notification->company_id ?? ($notifiable->company_id ?? null);
        $taskId = property_exists($notification, 'task') ? $notification->task->id : null;

        if (!$companyId || !$taskId) {
            return;
        }

        $setting = NotificationIntegration::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('status', 'active')
            ->first();

        if (!$setting) {
            return;
        }

        $template = NotificationTemplate::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('event_name', $eventName)
            ->where('is_active', true)
            ->first();

        if (!$template) {
            return;
        }

        // Generate Idempotency Key (Event Cycle Key)
        // Format: task_id + date hash + event
        $task = $notification->task;
        $dateHash = $task->due_date ? md5($task->due_date->format('Y-m-d')) : 'nodate';
        $eventCycleKey = "{$task->id}_{$dateHash}_{$eventName}";

        try {
            // Create delivery log and lock idempotency via unique index
            $delivery = NotificationDelivery::create([
                'company_id' => $companyId,
                'task_id' => $taskId,
                'event_name' => $eventName,
                'channel' => 'whatsapp',
                'recipient' => $to,
                'event_cycle_key' => $eventCycleKey,
                'status' => 'pending'
            ]);

            // Resolve Parameter Values dynamically
            $placeholderReplacements = [
                '{employee_name}' => $notifiable->name,
                '{task_name}' => $task->heading,
                '{due_date}' => $task->due_date ? $task->due_date->format('Y-m-d') : 'None',
                '{project_name}' => $task->project ? $task->project->project_name : 'No Project',
                '{company_name}' => $task->company ? $task->company->company_name : 'Worksuite'
            ];

            $parameters = [];
            foreach ($template->parameter_mappings ?? [] as $placeholder) {
                $parameters[] = $placeholderReplacements[$placeholder] ?? $placeholder;
            }

            // Dispatch isolated WhatsApp job with specific retry/backoff rules
            SendWhatsAppJob::dispatch(
                $delivery->id,
                $setting->id,
                $to,
                $template->template_id,
                $template->language_code,
                $parameters
            );

        } catch (QueryException $e) {
            // Catch Integrity Constraint Violation (Duplicate Entry)
            if ($e->getCode() == 23000) {
                Log::info("WhatsApp delivery prevented: Duplicate event cycle key {$eventCycleKey}");
                return;
            }
            throw $e;
        }
    }
}
