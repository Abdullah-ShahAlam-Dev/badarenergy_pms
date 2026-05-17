<?php
/*
 * WorkSuite PMS - WorkOrder Management Module
 * Migration 3: Activate the module in modules_statuses
 */

use Illuminate\Database\Migrations\Migration;
use App\Models\ModuleSetting;

return new class extends Migration
{
    public function up()
    {
        if (class_exists(ModuleSetting::class)) {
            $companies = \App\Models\Company::all();
            $moduleName = 'work_order';

            foreach ($companies as $company) {
                $module = ModuleSetting::where('company_id', $company->id)
                    ->where('module_name', $moduleName)
                    ->where('type', 'admin')
                    ->first();

                if (!$module) {
                    ModuleSetting::create([
                        'company_id' => $company->id,
                        'module_name' => $moduleName,
                        'status' => 'active',
                        'type' => 'admin'
                    ]);
                }

                $moduleEmployee = ModuleSetting::where('company_id', $company->id)
                    ->where('module_name', $moduleName)
                    ->where('type', 'employee')
                    ->first();

                if (!$moduleEmployee) {
                    ModuleSetting::create([
                        'company_id' => $company->id,
                        'module_name' => $moduleName,
                        'status' => 'active',
                        'type' => 'employee'
                    ]);
                }
            }
        }

        // Also update modules_statuses.json file if it exists
        $statusFile = base_path('modules_statuses.json');
        if (file_exists($statusFile)) {
            $statuses = json_decode(file_get_contents($statusFile), true) ?? [];
            $statuses['WorkOrder'] = true;
            file_put_contents($statusFile, json_encode($statuses, JSON_PRETTY_PRINT));
        }
    }

    public function down()
    {
        $statusFile = base_path('modules_statuses.json');
        if (file_exists($statusFile)) {
            $statuses = json_decode(file_get_contents($statusFile), true) ?? [];
            $statuses['WorkOrder'] = false;
            file_put_contents($statusFile, json_encode($statuses, JSON_PRETTY_PRINT));
        }
    }
};
