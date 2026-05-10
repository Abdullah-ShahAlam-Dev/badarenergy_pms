<?php

namespace Modules\DailyReports\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use App\Models\User;
use Illuminate\Http\Request;
use Modules\DailyReports\Entities\DailyReportSetting;
use Illuminate\Support\Facades\DB;

class DailyReportSettingController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'Daily Report Settings';
        $this->activeMenu = 'daily_report_settings';
    }

    public function index()
    {
        abort_403(!in_array('admin', user_roles()));

        $this->employees = User::allEmployees();
        $this->setting = DailyReportSetting::where('company_id', company()->id)->first();
        $this->selectedReporters = $this->setting ? ($this->setting->reporter_ids ?? []) : [];

        $this->view = 'dailyreports::settings.index';

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        return view($this->view, $this->data);
    }

    public function store(Request $request)
    {
        abort_403(!in_array('admin', user_roles()));

        DailyReportSetting::updateOrCreate(
            ['company_id' => company()->id],
            ['reporter_ids' => $request->reporter_ids]
        );

        return Reply::success(__('messages.recordSaved'));
    }
}
