<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\LeaveType;
use App\Models\EmployeeDetails;
use App\Models\EmployeeLeaveQuota;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DebugEmployeeLeaves extends Command
{
    protected $signature = 'debug:employee-leaves {userId}';
    protected $description = 'Debug leave type conditions and quotas for a specific user ID';

    public function handle()
    {
        $userId = $this->argument('userId');
        $this->info("=== Debugging User ID: {$userId} ===");

        // Fetch User bypassing active scope
        $user = User::withoutGlobalScope(\App\Scopes\ActiveScope::class)
            ->withoutGlobalScope(\App\Scopes\CompanyScope::class)
            ->find($userId);

        if (!$user) {
            $this->error("User not found!");
            return 1;
        }

        $this->line("User Name: " . $user->name);
        $this->line("User Email: " . $user->email);
        $this->line("User Status: " . $user->status);
        $this->line("User Company ID: " . $user->company_id);

        // Fetch Roles
        $roles = $user->roles;
        $userRole = $roles->pluck('id')->toArray();
        $this->line("User Roles: " . implode(', ', $roles->pluck('name')->toArray()) . " (IDs: " . implode(', ', $userRole) . ")");

        // Fetch EmployeeDetails bypassing scope
        $empDetail = EmployeeDetails::withoutGlobalScope(\App\Scopes\CompanyScope::class)
            ->where('user_id', $userId)
            ->first();

        if (!$empDetail) {
            $this->warn("EmployeeDetails record does NOT exist in database for this user!");
        } else {
            $this->info("EmployeeDetails record exists:");
            $this->line(" - ID: " . $empDetail->id);
            $this->line(" - Company ID in Details: " . $empDetail->company_id);
            $this->line(" - Joining Date: " . ($empDetail->joining_date ? $empDetail->joining_date->toDateTimeString() : 'NULL'));
            $this->line(" - Probation End Date: " . ($empDetail->probation_end_date ? $empDetail->probation_end_date->toDateTimeString() : 'NULL'));
            $this->line(" - Notice Period Start: " . ($empDetail->notice_period_start_date ? $empDetail->notice_period_start_date->toDateTimeString() : 'NULL'));
            $this->line(" - Gender: " . ($user->gender ?? 'NULL'));
            $this->line(" - Marital Status: " . ($empDetail->marital_status ?? 'NULL'));
            $this->line(" - Department ID: " . ($empDetail->department_id ?? 'NULL'));
            $this->line(" - Designation ID: " . ($empDetail->designation_id ?? 'NULL'));
        }

        // Fetch all leave types in user's company
        $leaveTypes = LeaveType::withoutGlobalScope(\App\Scopes\CompanyScope::class)
            ->where('company_id', $user->company_id)
            ->get();

        $this->info("\n=== Evaluating Leave Types in Company ({$user->company_id}) ===");
        if ($leaveTypes->isEmpty()) {
            $this->warn("No leave types defined for this company!");
            return 0;
        }

        foreach ($leaveTypes as $leave) {
            $this->line("\n------------------------------------------------");
            $this->info("Leave Type: {$leave->type_name} (ID: {$leave->id})");

            // Check Quota Record
            $quota = EmployeeLeaveQuota::where('user_id', $userId)
                ->where('leave_type_id', $leave->id)
                ->first();

            if ($quota) {
                $this->line(" - EmployeeLeaveQuota record exists. Leaves allocated: " . $quota->no_of_leaves);
            } else {
                $this->warn(" - EmployeeLeaveQuota record does NOT exist! (Will fall back to default: {$leave->no_of_leaves})");
            }

            // Run individual leave condition checks
            $currentDate = Carbon::now()->format('Y-m-d');
            $leaveRole = $leave->role;

            $effectiveDate = null;
            if(!is_null($leave->effective_type) && !is_null($leave->effective_after)){
                $joiningDateStr = $empDetail ? ($empDetail->joining_date ? $empDetail->joining_date->toDateString() : null) : null;
                $effectiveDate = $leave->effective_type == 'days' 
                    ? Carbon::parse($joiningDateStr)->addDays($leave->effective_after)->format('Y-m-d') 
                    : Carbon::parse($joiningDateStr)->addMonths($leave->effective_after)->format('Y-m-d');
            }

            $probation = $empDetail ? ($empDetail->probation_end_date ? $empDetail->probation_end_date->format('Y-m-d') : null) : null;
            $noticePeriod = $empDetail ? ($empDetail->notice_period_start_date ? $empDetail->notice_period_start_date->format('Y-m-d') : null) : null;

            // Clauses
            $probationOk = (is_null($probation) || ($leave->allowed_probation == 0 && $probation < $currentDate) || $leave->allowed_probation == 1);
            $noticeOk = (is_null($noticePeriod) || ($leave->allowed_notice == 0 && $noticePeriod > $currentDate) || $leave->allowed_notice == 1);
            $genderOk = (is_null($leave->gender) || in_array($user->gender, (array)json_decode($leave->gender)));
            $maritalOk = (is_null($leave->marital_status) || in_array($empDetail ? $empDetail->marital_status : null, (array)json_decode($leave->marital_status)));
            $deptOk = (is_null($leave->department) || in_array($empDetail ? $empDetail->department_id : null, (array)json_decode($leave->department)));
            $desigOk = (is_null($leave->designation) || in_array($empDetail ? $empDetail->designation_id : null, (array)json_decode($leave->designation)));
            $roleOk = (is_null($leave->role) || array_intersect($userRole, (array)json_decode($leaveRole)));
            $effectiveOk = (is_null($leave->effective_after) || $currentDate > $effectiveDate);

            $this->line(" - probation check: " . ($probationOk ? "PASS" : "FAIL") . " (probation end: " . ($probation ?? 'NULL') . ", allowed_probation: {$leave->allowed_probation})");
            $this->line(" - notice period check: " . ($noticeOk ? "PASS" : "FAIL") . " (notice start: " . ($noticePeriod ?? 'NULL') . ", allowed_notice: {$leave->allowed_notice})");
            $this->line(" - gender check: " . ($genderOk ? "PASS" : "FAIL") . " (user gender: " . ($user->gender ?? 'NULL') . ", leave allowed: " . ($leave->gender ?? 'ALL') . ")");
            $this->line(" - marital status check: " . ($maritalOk ? "PASS" : "FAIL") . " (user marital: " . ($empDetail ? $empDetail->marital_status : 'NULL') . ", leave allowed: " . ($leave->marital_status ?? 'ALL') . ")");
            $this->line(" - department check: " . ($deptOk ? "PASS" : "FAIL") . " (user dept ID: " . ($empDetail ? $empDetail->department_id : 'NULL') . ", leave allowed: " . ($leave->department ?? 'ALL') . ")");
            $this->line(" - designation check: " . ($desigOk ? "PASS" : "FAIL") . " (user desig ID: " . ($empDetail ? $empDetail->designation_id : 'NULL') . ", leave allowed: " . ($leave->designation ?? 'ALL') . ")");
            $this->line(" - role check: " . ($roleOk ? "PASS" : "FAIL") . " (user roles: " . implode(', ', $userRole) . ", leave allowed: " . ($leave->role ?? 'ALL') . ")");
            $this->line(" - effective date check: " . ($effectiveOk ? "PASS" : "FAIL") . " (joining date: " . ($joiningDateStr ?? 'NULL') . ", effective after: " . ($leave->effective_after ?? 'NULL') . " {$leave->effective_type}, effective date: " . ($effectiveDate ?? 'NULL') . ")");

            $final = ($probationOk && $noticeOk && $genderOk && $maritalOk && $deptOk && $desigOk && $roleOk && $effectiveOk);
            if ($final) {
                $this->info(" - OVERALL STATUS: AVAILABLE");
            } else {
                $this->error(" - OVERALL STATUS: NOT AVAILABLE");
            }
        }

        return 0;
    }
}
