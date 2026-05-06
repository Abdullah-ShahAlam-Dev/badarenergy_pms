<?php

namespace Modules\DailyReports\Http\Controllers;

use App\Http\Controllers\AccountBaseController;
use App\Models\User;
use App\Models\Team;
use Modules\DailyReports\DataTables\DailyReportDataTable;
use Modules\DailyReports\Entities\DailyReport;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DailyReportReportController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'Daily Report Analysis';
    }

    public function index(DailyReportDataTable $dataTable)
    {
        $isAdmin = in_array('admin', user_roles());
        abort_403(!$isAdmin && user()->permission('view_all_daily_reports') != 'all');

        $today = now($this->company->timezone)->toDateString();

        // Compliance stats for today
        $allEmployeeIds          = User::allEmployees()->pluck('id');
        $this->totalEmployees    = $allEmployeeIds->count();
        $this->submittedToday    = DailyReport::where('report_date', $today)->count();
        $this->missingToday      = max(0, $this->totalEmployees - $this->submittedToday);
        $this->complianceRate    = $this->totalEmployees > 0
            ? round(($this->submittedToday / $this->totalEmployees) * 100)
            : 0;

        $this->employees   = User::allEmployees();
        $this->departments = Team::all();
        $this->today       = $today;

        return $dataTable->render('dailyreports::report', $this->data);
    }

    public function employeeWise()
    {
        $isAdmin = in_array('admin', user_roles());
        abort_403(!$isAdmin && user()->permission('view_all_daily_reports') != 'all');

        $this->pageTitle = 'Employee Wise Daily Reports';
        
        $this->employees = User::withCount('dailyReports')
            ->with(['dailyReports' => function($q) {
                $q->orderBy('report_date', 'desc')->limit(1);
            }, 'employeeDetail', 'employeeDetail.designation', 'employeeDetail.department'])
            ->whereHas('employeeDetail')
            ->get();

        return view('dailyreports::employee_reports', $this->data);
    }
}
