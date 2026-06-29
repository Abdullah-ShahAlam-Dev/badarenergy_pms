<?php

use App\Models\Company;
use App\Models\Module;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\Role;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $module = Module::where('module_name', 'invoices')->first();

        if ($module) {
            $permissionsData = [
                ['name' => 'view_daily_sales', 'display_name' => 'View Daily Sales Report'],
                ['name' => 'view_weekly_sales', 'display_name' => 'View Weekly Sales Report'],
                ['name' => 'view_monthly_sales', 'display_name' => 'View Monthly Sales Report'],
                ['name' => 'view_dealer_sales', 'display_name' => 'View Dealer Wise Sales Report'],
                ['name' => 'view_product_sales', 'display_name' => 'View Product Wise Sales Report'],
                ['name' => 'view_model_sales', 'display_name' => 'View Model Wise Sales Report'],
                ['name' => 'view_location_sales', 'display_name' => 'View Location Wise Sales Report'],
                ['name' => 'view_salesperson_sales', 'display_name' => 'View Salesperson Wise Sales Report'],
                ['name' => 'view_finance_collection', 'display_name' => 'View Finance Collection Report'],
                ['name' => 'view_recovery_report', 'display_name' => 'View Recovery Report'],
                ['name' => 'view_outstanding_report', 'display_name' => 'View Outstanding Report'],
                ['name' => 'view_cashflow_report', 'display_name' => 'View Cash Flow Report'],
            ];

            $companies = Company::all();
            $adminUsers = User::allAdmins();

            foreach ($permissionsData as $permData) {
                $permission = Permission::firstOrCreate(
                    ['name' => $permData['name']],
                    [
                        'display_name' => $permData['display_name'],
                        'is_custom' => 1,
                        'module_id' => $module->id,
                        'allowed_permissions' => '{"all":4, "none":5}'
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
     */
    public function down(): void
    {
        $names = [
            'view_daily_sales', 'view_weekly_sales', 'view_monthly_sales',
            'view_dealer_sales', 'view_product_sales', 'view_model_sales',
            'view_location_sales', 'view_salesperson_sales', 'view_finance_collection',
            'view_recovery_report', 'view_outstanding_report', 'view_cashflow_report'
        ];

        $perms = Permission::whereIn('name', $names)->get();

        foreach ($perms as $perm) {
            PermissionRole::where('permission_id', $perm->id)->delete();
            UserPermission::where('permission_id', $perm->id)->delete();
            $perm->delete();
        }
    }
};
