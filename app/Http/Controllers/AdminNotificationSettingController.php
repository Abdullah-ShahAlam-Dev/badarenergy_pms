<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\EmailNotificationSetting;
use App\Models\User;
use Illuminate\Http\Request;

class AdminNotificationSettingController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('app.menu.adminNotificationSettings');
        $this->activeSettingMenu = 'admin_notification_settings';
        $this->middleware(function ($request, $next) {
            abort_403(user()->permission('manage_notification_setting') !== 'all');
            return $next($request);
        });
    }

    public function index()
    {
        $this->emailSettings = EmailNotificationSetting::all();
        $this->allAdmins = User::allAdmins(company()->id);

        if (request()->ajax()) {
            $html = view('admin-notification-settings.ajax.index', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        return view('admin-notification-settings.index', $this->data);
    }

    public function update(Request $request)
    {
        if ($request->send_to_admins) {
            foreach ($request->send_to_admins as $id => $value) {
                $allowedAdmins = isset($request->allowed_admin_ids[$id]) ? json_encode($request->allowed_admin_ids[$id]) : null;
                
                EmailNotificationSetting::where('id', $id)->update([
                    'send_to_admins' => $value,
                    'allowed_admin_ids' => $allowedAdmins
                ]);
            }
        }

        return Reply::success(__('messages.updateSuccess'));
    }

}
