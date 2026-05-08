<?php

namespace Modules\DailyReports\Http\Controllers;

use App\Helper\Files;
use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use App\Models\ProjectTimeLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Modules\DailyReports\Entities\DailyReport;
use Modules\DailyReports\Entities\DailyReportFile;

class DailyReportController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'Daily Reports';
    }

    /**
     * Employee: list their own reports
     */
    public function index()
    {
        abort_403(user()->permission('view_daily_report') == 'none');

        $today = now($this->company->timezone)->toDateString();

        $this->todayReport = DailyReport::where('user_id', user()->id)
            ->where('report_date', $today)
            ->first();

        $this->reports = DailyReport::where('user_id', user()->id)
            ->orderBy('report_date', 'desc')
            ->paginate(15);

        return view('dailyreports::index', $this->data);
    }

    /**
     * Employee: show create form
     */
    public function create()
    {
        $isAdmin = in_array('admin', user_roles());
        abort_403(!$isAdmin && user()->permission('add_daily_report') == 'none');

        $this->reportDate = now($this->company->timezone)->toDateString();

        // Prevent duplicate for today
        $existing = DailyReport::where('user_id', user()->id)
            ->where('report_date', $this->reportDate)
            ->first();

        if ($existing) {
            if (request()->ajax()) {
                $this->report   = $existing;
                $this->timelogs = $existing->getTimelogs();
                $html = view('dailyreports::ajax.show', $this->data)->render();
                return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => "Today's Report"]);
            }
            return redirect(route('daily-reports.index'))
                ->with('info', "You have already submitted today's report. You can view it below.");
        }

        $this->timelogs    = ProjectTimeLog::where('user_id', user()->id)
            ->whereDate('start_time', $this->reportDate)
            ->with('project', 'task')
            ->get();

        $this->totalMinutes = $this->timelogs->sum('total_minutes');

        if (request()->ajax()) {
            $html = view('dailyreports::ajax.create', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => 'Submit Daily Report']);
        }

        return view('dailyreports::create', $this->data);
    }

    /**
     * Employee: store the report
     */
    /**
     * Employee: store the report
     */
    public function store(Request $request)
    {
        $isAdmin = in_array('admin', user_roles());
        abort_403(!$isAdmin && user()->permission('add_daily_report') == 'none');

        $request->validate([
            'summary' => 'required',
            'report_date' => 'required|date_format:Y-m-d'
        ]);

        $reportDate = Carbon::parse($request->report_date)->toDateString();

        // Block future dates
        $today = now($this->company->timezone)->toDateString();
        if ($reportDate > $today) {
            return Reply::error('You cannot submit a report for a future date.');
        }

        $existing = DailyReport::where('user_id', user()->id)
            ->where('report_date', $reportDate)
            ->first();

        if ($existing) {
            return Reply::error('A report for this date has already been submitted.');
        }

        $timelogs       = ProjectTimeLog::where('user_id', user()->id)
            ->whereDate('start_time', $reportDate)->get();
        $totalMinutes   = $timelogs->sum('total_minutes');
        $timelogIds     = $timelogs->pluck('id')->toArray();

        $report = DailyReport::create([
            'company_id'            => company()->id,
            'user_id'               => user()->id,
            'report_date'           => $reportDate,
            'summary'               => $request->summary,
            'blockers'              => $request->blockers,
            'next_plan'             => $request->next_plan,
            'total_logged_minutes'  => $totalMinutes,
            'timelog_ids_snapshot'  => $timelogIds,
            'status'                => 'submitted',
        ]);

        if ($request->has('file_id') && count($request->file_id) > 0) {
            DailyReportFile::whereIn('id', $request->file_id)->update(['daily_report_id' => $report->id]);
        }

        return Reply::successWithData('Report submitted successfully!', [
            'redirectUrl' => route('daily-reports.index'),
            'reportID' => $report->id
        ]);
    }

    public function storeFile(Request $request)
    {
        $isAdmin = in_array('admin', user_roles());
        abort_403(!$isAdmin && user()->permission('add_daily_report') == 'none');

        if ($request->hasFile('file')) {
            foreach ($request->file as $fileData) {
                $file = new DailyReportFile();
                $file->user_id = user()->id;
                $file->daily_report_id = $request->daily_report_id;
                
                $filename = Files::uploadLocalOrS3($fileData, 'daily-report-files');

                $file->filename = $fileData->getClientOriginalName();
                $file->hashname = $filename;
                $file->size = $fileData->getSize();
                $file->save();
            }
        }

        return Reply::success(__('messages.fileUploaded'));
    }

    public function downloadFile($id)
    {
        $file = DailyReportFile::findOrFail($id);
        return Files::downloadLocalOrS3($file->hashname, 'daily-report-files/' . $file->hashname, $file->filename);
    }

    /**
     * Show detail of a single report
     */
    public function show($id)
    {
        $this->report = DailyReport::with('user', 'files')->findOrFail($id);

        $viewPerm = user()->permission('view_daily_report');
        abort_403(!($viewPerm == 'all'
            || ($viewPerm != 'none' && $this->report->user_id == user()->id)));

        $this->timelogs = $this->report->getTimelogs();

        if (request()->ajax()) {
            $html = view('dailyreports::ajax.show', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => 'Daily Report Detail']);
        }

        return view('dailyreports::show', $this->data);
    }

    /**
     * Admin: show employees who have NOT submitted for a given date
     */
    public function missingReports(Request $request)
    {
        $isAdmin = in_array('admin', user_roles()) || in_array('system-admin', user_roles());
        abort_403(!$isAdmin && user()->permission('view_all_daily_reports') != 'all');

        $date = $request->get('date', now($this->company->timezone)->toDateString());
        $this->date = $date;

        $submittedUserIds = DailyReport::where('report_date', $date)
            ->pluck('user_id')
            ->toArray();

        $this->submittedCount  = count($submittedUserIds);
        $allEmployees = User::onlyEmployee()->get();
        
        foreach ($allEmployees as $emp) {
            Cache::forget('permission-add_daily_report-' . $emp->id);
        }
        
        $eligibleEmployees = $allEmployees->filter(function ($user) {
            return $user->permission('add_daily_report') != 'none';
        });

        $this->totalEmployees = $eligibleEmployees->count();

        $this->missingEmployees = $eligibleEmployees->reject(function ($user) use ($submittedUserIds) {
            return in_array($user->id, $submittedUserIds);
        })->values();

        $eligibleEmployeeIds = $eligibleEmployees->pluck('id');
        $this->submittedReports = DailyReport::with('user')
            ->whereIn('user_id', $eligibleEmployeeIds)
            ->where('report_date', $date)
            ->orderBy('created_at', 'desc')
            ->get();

        if (request()->ajax()) {
            $html = view('dailyreports::ajax.missing', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html]);
        }

        return view('dailyreports::missing', $this->data);
    }

    public function edit($id)
    {
        $this->report = DailyReport::with('user', 'files')->findOrFail($id);

        $editPermission = user()->permission('edit_daily_report');
        $isOwner = $this->report->user_id == user()->id;
        
        abort_403($editPermission == 'none' || ($editPermission == 'owned' && !$isOwner));

        if ($editPermission != 'all' && (!$this->report->created_at || !$this->report->created_at->isToday())) {
            abort_403('You can only edit a report on the same day it was submitted.');
        }

        $this->reportDate = $this->report->report_date->toDateString();
        $this->timelogs = $this->report->getTimelogs();
        $this->totalMinutes = $this->report->total_logged_minutes;

        if (request()->ajax()) {
            $html = view('dailyreports::ajax.edit', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => 'Edit Daily Report']);
        }

        return view('dailyreports::edit', $this->data);
    }

    public function update(Request $request, $id)
    {
        $report = DailyReport::findOrFail($id);

        $editPermission = user()->permission('edit_daily_report');
        $isOwner = $report->user_id == user()->id;
        
        abort_403($editPermission == 'none' || ($editPermission == 'owned' && !$isOwner));

        if ($editPermission != 'all' && (!$report->created_at || !$report->created_at->isToday())) {
            return Reply::error('You can only edit a report on the same day it was submitted.');
        }

        $request->validate([
            'summary' => 'required',
        ]);

        $report->summary = $request->summary;
        $report->blockers = $request->blockers;
        $report->next_plan = $request->next_plan;
        $report->save();

        if ($request->has('file_id') && count($request->file_id) > 0) {
            DailyReportFile::whereIn('id', $request->file_id)->update(['daily_report_id' => $report->id]);
        }

        return Reply::successWithData('Report updated successfully!', [
            'redirectUrl' => route('daily-reports.index'),
            'reportID' => $report->id
        ]);
    }

    public function destroy($id)
    {
        $report = DailyReport::findOrFail($id);
        
        $deletePermission = user()->permission('delete_daily_report');
        $isOwner = $report->user_id == user()->id;

        abort_403($deletePermission == 'none' || ($deletePermission == 'owned' && !$isOwner));

        if ($deletePermission != 'all' && (!$report->created_at || !$report->created_at->isToday())) {
            return Reply::error('You can only delete a report on the same day it was submitted.');
        }

        $report->delete();

        return Reply::success(__('messages.recordDeleted'));
    }
}
