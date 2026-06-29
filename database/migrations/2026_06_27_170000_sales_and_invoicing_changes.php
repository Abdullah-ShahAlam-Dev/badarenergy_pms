<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Module;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\Role;
use App\Models\User;
use App\Models\UserPermission;
use App\Models\Company;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Modify invoices.status column from enum to string to support 'pending_approval'
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('status')->default('pending_approval')->change();
        });

        // 2. Create delivery_orders table
        Schema::create('delivery_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('company_id')->nullable()->index();
            $table->unsignedInteger('invoice_id')->unique()->index();
            $table->date('issue_date');
            $table->unsignedInteger('dispatcher_id')->nullable()->index();
            $table->enum('status', ['pending', 'dispatched', 'delivered', 'cancelled'])->default('pending')->index();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            $table->foreign('dispatcher_id')->references('id')->on('users')->onDelete('set null');
        });

        // 3. Register 'approve_invoices' permission
        $module = Module::where('module_name', 'invoices')->first();
        if ($module) {
            $permission = Permission::firstOrCreate(
                ['name' => 'approve_invoices'],
                [
                    'display_name' => 'Approve Invoices',
                    'is_custom' => 1,
                    'module_id' => $module->id,
                    'allowed_permissions' => Permission::ALL_NONE
                ]
            );

            $companies = Company::select('id')->get();
            $adminUsers = User::allAdmins();

            // Assign permission to admin roles across all companies
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

            // Assign directly to admin users
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Delete permission and its role/user associations
        $permission = Permission::where('name', 'approve_invoices')->first();
        if ($permission) {
            PermissionRole::where('permission_id', $permission->id)->delete();
            UserPermission::where('permission_id', $permission->id)->delete();
            $permission->delete();
        }

        // 2. Drop delivery_orders table
        Schema::dropIfExists('delivery_orders');

        // 3. Restore invoices.status column back to enum if desired (can keep as string since it's more flexible)
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('status')->default('unpaid')->change();
        });
    }
};
