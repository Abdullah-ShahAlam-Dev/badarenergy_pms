<?php

namespace Modules\DailyReports\Http\ViewComposers;

use Illuminate\View\View;
use Modules\DailyReports\Entities\DailyReport;
use App\Models\User;

class DashboardComposer
{
    public function compose(View $view)
    {
        if (!user() || !in_array('admin', user_roles())) {
            return;
        }

        $today          = now(company()->timezone)->toDateString();
        $allEmployeeIds = User::onlyEmployee()->pluck('id');
        $totalEmployees = $allEmployeeIds->count();
        $submittedCount = DailyReport::where('report_date', $today)->count();

        $view->with('dailyReportWidget', [
            'submittedCount'  => $submittedCount,
            'totalEmployees'  => $totalEmployees,
            'missingCount'    => max(0, $totalEmployees - $submittedCount),
            'complianceRate'  => $totalEmployees > 0
                ? round(($submittedCount / $totalEmployees) * 100) : 0,
            'today'           => $today,
            'latestReports'   => DailyReport::with('user')
                ->where('report_date', $today)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
        ]);
    }
}
