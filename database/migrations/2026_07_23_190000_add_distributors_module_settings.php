<?php

use App\Models\Company;
use App\Models\ModuleSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $companies = Company::select('id')->get();
        $types = ['admin', 'employee'];

        foreach ($companies as $company) {
            foreach ($types as $type) {
                ModuleSetting::firstOrCreate([
                    'company_id' => $company->id,
                    'module_name' => 'distributors',
                    'type' => $type,
                ], [
                    'status' => 'active'
                ]);
            }
        }

        Cache::flush();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        ModuleSetting::where('module_name', 'distributors')->delete();
    }
};
