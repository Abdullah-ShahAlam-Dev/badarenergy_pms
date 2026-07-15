<?php

use App\Models\Company;
use App\Models\Module;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\Role;
use App\Models\User;
use App\Models\UserPermission;
use App\Models\ModuleSetting;
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
        // 1. Create or Find the dedicated Inventory module
        $module = Module::firstOrCreate(
            ['module_name' => 'inventory'],
            [
                'description' => 'Manage physical warehouses, stock intake, transfers, serial tracking, and delivery dispatches.'
            ]
        );

        if ($module) {
            $permissionsData = [
                // --- Primary CRUD Grid Permissions ---
                [
                    'name' => 'add_inventory',
                    'display_name' => 'Add Inventory',
                    'is_custom' => 0,
                    'allowed' => Permission::ALL_NONE
                ],
                [
                    'name' => 'view_inventory',
                    'display_name' => 'View Inventory',
                    'is_custom' => 0,
                    'allowed' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5
                ],
                [
                    'name' => 'edit_inventory',
                    'display_name' => 'Edit Inventory',
                    'is_custom' => 0,
                    'allowed' => Permission::ALL_NONE
                ],
                [
                    'name' => 'delete_inventory',
                    'display_name' => 'Delete Inventory',
                    'is_custom' => 0,
                    'allowed' => Permission::ALL_NONE
                ],

                // --- Custom Sub-Module Toggles ---
                [
                    'name' => 'view_stock_movements',
                    'display_name' => 'View Stock Movements',
                    'is_custom' => 1,
                    'allowed' => Permission::ALL_NONE
                ],
                [
                    'name' => 'manage_warehouses',
                    'display_name' => 'Manage Warehouses',
                    'is_custom' => 1,
                    'allowed' => Permission::ALL_NONE
                ],
                [
                    'name' => 'approve_stock_intake',
                    'display_name' => 'Approve Stock Intake',
                    'is_custom' => 1,
                    'allowed' => Permission::ALL_NONE
                ],
                [
                    'name' => 'approve_stock_transfer',
                    'display_name' => 'Approve Stock Transfer',
                    'is_custom' => 1,
                    'allowed' => Permission::ALL_NONE
                ],
                [
                    'name' => 'manage_serial_numbers',
                    'display_name' => 'Manage Serial Numbers',
                    'is_custom' => 1,
                    'allowed' => Permission::ALL_NONE
                ],
                [
                    'name' => 'adjust_stock',
                    'display_name' => 'Adjust Stock',
                    'is_custom' => 1,
                    'allowed' => Permission::ALL_NONE
                ],
                [
                    'name' => 'manage_dispatch',
                    'display_name' => 'Manage Dispatch',
                    'is_custom' => 1,
                    'allowed' => Permission::ALL_NONE
                ]
            ];

            $companies = Company::select('id')->get();
            $adminUsers = User::allAdmins();

            foreach ($permissionsData as $permData) {
                // Insert permissions
                $permission = Permission::firstOrCreate(
                    ['name' => $permData['name']],
                    [
                        'display_name' => $permData['display_name'],
                        'is_custom' => $permData['is_custom'],
                        'module_id' => $module->id,
                        'allowed_permissions' => $permData['allowed']
                    ]
                );

                // Assign default settings for all companies
                foreach ($companies as $company) {
                    // Admins get ALL (4)
                    $adminRole = Role::where('name', 'admin')
                        ->where('company_id', $company->id)
                        ->first();

                    if ($adminRole) {
                        PermissionRole::firstOrCreate([
                            'permission_id' => $permission->id,
                            'role_id' => $adminRole->id,
                        ], [
                            'permission_type_id' => 4 // All
                        ]);
                    }

                    // Employees get NONE (5) by default, requiring customization
                    $employeeRole = Role::where('name', 'employee')
                        ->where('company_id', $company->id)
                        ->first();

                    if ($employeeRole) {
                        PermissionRole::firstOrCreate([
                            'permission_id' => $permission->id,
                            'role_id' => $employeeRole->id,
                        ], [
                            'permission_type_id' => 5 // None
                        ]);
                    }
                }

                // Assign ALL (4) to admin users directly
                foreach ($adminUsers as $adminUser) {
                    UserPermission::firstOrCreate([
                        'user_id' => $adminUser->id,
                        'permission_id' => $permission->id,
                    ], [
                        'permission_type_id' => 4 // All
                    ]);
                }
            }

            // Move shipments permissions under inventory module
            Permission::whereIn('name', [
                'view_shipments',
                'add_shipments',
                'edit_shipments',
                'delete_shipments'
            ])->update(['module_id' => $module->id]);

            // Register in module_settings for all companies
            $moduleSettings = [];
            $types = ['admin', 'employee', 'client'];
            foreach ($companies as $company) {
                foreach ($types as $type) {
                    $status = ($type === 'client') ? 'deactive' : 'active';
                    $moduleSettings[] = [
                        'company_id' => $company->id,
                        'type' => $type,
                        'module_name' => 'inventory',
                        'status' => $status,
                    ];
                }
            }
            ModuleSetting::insert($moduleSettings);
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
            'view_inventory',
            'add_inventory',
            'edit_inventory',
            'delete_inventory',
            'view_stock_movements',
            'manage_warehouses',
            'approve_stock_intake',
            'approve_stock_transfer',
            'manage_serial_numbers',
            'adjust_stock',
            'manage_dispatch'
        ];

        $perms = Permission::whereIn('name', $names)->get();

        foreach ($perms as $perm) {
            PermissionRole::where('permission_id', $perm->id)->delete();
            UserPermission::where('permission_id', $perm->id)->delete();
            $perm->delete();
        }

        // Restore shipments permissions back under products module
        $productsModule = Module::where('module_name', 'products')->first();
        if ($productsModule) {
            Permission::whereIn('name', [
                'view_shipments',
                'add_shipments',
                'edit_shipments',
                'delete_shipments'
            ])->update(['module_id' => $productsModule->id]);
        }

        // Delete module settings
        ModuleSetting::where('module_name', 'inventory')->delete();

        Module::where('module_name', 'inventory')->delete();
    }
};
