<?php

namespace Modules\CRMEmail\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Module;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\Role;

class CRMEmailPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Register or retrieve the CRMEmail module
        $module = Module::updateOrCreate(
            ['module_name' => 'crm_email'],
            ['description' => 'CRM Email module for managing segments, templates, campaigns, and bulk marketing.']
        );

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
            $permission = Permission::updateOrCreate(
                ['name' => $permissionData['name']],
                [
                    'display_name' => $permissionData['display_name'],
                    'module_id' => $permissionData['module_id'],
                    'allowed_permissions' => $permissionData['allowed_permissions'],
                    'is_custom' => 0
                ]
            );

            // Assign to admin role by default
            $adminRole = Role::where('name', 'admin')->first();
            if ($adminRole) {
                PermissionRole::firstOrCreate([
                    'permission_id' => $permission->id,
                    'role_id' => $adminRole->id,
                    'permission_type_id' => 4 // 'all'
                ]);
            }
        }
    }
}
