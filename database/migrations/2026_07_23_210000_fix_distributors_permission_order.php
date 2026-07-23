<?php

use App\Models\Permission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $viewPerm = Permission::where('name', 'view_distributors')->first();
        $addPerm = Permission::where('name', 'add_distributors')->first();

        if ($viewPerm && $addPerm && $viewPerm->id < $addPerm->id) {
            DB::table('permissions')->where('id', $viewPerm->id)->update([
                'name' => 'temp_distributors',
            ]);

            DB::table('permissions')->where('id', $addPerm->id)->update([
                'name' => 'view_distributors',
                'display_name' => 'View Distributors',
            ]);

            DB::table('permissions')->where('id', $viewPerm->id)->update([
                'name' => 'add_distributors',
                'display_name' => 'Add Distributors',
            ]);
        }

        Cache::flush();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $addPerm = Permission::where('name', 'add_distributors')->first();
        $viewPerm = Permission::where('name', 'view_distributors')->first();

        if ($addPerm && $viewPerm && $addPerm->id < $viewPerm->id) {
            DB::table('permissions')->where('id', $addPerm->id)->update([
                'name' => 'temp_distributors',
            ]);

            DB::table('permissions')->where('id', $viewPerm->id)->update([
                'name' => 'add_distributors',
                'display_name' => 'Add Distributors',
            ]);

            DB::table('permissions')->where('id', $addPerm->id)->update([
                'name' => 'view_distributors',
                'display_name' => 'View Distributors',
            ]);
        }

        Cache::flush();
    }
};
