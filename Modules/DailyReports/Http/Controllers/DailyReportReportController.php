<?php

namespace Modules\DailyReports\Http\Controllers;

use App\Http\Controllers\AccountBaseController;
use App\Models\User;
use App\Models\Team;
use Modules\DailyReports\DataTables\DailyReportDataTable;
use Modules\DailyReports\Entities\DailyReport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DailyReportReportController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'Daily Report Analysis';
    }

    public function index(DailyReportDataTable $dataTable)
    {
        $isAdmin = in_array('admin', user_roles()) || in_array('system-admin', user_roles());
        abort_403(!$isAdmin && user()->permission('view_all_daily_reports') != 'all');

        $today = now($this->company->timezone)->toDateString();

        // Compliance stats for today
        $allEmployees = User::onlyEmployee()->get();
        
        foreach ($allEmployees as $emp) {
            Cache::forget('permission-add_daily_report-' . $emp->id);
        }

        $eligibleEmployees = $allEmployees->filter(function ($user) {
            return $user->permission('add_daily_report') != 'none' && $user->permission('add_daily_report') != false;
        });

        $allEmployeeIds          = $eligibleEmployees->pluck('id');
        $this->totalEmployees    = $allEmployeeIds->count();
        $this->submittedToday    = DailyReport::whereIn('user_id', $allEmployeeIds)->where('report_date', $today)->count();
        $this->missingToday      = max(0, $this->totalEmployees - $this->submittedToday);
        $this->complianceRate    = $this->totalEmployees > 0
            ? round(($this->submittedToday / $this->totalEmployees) * 100)
            : 0;

        $this->employees   = $eligibleEmployees;
        $this->departments = Team::all();
        $this->today       = $today;

        return $dataTable->render('dailyreports::report', $this->data);
    }

    public function employeeWise()
    {
        $isAdmin = in_array('admin', user_roles()) || in_array('system-admin', user_roles());
        abort_403(!$isAdmin && user()->permission('view_all_daily_reports') != 'all');

        $this->pageTitle = 'Employee Wise Daily Reports';
        
        $allEmployees = User::withCount('dailyReports')
            ->with(['dailyReports' => function($q) {
                $q->orderBy('report_date', 'desc')->limit(1);
            }, 'employeeDetail', 'employeeDetail.designation', 'employeeDetail.department'])
            ->whereHas('employeeDetail')
            ->get();

        foreach ($allEmployees as $emp) {
            Cache::forget('permission-add_daily_report-' . $emp->id);
        }

        $this->employees = $allEmployees->filter(function ($user) {
            return $user->permission('add_daily_report') != 'none' && $user->permission('add_daily_report') != false;
        });

        return view('dailyreports::employee_reports', $this->data);
    }
}
