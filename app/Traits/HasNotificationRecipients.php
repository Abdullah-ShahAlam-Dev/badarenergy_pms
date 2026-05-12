<?php
namespace App\Traits;

use App\Models\EmailNotificationSetting;
use App\Models\User;

trait HasNotificationRecipients
{
    /**
     * Get the list of admins who should receive a notification based on settings.
     *
     * @param string $slug
     * @param int $companyId
     * @param mixed $object The model instance related to the notification (e.g. Leave, Task, Ticket)
     * @return \Illuminate\Support\Collection
     */
    public function getAdminRecipients($slug, $companyId, $object = null, $excludeUserIds = [])
    {
        $setting = EmailNotificationSetting::where('company_id', $companyId)->where('slug', $slug)->first();
        $allAdmins = User::allAdmins($companyId);

        if (!$setting) {
            return $allAdmins;
        }

        // 1. Never Notify (Block List) - Highest Priority
        if ($setting->blocked_admin_ids) {
            $blockedIds = json_decode($setting->blocked_admin_ids) ?: [];
            if (!empty($blockedIds)) {
                $allAdmins = $allAdmins->whereNotIn('id', $blockedIds);
            }
        }

        // 2. Behavior Filter with Bypass Logic
        $bypassIds = [];
        if ($setting->allowed_admin_ids) {
            $bypassIds = json_decode($setting->allowed_admin_ids) ?: [];
        }

        if ($setting->send_to_admins == 'involved' && $object) {
            $involvedUserIds = $this->getInvolvedUserIds($object);
            
            // Final list is (Involved Admins + Bypass Admins)
            $finalRecipients = array_unique(array_merge($involvedUserIds, $bypassIds));
            
            // Exclude specified IDs if they are NOT in the bypass list
            $finalRecipients = array_filter($finalRecipients, function($userId) use ($excludeUserIds, $bypassIds) {
                return !in_array($userId, $excludeUserIds) || in_array($userId, $bypassIds);
            });

            return $allAdmins->whereIn('id', $finalRecipients);
        }

        // If 'all' admins should be notified, we still respect exclusions for non-bypassed users
        if (!empty($excludeUserIds)) {
            $allAdminIds = $allAdmins->pluck('id')->toArray();
            $finalRecipients = array_filter($allAdminIds, function($userId) use ($excludeUserIds, $bypassIds) {
                return !in_array($userId, $excludeUserIds) || in_array($userId, $bypassIds);
            });
            return $allAdmins->whereIn('id', $finalRecipients);
        }

        return $allAdmins;
    }

    /**
     * Logic to determine who is "involved" in a specific object.
     */
    private function getInvolvedUserIds($object)
    {
        $userIds = [];

        // For Leaves
        if ($object instanceof \App\Models\Leave) {
            $userIds[] = $object->user_id;
            if ($object->user && $object->user->employeeDetail) {
                $userIds[] = $object->user->employeeDetail->reporting_to;
            }
        }
        // For Tasks
        elseif ($object instanceof \App\Models\Task) {
            $userIds = array_merge($userIds, $object->users->pluck('id')->toArray());
            $userIds = array_merge($userIds, $object->ccUsers->pluck('id')->toArray());
            $userIds[] = $object->created_by;
            $userIds[] = $object->added_by;
        }
        // For Tickets
        elseif ($object instanceof \App\Models\Ticket) {
            $userIds[] = $object->user_id;
            $userIds[] = $object->agent_id;
        }
        // For Projects
        elseif ($object instanceof \App\Models\Project) {
            $userIds = array_merge($userIds, $object->members->pluck('user_id')->toArray());
            $userIds[] = $object->client_id;
            $userIds[] = $object->added_by;
        }
        // For Expenses
        elseif ($object instanceof \App\Models\Expense) {
            $userIds[] = $object->user_id;
            $userIds[] = $object->added_by;
        }
        // For Leads
        elseif ($object instanceof \App\Models\Lead) {
            $userIds[] = $object->agent_id;
            $userIds[] = $object->added_by;
        }
        // For Proposals
        elseif ($object instanceof \App\Models\Proposal) {
            $userIds[] = $object->added_by;
            if ($object->lead) {
                $userIds[] = $object->lead->agent_id;
            }
        }
        // For Invoices
        elseif ($object instanceof \App\Models\Invoice) {
            $userIds[] = $object->client_id;
            $userIds[] = $object->added_by;
        }
        // For Orders
        elseif ($object instanceof \App\Models\Order) {
            $userIds[] = $object->client_id;
            $userIds[] = $object->added_by;
        }
        // For Attendance
        elseif ($object instanceof \App\Models\Attendance) {
            $userIds[] = $object->user_id;
            if ($object->user && $object->user->employeeDetail) {
                $userIds[] = $object->user->employeeDetail->reporting_to;
            }
        }

        return array_unique(array_filter($userIds));
    }
}
