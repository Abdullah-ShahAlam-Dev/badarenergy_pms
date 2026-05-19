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
        Schema::table('gate_pass_items', function (Blueprint $table) {
            $table->unsignedInteger('product_id')->nullable()->after('gate_pass_request_id');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('set null')->onUpdate('cascade');
            $table->decimal('returned_quantity', 16, 2)->default(0.00)->after('quantity');
            $table->decimal('settled_quantity', 16, 2)->default(0.00)->after('returned_quantity');
        });

        Schema::create('gate_pass_item_returns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('gate_pass_request_id');
            $table->foreign('gate_pass_request_id')->references('id')->on('gate_pass_requests')->onDelete('cascade');
            $table->unsignedBigInteger('gate_pass_item_id');
            $table->foreign('gate_pass_item_id')->references('id')->on('gate_pass_items')->onDelete('cascade');
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->decimal('quantity', 16, 2);
            $table->string('type'); // return, settlement
            $table->string('status'); // returned, damaged, lost, consumed, unreturnable
            $table->text('remarks')->nullable();
            $table->text('settlement_note')->nullable();
            $table->timestamps();
        });

        $module = Module::where('module_name', 'gate_pass')->first();
        if ($module) {
            $permissions = [
                [
                    'name' => 'product.create',
                    'display_name' => 'Create/Add Product (Gate Pass)',
                    'module_id' => $module->id,
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1
                ],
                [
                    'name' => 'product.update',
                    'display_name' => 'Modify Product (Gate Pass)',
                    'module_id' => $module->id,
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1
                ],
                [
                    'name' => 'product.manage',
                    'display_name' => 'Manage Product Master (Gate Pass)',
                    'module_id' => $module->id,
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1
                ]
            ];

            foreach ($permissions as $permissionData) {
                // Ensure duplicate permissions are not created if re-run
                $exists = Permission::where('name', $permissionData['name'])->exists();
                if (!$exists) {
                    $permission = new Permission();
                    $permission->name = $permissionData['name'];
                    $permission->display_name = $permissionData['display_name'];
                    $permission->module_id = $permissionData['module_id'];
                    $permission->allowed_permissions = $permissionData['allowed_permissions'];
                    $permission->is_custom = $permissionData['is_custom'] ?? 0;
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
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $module = Module::where('module_name', 'gate_pass')->first();
        if ($module) {
            Permission::where('module_id', $module->id)
                ->whereIn('name', ['product.create', 'product.update', 'product.manage'])
                ->delete();
        }

        Schema::dropIfExists('gate_pass_item_returns');

        Schema::table('gate_pass_items', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropColumn(['product_id', 'returned_quantity', 'settled_quantity']);
        });
    }
};
