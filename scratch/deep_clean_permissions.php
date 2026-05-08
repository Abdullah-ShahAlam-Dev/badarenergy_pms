<?php

use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

// 1. Delete all user-level overrides for Daily Reports
$deletedPerms = UserPermission::whereHas('permission', function($q){ 
    $q->where('name', 'like', '%daily_report%'); 
})->delete();

echo "Deleted $deletedPerms user-level overrides for daily reports.\n";

// 2. Reset customised_permissions for all users to ensure Role sync works correctly
$updatedUsers = User::where('customised_permissions', 1)->update(['customised_permissions' => 0]);
echo "Reset customised_permissions to 0 for $updatedUsers users.\n";

// 3. Clear all caches
Cache::flush();
echo "All caches cleared.\n";

echo "The system is now fully synchronized with Role-based permissions. No more 'stuck' user overrides.\n";
