<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\User;
use App\Models\Permission;
use App\Models\UserPermission;
use App\Models\PermissionRole;
use App\Models\Role;
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
        $moduleId = DB::table('modules')->where('module_name', 'daily_reports')->value('id');
        
        if (!$moduleId) {
            return;
        }

        $adminRole = Role::where('name', 'admin')->first();
        $employeeRole = Role::where('name', 'employee')->first();

        if (!$adminRole || !$employeeRole) {
            return;
        }

        $permissions = Permission::where('module_id', $moduleId)->get();

        foreach ($permissions as $permission) {
            // Admin permissions
            PermissionRole::firstOrCreate([
                'permission_id' => $permission->id,
                'role_id' => $adminRole->id,
                'permission_type_id' => 4 // all
            ]);

            // Employee permissions
            if (in_array($permission->name, ['view_daily_report', 'add_daily_report', 'edit_daily_report'])) {
                PermissionRole::firstOrCreate([
                    'permission_id' => $permission->id,
                    'role_id' => $employeeRole->id,
                    'permission_type_id' => 2 // owned
                ]);
            }
        }

        // Now sync user_permissions for all users
        $users = User::all();
        foreach ($users as $user) {
            $userRole = $user->roles->first();
            if (!$userRole) continue;

            $rolePermissions = PermissionRole::where('role_id', $userRole->id)
                ->whereIn('permission_id', $permissions->pluck('id'))
                ->get();

            foreach ($rolePermissions as $rp) {
                UserPermission::updateOrCreate(
                    ['user_id' => $user->id, 'permission_id' => $rp->permission_id],
                    ['permission_type_id' => $rp->permission_type_id]
                );
            }
            
            // Clear user permission cache
            foreach ($permissions as $p) {
                cache()->forget('permission-' . $p->name . '-' . $user->id);
                cache()->forget('permission-id-' . $p->name . '-' . $user->id);
            }
        }
        
        cache()->forget('user_modules_1'); // Clear for admin at least
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // No need to reverse for a fix
    }
};
