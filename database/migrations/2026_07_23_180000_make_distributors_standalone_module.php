<?php

use App\Models\Module;
use App\Models\Permission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $module = Module::firstOrCreate(
            ['module_name' => 'distributors'],
            ['description' => 'Distributors management module']
        );

        $permissionMap = [
            'add_distributors',
            'view_distributors',
            'edit_distributors',
            'delete_distributors'
        ];

        Permission::whereIn('name', $permissionMap)->update([
            'module_id' => $module->id,
            'is_custom' => 0
        ]);

        Cache::flush();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $clientModule = Module::where('module_name', 'clients')->first();
        if ($clientModule) {
            Permission::whereIn('name', [
                'add_distributors',
                'view_distributors',
                'edit_distributors',
                'delete_distributors'
            ])->update([
                'module_id' => $clientModule->id,
                'is_custom' => 1
            ]);
        }
    }
};
