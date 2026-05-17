<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkOrderNotificationSeeder extends Seeder
{
    public function run()
    {
        $companies = DB::table('companies')->get();

        foreach ($companies as $company) {
            DB::table('email_notification_settings')->updateOrInsert(
                [
                    'company_id' => $company->id,
                    'slug'       => 'work-order-notification'
                ],
                [
                    'setting_name' => 'Work Order Notification',
                    'send_email'   => 'yes',
                    'send_slack'   => 'no',
                    'send_push'    => 'yes'
                ]
            );
        }
    }
}
