<?php

namespace App\Listeners;

use App\Events\LeaveEvent;
use App\Models\EmployeeDetails;
use App\Models\Permission;
use App\Models\PermissionType;
use App\Models\Role;
use App\Notifications\LeaveApplication;
use App\Notifications\LeaveStatusApprove;
use App\Notifications\LeaveStatusReject;
use App\Notifications\LeaveStatusUpdate;
use App\Notifications\MultipleLeaveApplication;
use App\Notifications\NewLeaveRequest;
use App\Notifications\NewMultipleLeaveRequest;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Support\Facades\Notification;
use App\Traits\HasNotificationRecipients;

class LeaveListener
{
    use HasNotificationRecipients;

    /**
     * Handle the event.
     *
     * @param LeaveEvent $event
     * @return void
     */

    public function handle(LeaveEvent $event)
    {
        $leaveApproveRejectPermission = Permission::where('name', 'approve_or_reject_leaves')->first();
        $permissionUserIds = UserPermission::where('permission_id', $leaveApproveRejectPermission->id)->where('permission_type_id', PermissionType::ALL)->get()->pluck('user_id')->toArray();

        $reportingTo = EmployeeDetails::where('user_id', $event->leave->user_id)->pluck('reporting_to')->toArray();

        $adminUsers = $this->getAdminRecipients('new-leave-application', $event->leave->company->id, $event->leave);
        $adminUserIds = $adminUsers->pluck('id')->toArray();

        $notificationTo = array_merge($permissionUserIds, $adminUserIds, $reportingTo);
        $adminUsers = User::whereIn('id', array_unique(array_filter($notificationTo)))->get();

        if ($event->status == 'created') {
            if (!is_null($event->multiDates)) {
                Notification::send($event->leave->user, new MultipleLeaveApplication($event->leave, $event->multiDates));
                Notification::send($adminUsers, new NewMultipleLeaveRequest($event->leave, $event->multiDates));
            }
            else {
                Notification::send($event->leave->user, new LeaveApplication($event->leave));
                Notification::send($adminUsers, new NewLeaveRequest($event->leave));
            }
        }
        elseif ($event->status == 'statusUpdated') {
            if ($event->leave->status == 'approved') {
                Notification::send($event->leave->user, new LeaveStatusApprove($event->leave));
            }
            else {
                Notification::send($event->leave->user, new LeaveStatusReject($event->leave));
            }
        }
        elseif ($event->status == 'updated') {
            Notification::send($event->leave->user, new LeaveStatusUpdate($event->leave));
        }
    }

}
