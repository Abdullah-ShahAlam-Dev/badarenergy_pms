<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

// 1. Get IDs
$adminRole = Role::where('name', 'admin')->first();
$permissions = Permission::where('name', 'like', '%daily_report%')->get();
$nonePermissionType = DB::table('permission_types')->where('name', 'none')->first();

if (!$nonePermissionType || $permissions->isEmpty()) {
    die("Missing data: Permissions: " . ($permissions->count()) . ", None Type: " . ($nonePermissionType ? 'OK' : 'FAIL') . "\n");
}

// 2. Update all roles except Admin
$otherRoles = Role::where('name', '!=', 'admin')->get();
echo "Setting permissions to 'none' for " . $otherRoles->count() . " non-admin roles.\n";

foreach ($otherRoles as $role) {
    foreach ($permissions as $permission) {
        DB::table('permission_role')->updateOrInsert(
            ['role_id' => $role->id, 'permission_id' => $permission->id],
            ['permission_type_id' => $nonePermissionType->id]
        );
    }
    echo "  - Role '{$role->name}' restricted.\n";
}

// 3. Update all users except Admins
$nonAdmins = User::whereDoesntHave('roles', function($q) {
    $q->where('name', 'admin');
})->get();

echo "Setting permissions to 'none' for " . $nonAdmins->count() . " non-admin users.\n";

foreach ($nonAdmins as $user) {
    foreach ($permissions as $permission) {
        UserPermission::updateOrCreate(
            ['user_id' => $user->id, 'permission_id' => $permission->id],
            ['permission_type_id' => $nonePermissionType->id]
        );
        
        // Clear Cache
        Cache::forget('permission-' . $permission->name . '-' . $user->id);
        Cache::forget('permission-id-' . $permission->name . '-' . $user->id);
    }
}

echo "Seeding completed successfully. All non-admin employees are now restricted from Daily Reports.\n";
