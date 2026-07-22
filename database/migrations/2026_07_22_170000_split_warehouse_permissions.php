<?php

use App\Models\Company;
use App\Models\Module;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\Role;
use App\Models\User;
use App\Models\UserPermission;
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
            ['module_name' => 'inventory'],
            ['description' => 'Manage physical warehouses, stock intake, transfers, serial tracking, and delivery dispatches.']
        );

        $oldPermission = Permission::where('name', 'manage_warehouses')->first();

        $newPermissions = [
            [
                'name' => 'view_warehouses',
                'display_name' => 'View Warehouses',
                'is_custom' => 1,
                'allowed' => Permission::ALL_NONE
            ],
            [
                'name' => 'add_warehouses',
                'display_name' => 'Add Warehouses',
                'is_custom' => 1,
                'allowed' => Permission::ALL_NONE
            ],
            [
                'name' => 'edit_warehouses',
                'display_name' => 'Edit Warehouses',
                'is_custom' => 1,
                'allowed' => Permission::ALL_NONE
            ],
            [
                'name' => 'delete_warehouses',
                'display_name' => 'Delete Warehouses',
                'is_custom' => 1,
                'allowed' => Permission::ALL_NONE
            ]
        ];

        $companies = Company::select('id')->get();
        $adminUsers = User::allAdmins();

        foreach ($newPermissions as $permData) {
            $permission = Permission::firstOrCreate(
                ['name' => $permData['name']],
                [
                    'display_name' => $permData['display_name'],
                    'is_custom' => $permData['is_custom'],
                    'module_id' => $module->id,
                    'allowed_permissions' => $permData['allowed']
                ]
            );

            if ($oldPermission) {
                // Migrate role permissions from manage_warehouses
                $oldRolePerms = PermissionRole::where('permission_id', $oldPermission->id)->get();
                foreach ($oldRolePerms as $oldRolePerm) {
                    PermissionRole::firstOrCreate(
                        [
                            'permission_id' => $permission->id,
                            'role_id' => $oldRolePerm->role_id,
                        ],
                        [
                            'permission_type_id' => $oldRolePerm->permission_type_id
                        ]
                    );
                }

                // Migrate user permissions from manage_warehouses
                $oldUserPerms = UserPermission::where('permission_id', $oldPermission->id)->get();
                foreach ($oldUserPerms as $oldUserPerm) {
                    UserPermission::firstOrCreate(
                        [
                            'permission_id' => $permission->id,
                            'user_id' => $oldUserPerm->user_id,
                        ],
                        [
                            'permission_type_id' => $oldUserPerm->permission_type_id
                        ]
                    );
                }
            } else {
                // Default setup if manage_warehouses was not in DB
                foreach ($companies as $company) {
                    $adminRole = Role::where('name', 'admin')->where('company_id', $company->id)->first();
                    if ($adminRole) {
                        PermissionRole::firstOrCreate(
                            ['permission_id' => $permission->id, 'role_id' => $adminRole->id],
                            ['permission_type_id' => 4] // All
                        );
                    }

                    $employeeRole = Role::where('name', 'employee')->where('company_id', $company->id)->first();
                    if ($employeeRole) {
                        PermissionRole::firstOrCreate(
                            ['permission_id' => $permission->id, 'role_id' => $employeeRole->id],
                            ['permission_type_id' => 5] // None
                        );
                    }
                }

                foreach ($adminUsers as $adminUser) {
                    UserPermission::firstOrCreate(
                        ['user_id' => $adminUser->id, 'permission_id' => $permission->id],
                        ['permission_type_id' => 4] // All
                    );
                }
            }
        }

        // Clean up old manage_warehouses permission
        if ($oldPermission) {
            PermissionRole::where('permission_id', $oldPermission->id)->delete();
            UserPermission::where('permission_id', $oldPermission->id)->delete();
            $oldPermission->delete();
        }

        Cache::flush();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Permission::whereIn('name', [
            'view_warehouses',
            'add_warehouses',
            'edit_warehouses',
            'delete_warehouses'
        ])->delete();
    }
};
