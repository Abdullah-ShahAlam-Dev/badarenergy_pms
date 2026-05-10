<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

// 1. Force the 'Administrator' role (Role 1) to 'None' for Add Daily Report
$adminRole = Role::where('name', 'admin')->first();
$permissions = Permission::where('name', 'like', '%daily_report%')->get();
$nonePermissionType = DB::table('permission_types')->where('name', 'none')->first();
$allPermissionType = DB::table('permission_types')->where('name', 'all')->first();

if ($adminRole && $nonePermissionType) {
    // We only set 'add_daily_report' to none for the Admin role to remove them from reports
    // But we keep 'view_daily_report' as all so they can still see things
    DB::table('permission_role')->updateOrInsert(
        ['role_id' => $adminRole->id, 'permission_id' => Permission::where('name', 'add_daily_report')->first()->id],
        ['permission_type_id' => $nonePermissionType->id]
    );
    echo "Set 'Add Daily Report' to None for Administrator role.\n";
}

// 2. Ensure YOU (ID 1) can still report by giving you a user-level override
$me = User::find(1);
if ($me && $allPermissionType) {
    UserPermission::updateOrCreate(
        ['user_id' => $me->id, 'permission_id' => Permission::where('name', 'add_daily_report')->first()->id],
        ['permission_type_id' => $allPermissionType->id]
    );
    echo "Created user-level override for you (Administrator) so you can still submit reports.\n";
}

// 3. Clear all caches
Cache::flush();
echo "All caches cleared.\n";

echo "Zohair and other admins should now be gone from the report, but you should still be there.\n";
