<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Company;
use App\Models\ModuleSetting;
use App\Models\PermissionRole;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $companies = Company::all();

        $customModules = [
            'crm_email' => ['admin', 'employee'],
            'daily_reports' => ['admin', 'employee'],
            'gate_pass' => ['admin', 'employee'],
            'recruit' => ['admin', 'employee'],
            'work_order' => ['admin', 'employee'],
            'zoom' => ['admin', 'employee', 'client']
        ];

        foreach ($companies as $company) {
            foreach ($customModules as $module => $roles) {
                foreach ($roles as $role) {
                    $exists = DB::table('module_settings')
                        ->where('company_id', $company->id)
                        ->where('module_name', $module)
                        ->where('type', $role)
                        ->exists();

                    if (!$exists) {
                        DB::table('module_settings')->insert([
                            'company_id' => $company->id,
                            'module_name' => $module,
                            'type' => $role,
                            'status' => 'active',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                try {
                    if (class_exists(PermissionRole::class)) {
                        PermissionRole::insertModuleRolePermission($module, $company->id);
                    }
                } catch (\Exception $e) {
                    // Ignore if permissions aren't fully registered in DB yet for this module
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $customModules = ['crm_email', 'daily_reports', 'gate_pass', 'recruit', 'work_order', 'zoom'];
        DB::table('module_settings')->whereIn('module_name', $customModules)->delete();
    }
};
