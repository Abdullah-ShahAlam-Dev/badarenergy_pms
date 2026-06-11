<?php

namespace Modules\CRMEmail\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use Modules\CRMEmail\Entities\CrmEmailSetting;
use Modules\CRMEmail\Http\Requests\StoreCrmEmailSetting;

class CrmEmailSettingController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'CRM Email Settings';
        $this->activeSettingMenu = 'crm_email_settings';

        $this->middleware(function ($request, $next) {
            abort_403(!in_array(CrmEmailSetting::MODULE_NAME, $this->user->modules));
            return $next($request);
        });
    }

    /**
     * Display the settings page with tab support.
     */
    public function index()
    {
        $this->setting = CrmEmailSetting::getForCompany();

        $tab = request('tab', 'general');

        $this->activeTab = $tab;

        $this->view = match ($tab) {
            'email'   => 'crmemail::settings.ajax.email',
            default   => 'crmemail::settings.ajax.general',
        };

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();
            return Reply::dataOnly([
                'status'    => 'success',
                'html'      => $html,
                'title'     => $this->pageTitle,
                'activeTab' => $this->activeTab,
            ]);
        }

        return view('crmemail::settings.index', $this->data);
    }

    /**
     * Persist CRM email settings.
     */
    public function update(StoreCrmEmailSetting $request, $id = null)
    {
        $setting = CrmEmailSetting::getForCompany();

        $setting->from_name           = $request->input('from_name');
        $setting->from_email          = $request->input('from_email');
        $setting->throttle_per_minute = (int) $request->input('throttle_per_minute', 60);
        $setting->track_opens         = $request->input('track_opens', 'no');
        $setting->track_clicks        = $request->input('track_clicks', 'no');
        $setting->unsubscribe_footer  = $request->input('unsubscribe_footer', 'yes');
        $setting->footer_text         = $request->input('footer_text');

        // Ensure company_id is always set (firstOrNew may not have persisted it yet).
        if (!$setting->exists) {
            $setting->company_id = company()->id;
        }

        $setting->save();

        return Reply::successWithData('Settings updated successfully.', [
            'redirectUrl' => route('crm-email-settings.index'),
        ]);
    }
}
