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
        // Activate the module via ModuleSetting (same as GatePass approach)
        if (class_exists(ModuleSetting::class)) {
            $setting = ModuleSetting::where('module_name', 'work_order')->first();
            if (!$setting) {
                ModuleSetting::create([
                    'module_name' => 'work_order',
                    'status'      => 'active',
                    'type'        => 'admin',
                ]);
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
