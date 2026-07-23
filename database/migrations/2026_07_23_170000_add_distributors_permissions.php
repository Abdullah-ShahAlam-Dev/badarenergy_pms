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
            ['module_name' => 'clients'],
            ['description' => 'Clients and Distributors management']
        );

        $newPermissions = [
            [
                'name' => 'view_distributors',
                'display_name' => 'View Distributors',
                'is_custom' => 1,
                'allowed' => Permission::ALL_NONE
            ],
            [
                'name' => 'add_distributors',
                'display_name' => 'Add Distributors',
                'is_custom' => 1,
                'allowed' => Permission::ALL_NONE
            ],
            [
                'name' => 'edit_distributors',
                'display_name' => 'Edit Distributors',
                'is_custom' => 1,
                'allowed' => Permission::ALL_NONE
            ],
            [
                'name' => 'delete_distributors',
                'display_name' => 'Delete Distributors',
                'is_custom' => 1,
                'allowed' => Permission::ALL_NONE
            ]
        ];

        $companies = Company::select('id')->get();
        $adminUsers = User::allAdmins();
        $allUsers = User::whereHas('roles', function($q) {
            $q->where('name', 'employee');
        })->get();

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
                        ['permission_type_id' => 4] // All by default for Distributors
                    );
                }
            }

            foreach ($adminUsers as $adminUser) {
                UserPermission::firstOrCreate(
                    ['user_id' => $adminUser->id, 'permission_id' => $permission->id],
                    ['permission_type_id' => 4] // All
                );
            }

            foreach ($allUsers as $empUser) {
                UserPermission::firstOrCreate(
                    ['user_id' => $empUser->id, 'permission_id' => $permission->id],
                    ['permission_type_id' => 4] // All by default
                );
            }
        }

        Cache::flush();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Permission::whereIn('name', [
            'view_distributors',
            'add_distributors',
            'edit_distributors',
            'delete_distributors'
        ])->delete();
    }
};
