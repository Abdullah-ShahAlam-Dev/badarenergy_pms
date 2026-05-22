<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\EmailNotificationSetting;
use App\Models\PusherSetting;
use App\Models\PushNotificationSetting;
use App\Models\SlackSetting;
use App\Models\SmtpSetting;

class NotificationSettingController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.notificationSettings';
        $this->activeSettingMenu = 'notification_settings';
        $this->middleware(function ($request, $next) {
            abort_403(user()->permission('manage_notification_setting') !== 'all');

            return $next($request);
        });
    }

    public function index()
    {
        $tab = request('tab');

        $this->emailSettings = EmailNotificationSetting::all();

        $sendEmailCount = $this->emailSettings->filter(function ($value, $key) {
            return $value->send_email == 'yes';
        })->count();

        $this->checkedAll = ($this->emailSettings->count() == $sendEmailCount) ? true : false;

        $this->slackSettings = SlackSetting::first();
        $this->pushSettings = PushNotificationSetting::first();
        $this->pusherSettings = PusherSetting::first();
        $this->whatsappSetting = \App\Models\NotificationIntegration::where('company_id', company()->id)->first();

        switch ($tab) {
        case 'slack-setting':
            $this->view = 'notification-settings.ajax.slack-setting';
            break;
        case 'push-notification-setting':
            $this->view = 'notification-settings.ajax.push-notification-setting';
            break;
        case 'pusher-setting':
            $this->view = 'notification-settings.ajax.pusher-setting';
            break;
        case 'email-templates':
            $this->emailTemplates = \App\Models\EmailTemplate::all();
            $this->view = 'notification-settings.ajax.email-templates';
            break;
        case 'whatsapp-setting':
            $this->whatsappSetting = \App\Models\NotificationIntegration::firstOrCreate(
                ['company_id' => company()->id, 'provider' => 'meta_cloud'],
                ['status' => 'inactive', 'credentials' => []]
            );
            $this->whatsappTemplate = \App\Models\NotificationTemplate::firstOrCreate(
                ['company_id' => company()->id, 'event_name' => \App\Models\NotificationDelivery::EVENT_TASK_OVERDUE],
                ['template_id' => 'task_overdue_alert', 'language_code' => 'en', 'parameter_mappings' => ['{employee_name}', '{task_name}', '{due_date}']]
            );
            $this->whatsappLogs = \App\Models\NotificationDelivery::with('task')
                ->where('company_id', company()->id)
                ->where('channel', 'whatsapp')
                ->latest()
                ->limit(50)
                ->get();
            $this->view = 'notification-settings.ajax.whatsapp-setting';
            break;
        default:
            $this->smtpSetting = SmtpSetting::first();
            $this->view = 'notification-settings.ajax.email-setting';
            break;
        }

        $this->activeTab = $tab ?: 'email-setting';

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle, 'activeTab' => $this->activeTab]);
        }

        return view('notification-settings.index', $this->data);
    }

}
