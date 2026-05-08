<?php

use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\Role;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

// 1. Get IDs
$adminRole = Role::where('name', 'admin')->first();
$permissions = Permission::where('name', 'like', '%daily_report%')->get();
$allPermissionType = DB::table('permission_types')->where('name', 'all')->first();

if (!$adminRole || $permissions->isEmpty() || !$allPermissionType) {
    die("Missing data: Admin Role: " . ($adminRole ? 'OK' : 'FAIL') . ", Permissions: " . ($permissions->count()) . ", All Type: " . ($allPermissionType ? 'OK' : 'FAIL') . "\n");
}

echo "Assigning permissions to Admin Role (ID: {$adminRole->id})\n";

foreach ($permissions as $permission) {
    // 2. Update PermissionRole
    PermissionRole::updateOrCreate(
        ['role_id' => $adminRole->id, 'permission_id' => $permission->id],
        ['permission_type_id' => $allPermissionType->id]
    );
    echo "  - Permission '{$permission->name}' assigned to Admin Role.\n";

    // 3. Update UserPermission for all Admin users
    $admins = User::withRole('admin')->get();
    foreach ($admins as $admin) {
        UserPermission::updateOrCreate(
            ['user_id' => $admin->id, 'permission_id' => $permission->id],
            ['permission_type_id' => $allPermissionType->id]
        );
        
        // Clear Cache
        Cache::forget('permission-' . $permission->name . '-' . $admin->id);
        Cache::forget('permission-id-' . $permission->name . '-' . $admin->id);
    }
}

echo "Seeding completed successfully for " . $admins->count() . " admin users.\n";
