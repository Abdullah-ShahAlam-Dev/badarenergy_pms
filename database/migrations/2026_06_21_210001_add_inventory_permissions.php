<?php

use App\Models\Company;
use App\Models\Module;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\Role;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $module = Module::where('module_name', 'products')->first();

        if ($module) {
            $permissionsData = [
                // Warehouses
                [
                    'name' => 'view_warehouses',
                    'display_name' => 'View Warehouses',
                    'allowed' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5
                ],
                [
                    'name' => 'add_warehouses',
                    'display_name' => 'Add Warehouses',
                    'allowed' => Permission::ALL_NONE
                ],
                [
                    'name' => 'edit_warehouses',
                    'display_name' => 'Edit Warehouses',
                    'allowed' => Permission::ALL_NONE
                ],
                [
                    'name' => 'delete_warehouses',
                    'display_name' => 'Delete Warehouses',
                    'allowed' => Permission::ALL_NONE
                ],
                // Inventory
                [
                    'name' => 'view_inventory',
                    'display_name' => 'View Inventory',
                    'allowed' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5
                ],
                [
                    'name' => 'adjust_inventory',
                    'display_name' => 'Adjust Inventory',
                    'allowed' => Permission::ALL_NONE
                ],
            ];

            $companies = Company::select('id')->get();
            $adminUsers = User::allAdmins();

            foreach ($permissionsData as $permData) {
                $permission = Permission::firstOrCreate(
                    ['name' => $permData['name']],
                    [
                        'display_name' => $permData['display_name'],
                        'is_custom' => 1,
                        'module_id' => $module->id,
                        'allowed_permissions' => $permData['allowed']
                    ]
                );

                // Assign to Admin Role for all companies
                foreach ($companies as $company) {
                    $role = Role::where('name', 'admin')
                        ->where('company_id', $company->id)
                        ->first();

                    if ($role) {
                        PermissionRole::firstOrCreate([
                            'permission_id' => $permission->id,
                            'role_id' => $role->id,
                        ], [
                            'permission_type_id' => 4 // All
                        ]);
                    }
                }

                // Assign directly to Admin Users
                foreach ($adminUsers as $adminUser) {
                    UserPermission::firstOrCreate([
                        'user_id' => $adminUser->id,
                        'permission_id' => $permission->id,
                    ], [
                        'permission_type_id' => 4 // All
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
    public function down(): void
    {
        $names = [
            'view_warehouses', 'add_warehouses', 'edit_warehouses', 'delete_warehouses',
            'view_inventory', 'adjust_inventory'
        ];

        $perms = Permission::whereIn('name', $names)->get();

        foreach ($perms as $perm) {
            PermissionRole::where('permission_id', $perm->id)->delete();
            UserPermission::where('permission_id', $perm->id)->delete();
            $perm->delete();
        }
    }
};
