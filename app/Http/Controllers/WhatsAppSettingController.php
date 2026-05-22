<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\NotificationDelivery;
use App\Models\NotificationIntegration;
use App\Models\NotificationTemplate;
use App\Models\User;
use App\Services\WhatsApp\WhatsAppManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class WhatsAppSettingController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'WhatsApp Settings';
        $this->activeSettingMenu = 'notification_settings';
        $this->middleware(function ($request, $next) {
            abort_403(user()->permission('manage_notification_setting') !== 'all');

            return $next($request);
        });
    }

    /**
     * Save or update WhatsApp integration settings.
     */
    public function update(Request $request, $id)
    {
        $rules = [
            'provider' => 'required|in:meta_cloud,twilio',
        ];

        // Dynamic credential validation based on selected provider
        if ($request->provider === 'meta_cloud') {
            $rules['credentials.access_token'] = 'required_if:whatsapp_status,active|string';
            $rules['credentials.phone_number_id'] = 'required_if:whatsapp_status,active|string';
        } elseif ($request->provider === 'twilio') {
            $rules['credentials.sid'] = 'required_if:whatsapp_status,active|string';
            $rules['credentials.auth_token'] = 'required_if:whatsapp_status,active|string';
            $rules['credentials.sender'] = 'required_if:whatsapp_status,active|string';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return Reply::error($validator->errors()->first());
        }

        $setting = NotificationIntegration::findOrFail($id);

        // Verify ownership
        abort_403($setting->company_id !== company()->id);

        $setting->status = $request->whatsapp_status ? 'active' : 'inactive';
        $setting->provider = $request->provider;
        $setting->credentials = $request->credentials;
        $setting->save();

        // Update template mapping if provided
        if ($request->has('template_id')) {
            $template = NotificationTemplate::firstOrCreate(
                ['company_id' => company()->id, 'event_name' => NotificationDelivery::EVENT_TASK_OVERDUE],
                ['template_id' => 'task_overdue_alert', 'language_code' => 'en', 'parameter_mappings' => ['{employee_name}', '{task_name}', '{due_date}']]
            );

            $template->update([
                'template_id' => $request->template_id,
                'language_code' => $request->language_code ?? 'en',
                'parameter_mappings' => $request->parameter_mappings ? json_decode($request->parameter_mappings, true) : $template->parameter_mappings,
                'is_active' => $request->template_active ? true : false,
            ]);
        }

        // Clear cached channel resolution
        Cache::forget("whatsapp_enabled_company_" . company()->id . "_" . NotificationDelivery::EVENT_TASK_OVERDUE);
        Cache::forget("whatsapp_enabled_company_" . company()->id . "_" . NotificationDelivery::EVENT_TASK_REMINDER);
        Cache::forget("whatsapp_enabled_company_" . company()->id . "_" . NotificationDelivery::EVENT_AUTO_TASK_REMINDER);

        return Reply::success('WhatsApp settings saved successfully.');
    }

    /**
     * Test the connection to the WhatsApp provider.
     */
    public function testConnection()
    {
        $setting = NotificationIntegration::where('company_id', company()->id)->first();

        if (!$setting || !$setting->credentials) {
            return Reply::error('No WhatsApp credentials configured. Please save settings first.');
        }

        try {
            $provider = WhatsAppManager::resolveProvider($setting);
            $result = $provider->testConnection();

            if ($result['success']) {
                return Reply::success('Connection to ' . ucfirst(str_replace('_', ' ', $setting->provider)) . ' verified successfully.');
            }

            return Reply::error('Connection failed: ' . ($result['error_message'] ?? 'Invalid credentials.'));
        } catch (\Exception $e) {
            return Reply::error('Connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Send a test WhatsApp message to the current user.
     */
    public function sendTestMessage()
    {
        $setting = NotificationIntegration::where('company_id', company()->id)
            ->where('status', 'active')
            ->first();

        if (!$setting) {
            return Reply::error('WhatsApp integration is not active. Enable and save settings first.');
        }

        $user = User::findOrFail(user()->id);
        $to = $user->routeNotificationForWhatsApp();

        if (!$to) {
            return Reply::error('Your profile does not have a phone number configured.');
        }

        $template = NotificationTemplate::where('company_id', company()->id)
            ->where('event_name', NotificationDelivery::EVENT_TASK_OVERDUE)
            ->where('is_active', true)
            ->first();

        if (!$template) {
            return Reply::error('No active template configured for task overdue notifications.');
        }

        try {
            $provider = WhatsAppManager::resolveProvider($setting);
            $result = $provider->sendMessage($to, $template->template_id, $template->language_code, [$user->name, 'Test Task', now()->format('Y-m-d')]);

            // Log the test delivery
            NotificationDelivery::create([
                'company_id' => company()->id,
                'task_id' => null,
                'event_name' => 'test_message',
                'channel' => 'whatsapp',
                'recipient' => $to,
                'event_cycle_key' => 'test_' . md5(now()->timestamp),
                'status' => $result['success'] ? 'sent' : 'failed',
                'response' => json_encode($result['response']),
            ]);

            if ($result['success']) {
                return Reply::success('Test message sent successfully to ' . $to);
            }

            return Reply::error('Message failed: ' . ($result['error_message'] ?? 'Unknown error'));
        } catch (\Exception $e) {
            return Reply::error('Failed to send test message: ' . $e->getMessage());
        }
    }
}
