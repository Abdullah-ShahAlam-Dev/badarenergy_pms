<?php
/*
 * WorkSuite PMS - WorkOrder Management Module
 * Migration 1: Register module & seed permissions
 */

use Illuminate\Database\Migrations\Migration;
use App\Models\Module;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\Role;

return new class extends Migration
{
    public function up()
    {
        // ── Register Module ────────────────────────────────────────────────────
        $module = new Module();
        $module->module_name  = 'work_order';
        $module->description  = 'Work Order Management System for issuing vendor work orders against events.';
        $module->save();

        $permissions = [
            // ── Vendor permissions ─────────────────────────────────────────────
            [
                'name'                => 'add_vendor',
                'display_name'        => 'Add Vendor',
                'allowed_permissions' => Permission::ALL_NONE,
                'is_custom'           => 1,
            ],
            [
                'name'                => 'view_vendor',
                'display_name'        => 'View Vendor',
                'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                'is_custom'           => 1,
            ],
            [
                'name'                => 'edit_vendor',
                'display_name'        => 'Edit Vendor',
                'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                'is_custom'           => 1,
            ],
            [
                'name'                => 'delete_vendor',
                'display_name'        => 'Delete Vendor',
                'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                'is_custom'           => 1,
            ],

            // ── Work Order permissions ─────────────────────────────────────────
            [
                'name'                => 'add_work_order',
                'display_name'        => 'Add Work Order',
                'allowed_permissions' => Permission::ALL_NONE,
                'is_custom'           => 0,
            ],
            [
                'name'                => 'view_work_order',
                'display_name'        => 'View Work Order',
                'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                'is_custom'           => 0,
            ],
            [
                'name'                => 'edit_work_order',
                'display_name'        => 'Edit Work Order',
                'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                'is_custom'           => 0,
            ],
            [
                'name'                => 'delete_work_order',
                'display_name'        => 'Delete Work Order',
                'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                'is_custom'           => 0,
            ],
            [
                'name'                => 'approve_work_order',
                'display_name'        => 'Approve Work Order',
                'allowed_permissions' => Permission::ALL_NONE,
                'is_custom'           => 1,
            ],
            [
                'name'                => 'manage_approval_mappings',
                'display_name'        => 'Manage Approval Mappings',
                'allowed_permissions' => Permission::ALL_NONE,
                'is_custom'           => 1,
            ],
            [
                'name'                => 'view_work_order_reports',
                'display_name'        => 'View Work Order Reports',
                'allowed_permissions' => Permission::ALL_NONE,
                'is_custom'           => 1,
            ],
            [
                'name'                => 'manage_vendor_payments',
                'display_name'        => 'Manage Vendor Payments',
                'allowed_permissions' => Permission::ALL_NONE,
                'is_custom'           => 1,
            ],
        ];

        foreach ($permissions as $permissionData) {
            $permission                       = new Permission();
            $permission->name                 = $permissionData['name'];
            $permission->display_name         = $permissionData['display_name'];
            $permission->module_id            = $module->id;
            $permission->allowed_permissions  = $permissionData['allowed_permissions'];
            $permission->is_custom            = $permissionData['is_custom'];
            $permission->save();

            // Assign all permissions to admin role by default
            $adminRole = Role::where('name', 'admin')->first();
            if ($adminRole) {
                $permissionRole                 = new PermissionRole();
                $permissionRole->permission_id  = $permission->id;
                $permissionRole->role_id        = $adminRole->id;
                $permissionRole->permission_type_id = 4; // 'all'
                $permissionRole->save();
            }
        }
    }

    public function down()
    {
        $module = Module::where('module_name', 'work_order')->first();
        if ($module) {
            Permission::where('module_id', $module->id)->delete();
            $module->delete();
        }
    }
};
