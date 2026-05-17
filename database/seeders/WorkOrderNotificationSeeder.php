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
            // Seed Email Notification Setting
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

            // Seed Module Setting for Admin
            DB::table('module_settings')->updateOrInsert(
                [
                    'company_id'  => $company->id,
                    'module_name' => 'work_order',
                    'type'        => 'admin'
                ],
                [
                    'status'      => 'active'
                ]
            );

            // Seed Module Setting for Employee
            DB::table('module_settings')->updateOrInsert(
                [
                    'company_id'  => $company->id,
                    'module_name' => 'work_order',
                    'type'        => 'employee'
                ],
                [
                    'status'      => 'active'
                ]
            );
        }
    }
}
