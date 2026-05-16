<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Company;
use App\Models\ModuleSetting;

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
        $moduleName = 'gate_pass';

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

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        ModuleSetting::where('module_name', 'gate_pass')->delete();
    }
};
