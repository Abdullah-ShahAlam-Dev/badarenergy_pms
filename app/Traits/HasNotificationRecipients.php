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
    public function getAdminRecipients($slug, $companyId, $object = null)
    {
        $setting = EmailNotificationSetting::where('company_id', $companyId)->where('slug', $slug)->first();
        $allAdmins = User::allAdmins($companyId);

        if (!$setting) {
            return $allAdmins;
        }

        // Apply specific admin filter if configured
        if ($setting->allowed_admin_ids) {
            $allowedIds = json_decode($setting->allowed_admin_ids);
            if (!empty($allowedIds)) {
                $allAdmins = $allAdmins->whereIn('id', $allowedIds);
            }
        }

        // Apply behavior filter
        if ($setting->send_to_admins == 'all') {
            return $allAdmins;
        }

        if ($setting->send_to_admins == 'involved' && $object) {
            $involvedUserIds = $this->getInvolvedUserIds($object);
            return $allAdmins->whereIn('id', $involvedUserIds);
        }

        return collect([]);
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

        return array_unique(array_filter($userIds));
    }
}
