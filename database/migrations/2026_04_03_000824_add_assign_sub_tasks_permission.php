<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Permission;
use App\Models\Module;
use App\Models\PermissionType;
use App\Models\Role;
use App\Models\PermissionRole;
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
        $taskModule = Module::where('module_name', 'tasks')->first();
        if ($taskModule) {
            $permission = Permission::updateOrCreate(
                [
                    'name' => 'assign_sub_tasks',
                    'module_id' => $taskModule->id,
                ],
                [
                    'display_name' => 'Assign Sub Tasks',
                    'is_custom' => 1,
                    'allowed_permissions' => '{"all":4, "none":5}'
                ]
            );

            // Fetch permission types
            $allPermType = PermissionType::where('name', 'all')->first();
            $nonePermType = PermissionType::where('name', 'none')->first();

            // Setup roles
            $rolesToGiveAll = Role::whereIn('name', ['admin', 'Project Task Heads'])->get();
            $rolesToGiveNone = Role::whereNotIn('name', ['admin', 'Project Task Heads'])->get();

            if ($allPermType) {
                foreach ($rolesToGiveAll as $role) {
                    PermissionRole::updateOrCreate([
                        'permission_id' => $permission->id,
                        'role_id' => $role->id,
                    ], [
                        'permission_type_id' => $allPermType->id,
                    ]);
                }
            }

            if ($nonePermType) {
                foreach ($rolesToGiveNone as $role) {
                    PermissionRole::updateOrCreate([
                        'permission_id' => $permission->id,
                        'role_id' => $role->id,
                    ], [
                        'permission_type_id' => $nonePermType->id,
                    ]);
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
        $permission = Permission::where('name', 'assign_sub_tasks')->first();
        if ($permission) {
            PermissionRole::where('permission_id', $permission->id)->delete();
            DB::table('user_permissions')->where('permission_id', $permission->id)->delete();
            $permission->delete();
        }
    }
};
