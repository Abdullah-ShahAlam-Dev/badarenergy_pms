<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\User;
use App\Models\Permission;
use App\Models\UserPermission;
use App\Models\PermissionRole;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $dailyReportPermissions = Permission::where('module_id', function($query) {
            $query->select('id')->from('modules')->where('module_name', 'daily_reports');
        })->get();

        $users = User::all();

        foreach ($users as $user) {
            foreach ($dailyReportPermissions as $permission) {
                // Get the permission type assigned to the user's role for this permission
                $rolePermission = PermissionRole::where('role_id', $user->role[0]->role_id)
                    ->where('permission_id', $permission->id)
                    ->first();

                if ($rolePermission) {
                    UserPermission::firstOrCreate([
                        'user_id' => $user->id,
                        'permission_id' => $permission->id,
                        'permission_type_id' => $rolePermission->permission_type_id
                    ]);
                } else {
                    // Default for admin if not found (fallback)
                    if ($user->hasRole('admin')) {
                        UserPermission::firstOrCreate([
                            'user_id' => $user->id,
                            'permission_id' => $permission->id,
                            'permission_type_id' => 4 // 'all'
                        ]);
                    } else {
                        // Default for others: none
                        UserPermission::firstOrCreate([
                            'user_id' => $user->id,
                            'permission_id' => $permission->id,
                            'permission_type_id' => 5 // 'none'
                        ]);
                    }
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
        $dailyReportPermissions = Permission::where('module_id', function($query) {
            $query->select('id')->from('modules')->where('module_name', 'daily_reports');
        })->pluck('id');

        UserPermission::whereIn('permission_id', $dailyReportPermissions)->delete();
    }
};
