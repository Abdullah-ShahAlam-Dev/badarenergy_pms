<?php

use App\Models\UserPermission;
use Illuminate\Support\Facades\Cache;
use App\Models\User;

$deleted = UserPermission::whereHas('permission', function($q){ 
    $q->where('name', 'like', '%daily_report%'); 
})->delete();

echo "Deleted $deleted user-level overrides for daily reports.\n";

// Clear all permission caches for all users to be safe
$users = User::all();
foreach ($users as $user) {
    Cache::forget('sidebar_user_perms_' . $user->id);
}
// Also clear general cache
Cache::flush();

echo "Caches cleared. Permissions should now fallback to Roles dynamically.\n";
