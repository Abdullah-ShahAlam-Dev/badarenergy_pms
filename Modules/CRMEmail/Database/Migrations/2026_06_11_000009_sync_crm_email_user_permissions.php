<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\User;
use App\Models\Permission;
use App\Models\UserPermission;
use App\Models\PermissionRole;
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
        $crmEmailPermissions = Permission::where('module_id', function($query) {
            $query->select('id')->from('modules')->where('module_name', 'crm_email');
        })->get();

        if ($crmEmailPermissions->isEmpty()) {
            return;
        }

        $users = User::all();

        foreach ($users as $user) {
            $userRole = $user->roles->first();

            foreach ($crmEmailPermissions as $permission) {
                $permissionTypeId = null;

                if ($userRole) {
                    $rolePermission = PermissionRole::where('role_id', $userRole->id)
                        ->where('permission_id', $permission->id)
                        ->first();

                    if ($rolePermission) {
                        $permissionTypeId = $rolePermission->permission_type_id;
                    }
                }

                // Fallbacks
                if (is_null($permissionTypeId)) {
                    if ($user->hasRole('admin')) {
                        $permissionTypeId = 4; // 'all'
                    } else {
                        $permissionTypeId = 5; // 'none'
                    }
                }

                UserPermission::firstOrCreate([
                    'user_id' => $user->id,
                    'permission_id' => $permission->id,
                ], [
                    'permission_type_id' => $permissionTypeId
                ]);

                // Clear caches
                Cache::forget('permission-' . $permission->name . '-' . $user->id);
                Cache::forget('permission-id-' . $permission->name . '-' . $user->id);
            }

            Cache::forget('user_modules_' . $user->id);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $crmEmailPermissions = Permission::where('module_id', function($query) {
            $query->select('id')->from('modules')->where('module_name', 'crm_email');
        })->pluck('id');

        if ($crmEmailPermissions->isNotEmpty()) {
            UserPermission::whereIn('permission_id', $crmEmailPermissions)->delete();
        }
    }
};
