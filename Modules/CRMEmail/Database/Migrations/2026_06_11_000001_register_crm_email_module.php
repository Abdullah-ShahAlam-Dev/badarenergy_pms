<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Module;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $module = new Module();
        $module->module_name = 'crm_email';
        $module->description = 'CRM Email module for managing segments, templates, campaigns, and bulk marketing.';
        $module->save();

        $permissions = [
            [
                'name' => 'add_crm_email',
                'display_name' => 'Add CRM Email',
                'module_id' => $module->id,
                'allowed_permissions' => Permission::ALL_NONE
            ],
            [
                'name' => 'view_crm_email',
                'display_name' => 'View CRM Email',
                'module_id' => $module->id,
                'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5
            ],
            [
                'name' => 'edit_crm_email',
                'display_name' => 'Edit CRM Email',
                'module_id' => $module->id,
                'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5
            ],
            [
                'name' => 'delete_crm_email',
                'display_name' => 'Delete CRM Email',
                'module_id' => $module->id,
                'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5
            ]
        ];

        foreach ($permissions as $permissionData) {
            $permission = new Permission();
            $permission->name = $permissionData['name'];
            $permission->display_name = $permissionData['display_name'];
            $permission->module_id = $permissionData['module_id'];
            $permission->allowed_permissions = $permissionData['allowed_permissions'];
            $permission->is_custom = 0;
            $permission->save();

            // Assign to admin role by default
            $adminRole = Role::where('name', 'admin')->first();
            if ($adminRole) {
                $permissionRole = new PermissionRole();
                $permissionRole->permission_id = $permission->id;
                $permissionRole->role_id = $adminRole->id;
                $permissionRole->permission_type_id = 4; // 'all'
                $permissionRole->save();
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
        $module = Module::where('module_name', 'crm_email')->first();
        if ($module) {
            Permission::where('module_id', $module->id)->delete();
            $module->delete();
        }
    }
};
