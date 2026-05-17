<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\EmployeeLeaveQuota;
use App\Models\LeaveType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LeavesQuotaController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.leaves';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('leaves', $this->user->modules));
            return $next($request);
        });
    }

    public function update(Request $request, $id)
    {
        if ($request->leaves < 0) {
            return Reply::error('messages.leaveTypeValueError');
        }

        $type = EmployeeLeaveQuota::findOrFail($id);
        $type->no_of_leaves = $request->leaves;
        $type->save();

        session()->forget('user');

        return Reply::success(__('messages.leaveTypeAdded'));
    }

    public function employeeLeaveTypes($userId)
    {
        $quotaHtml = '';
        if ($userId != 0) {
            $user = User::withoutGlobalScope(ActiveScope::class)->withOut('clientDetails', 'role')->findOrFail($userId);
            $setting = company();
            $leaveDate = Carbon::createFromFormat('d-m-Y', '01-'.company()->year_starts_from.'-'.now(company()->timezone)->year)->startOfMonth();
            if ($setting->leaves_start_from == 'joining_date' && isset($user->employee[0])) {
                $currentYearJoiningDate = Carbon::parse($user->employee[0]->joining_date->format((now(company()->timezone)->year) . '-m-d'));
                if ($currentYearJoiningDate->isFuture()) {
                    $currentYearJoiningDate->subYear();
                }
                $startDate = $currentYearJoiningDate->copy()->toDateString();
                $endDate = $currentYearJoiningDate->copy()->addYear()->toDateString();
            } else {
                $startDate = $leaveDate->copy()->toDateString();
                $endDate = $leaveDate->copy()->addYear()->toDateString();
            }

            $leaveQuotas = LeaveType::select('leave_types.*', 'employee_details.notice_period_start_date', 'employee_details.probation_end_date',
            'employee_details.department_id as employee_department', 'employee_details.designation_id as employee_designation',
            'employee_details.marital_status as maritalStatus', 'users.gender as usergender', 'employee_details.joining_date', 
            \Illuminate\Support\Facades\DB::raw('COALESCE(employee_leave_quotas.no_of_leaves, leave_types.no_of_leaves) as employeeLeave'))
                ->leftJoin('employee_leave_quotas', function($join) use ($userId) {
                    $join->on('employee_leave_quotas.leave_type_id', '=', 'leave_types.id')
                        ->where('employee_leave_quotas.user_id', $userId);
                })
                ->join('users', 'users.id', '=', \Illuminate\Support\Facades\DB::raw($userId))
                ->join('employee_details', 'employee_details.user_id', '=', 'users.id')
                ->with(['leavesCount' => function ($q) use ($userId, $startDate, $endDate) {
                    $q->where('leaves.user_id', $userId);
                    $q->whereBetween('leaves.leave_date', [$startDate, $endDate]);
                    $q->whereIn('leaves.status', ['approved', 'pending']);
                }])
                ->get();
        
            $roles = User::with('roles')->findOrFail($userId);

            $userRole = [];

            foreach($roles->roles as $role){
                $userRole[] = $role->id;
            }

            $options = '';
            $quotaItems = [];

            foreach($leaveQuotas as $leave){
                $leaveType = LeaveType::leaveTypeCodition($leave, $userRole);

                if ($leave->employeeLeave >= 0) { /** @phpstan-ignore-line */
                    if($leaveType){
                        $options .= '<option value="' . $leave->id . '"> ' .  $leave->type_name . ' </option>';

                        $usedLeaves = $leave->leavesCount ? ($leave->leavesCount->count - ($leave->leavesCount->halfday * 0.5)) : 0;
                        $allowedLeaves = $leave->employeeLeave;
                        $remainingLeaves = max(0, $allowedLeaves - $usedLeaves);

                        $percent = $allowedLeaves > 0 ? min(100, round(($remainingLeaves / $allowedLeaves) * 100)) : 0;
                        $barColorClass = $percent > 50 ? 'bg-success' : ($percent > 20 ? 'bg-warning' : 'bg-danger');

                        $quotaItems[] = '
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="border rounded p-3 bg-white shadow-sm d-flex flex-column justify-content-between h-100">
                                <div>
                                    <h6 class="f-13 font-weight-bold text-dark mb-1">' . $leave->type_name . '</h6>
                                    <div class="d-flex align-items-baseline mb-2">
                                        <span class="f-20 font-weight-bold text-primary mr-1">' . $remainingLeaves . '</span>
                                        <span class="f-11 text-muted">/ ' . $allowedLeaves . ' leaves left</span>
                                    </div>
                                </div>
                                <div class="progress" style="height: 5px;">
                                    <div class="progress-bar ' . $barColorClass . '" role="progressbar" style="width: ' . $percent . '%" aria-valuenow="' . $remainingLeaves . '" aria-valuemin="0" aria-valuemax="' . $allowedLeaves . '"></div>
                                </div>
                            </div>
                        </div>';
                    }
                }
            }

            if (!empty($quotaItems)) {
                $quotaHtml = '
                <div class="card border-0 bg-light-grey rounded mb-4 w-100">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fa fa-info-circle text-primary f-15 mr-2"></i>
                            <h5 class="f-14 font-weight-bold text-dark mb-0">Remaining Leave Quota</h5>
                        </div>
                        <div class="row">
                            ' . implode('', $quotaItems) . '
                        </div>
                    </div>
                </div>';
            }
        }
        else {
            $leaveQuotas = LeaveType::all();

            $options = '';

            foreach ($leaveQuotas as $leaveQuota) {
                $options .= '<option value="' . $leaveQuota->id . '"> ' .  $leaveQuota->type_name . ' </option>';
            }
        }

        return Reply::dataOnly(['status' => 'success', 'data' => $options, 'quotaHtml' => $quotaHtml]);
    }

}
