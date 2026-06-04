<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\User;
use App\Models\Role;
use App\Models\Module;
use App\Models\Permission;
use App\Models\UserPermission;
use Illuminate\Support\Facades\Cache;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $adminRole = Role::where('name', 'admin')->first();
        if (!$adminRole) {
            return;
        }

        $module = Module::where('module_name', 'gate_pass')->first();
        if (!$module) {
            return;
        }

        $permissions = Permission::where('module_id', $module->id)->get();
        $adminUsers = $adminRole->users;

        foreach ($adminUsers as $user) {
            foreach ($permissions as $permission) {
                UserPermission::firstOrCreate([
                    'user_id' => $user->id,
                    'permission_id' => $permission->id,
                ], [
                    'permission_type_id' => 4 // 'all'
                ]);

                // Clear cache for this permission
                Cache::forget('permission-' . $permission->name . '-' . $user->id);
                Cache::forget('permission-id-' . $permission->name . '-' . $user->id);
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
        // No reverse operation needed for permission sync
    }
};
