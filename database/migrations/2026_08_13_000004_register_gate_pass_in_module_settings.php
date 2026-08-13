<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Company;
use App\Models\ModuleSetting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $companies = Company::all();
        if ($companies->isEmpty()) {
            $companies = collect([(object)['id' => 1]]);
        }
        
        $types = ['admin', 'employee', 'client'];

        foreach ($companies as $company) {
            foreach ($types as $type) {
                $exists = ModuleSetting::where('company_id', $company->id)
                    ->where('module_name', 'gate_pass')
                    ->where('type', $type)
                    ->exists();

                if (!$exists) {
                    ModuleSetting::create([
                        'company_id' => $company->id,
                        'type' => $type,
                        'module_name' => 'gate_pass',
                        'status' => 'active',
                        'is_allowed' => '1',
                    ]);
                }
            }
        }

        // Flush application & user modules cache
        cache()->flush();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        ModuleSetting::where('module_name', 'gate_pass')->delete();
    }
};
