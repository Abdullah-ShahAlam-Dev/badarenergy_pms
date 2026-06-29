<?php

use App\Models\Company;
use App\Models\Module;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\Role;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('stock_transfer_logs');
        Schema::dropIfExists('transfer_serials');
        Schema::dropIfExists('transfer_items');
        Schema::dropIfExists('stock_transfers');
        Schema::dropIfExists('wms_settings');

        // 1. Create wms_settings Table
        Schema::create('wms_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('company_id')->unsigned();
            $table->boolean('transfer_approval_required')->default(false);
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });

        // 2. Create stock_transfers Table
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->integer('company_id')->unsigned();
            $table->string('transfer_number', 50)->unique();
            $table->string('challan_number', 50)->nullable();
            
            $table->unsignedBigInteger('source_warehouse_id');
            $table->unsignedBigInteger('destination_warehouse_id');
            
            $table->string('status', 30)->default('draft'); // draft, pending_approval, approved, dispatched, in_transit, partially_received, received, cancelled, rejected
            
            // Logistic parameters
            $table->string('vehicle_number', 50)->nullable();
            $table->string('driver_name', 100)->nullable();
            $table->text('remarks')->nullable();
            
            // User actions
            $table->integer('created_by')->unsigned();
            $table->integer('approved_by')->unsigned()->nullable();
            $table->integer('dispatched_by')->unsigned()->nullable();
            $table->integer('received_by')->unsigned()->nullable();
            $table->integer('cancelled_by')->unsigned()->nullable();
            
            // Timestamps for audit
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            
            // Log IPs
            $table->string('created_ip', 45);
            $table->string('approved_ip', 45)->nullable();
            $table->string('dispatched_ip', 45)->nullable();
            $table->string('received_ip', 45)->nullable();
            $table->string('cancelled_ip', 45)->nullable();
            
            $table->string('reason', 255)->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('source_warehouse_id')->references('id')->on('warehouses');
            $table->foreign('destination_warehouse_id')->references('id')->on('warehouses');
            
            $table->index(['company_id', 'status', 'source_warehouse_id', 'destination_warehouse_id'], 'stock_transfers_reporting_idx');
        });

        // 3. Create transfer_items Table
        Schema::create('transfer_items', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('transfer_id')->unsigned();
            $table->unsignedInteger('product_id');
            $table->decimal('quantity', 12, 2)->default(0.00);
            $table->decimal('quantity_received', 12, 2)->default(0.00);
            $table->timestamps();

            $table->foreign('transfer_id')->references('id')->on('stock_transfers')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products');
            
            $table->index(['transfer_id', 'product_id'], 'transfer_items_idx');
        });

        // 4. Create transfer_serials Table
        Schema::create('transfer_serials', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('transfer_item_id')->unsigned();
            $table->unsignedBigInteger('product_serial_id');
            $table->string('status', 30)->default('reserved'); // reserved, dispatched, received, damaged, returned
            $table->timestamp('received_at')->nullable();
            $table->integer('received_by')->unsigned()->nullable();
            $table->timestamps();

            $table->foreign('transfer_item_id')->references('id')->on('transfer_items')->onDelete('cascade');
            $table->foreign('product_serial_id')->references('id')->on('product_serials');
            
            $table->index(['transfer_item_id', 'product_serial_id'], 'transfer_serials_idx');
        });

        // 5. Create stock_transfer_logs Table
        Schema::create('stock_transfer_logs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('transfer_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->string('action', 50);
            $table->string('ip_address', 45);
            $table->text('payload')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->foreign('transfer_id')->references('id')->on('stock_transfers')->onDelete('cascade');
        });

        // 6. Seed WMS Stock Transfer Permissions
        $module = Module::where('module_name', 'invoices')->first();

        if ($module) {
            $permissionsData = [
                ['name' => 'view_stock_transfer', 'display_name' => 'View Stock Transfers'],
                ['name' => 'add_stock_transfer', 'display_name' => 'Add Stock Transfer'],
                ['name' => 'edit_stock_transfer', 'display_name' => 'Edit Stock Transfer'],
                ['name' => 'delete_stock_transfer', 'display_name' => 'Delete Stock Transfer'],
                ['name' => 'approve_stock_transfer', 'display_name' => 'Approve Stock Transfer'],
                ['name' => 'reject_stock_transfer', 'display_name' => 'Reject Stock Transfer'],
                ['name' => 'dispatch_stock_transfer', 'display_name' => 'Dispatch Stock Transfer'],
                ['name' => 'receive_stock_transfer', 'display_name' => 'Receive Stock Transfer'],
                ['name' => 'cancel_stock_transfer', 'display_name' => 'Cancel Stock Transfer'],
                ['name' => 'print_stock_transfer', 'display_name' => 'Print Stock Transfer Challans/GRN'],
                ['name' => 'view_transfer_history', 'display_name' => 'View Transfer History Log'],
                ['name' => 'view_in_transit_stock', 'display_name' => 'View In-Transit Stock Report'],
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

                    // Seed default WMS settings row for each company
                    DB::table('wms_settings')->insertOrIgnore([
                        'company_id' => $company->id,
                        'transfer_approval_required' => 0,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
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
            'view_stock_transfer', 'add_stock_transfer', 'edit_stock_transfer',
            'delete_stock_transfer', 'approve_stock_transfer', 'reject_stock_transfer',
            'dispatch_stock_transfer', 'receive_stock_transfer', 'cancel_stock_transfer',
            'print_stock_transfer', 'view_transfer_history', 'view_in_transit_stock'
        ];

        $perms = Permission::whereIn('name', $names)->get();

        foreach ($perms as $perm) {
            PermissionRole::where('permission_id', $perm->id)->delete();
            UserPermission::where('permission_id', $perm->id)->delete();
            $perm->delete();
        }

        Schema::dropIfExists('stock_transfer_logs');
        Schema::dropIfExists('transfer_serials');
        Schema::dropIfExists('transfer_items');
        Schema::dropIfExists('stock_transfers');
        Schema::dropIfExists('wms_settings');
    }
};
