<?php

namespace App\Observers;

use App\Models\EmployeeDetails;
use App\Models\EmployeeLeaveQuota;
use App\Models\LeaveType;

class LeaveTypeObserver
{

    public function creating(LeaveType $leaveType)
    {
        if (company()) {
            $leaveType->company_id = company()->id;
        }
    }

    public function created(LeaveType $leaveType)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $employees = EmployeeDetails::where('company_id', $leaveType->company_id)
                ->whereHas('user')
                ->select('id', 'user_id')
                ->get();

            foreach ($employees as $key => $employee) {
                try {
                    EmployeeLeaveQuota::create(
                        [
                            'user_id' => $employee->user_id,
                            'leave_type_id' => $leaveType->id,
                            'no_of_leaves' => $leaveType->no_of_leaves
                        ]
                    );
                } catch (\Exception $e) {
                    // Ignore
                }
            }
        }
    }

}
