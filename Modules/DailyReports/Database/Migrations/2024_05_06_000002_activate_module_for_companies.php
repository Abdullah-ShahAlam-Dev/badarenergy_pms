<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Company;
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
        
        foreach ($companies as $company) {
            // Add to module_settings for employees
            DB::table('module_settings')->insertOrIgnore([
                'company_id' => $company->id,
                'module_name' => 'daily_reports',
                'status' => 'active',
                'type' => 'employee'
            ]);
            
            // Add to module_settings for admin
            DB::table('module_settings')->insertOrIgnore([
                'company_id' => $company->id,
                'module_name' => 'daily_reports',
                'status' => 'active',
                'type' => 'admin'
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('module_settings')->where('module_name', 'daily_reports')->delete();
    }
};
