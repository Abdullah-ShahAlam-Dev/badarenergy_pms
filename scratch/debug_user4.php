<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

$userId = 4; // One of the 9 people
$user = User::find($userId);
if (!$user) {
    die("User $userId not found.\n");
}

auth()->login($user);
session(['user' => $user]);

echo "User: {$user->name} (ID: {$user->id})\n";
echo "Roles: " . implode(', ', user_roles()) . "\n";

$sidebarUserPermissions = sidebar_user_perms();
echo "Sidebar Permissions for Daily Reports:\n";
echo "  - view_daily_report: " . ($sidebarUserPermissions['view_daily_report'] ?? 'MISSING') . "\n";
echo "  - add_daily_report: " . ($sidebarUserPermissions['add_daily_report'] ?? 'MISSING') . "\n";

echo "\nPermission Check via User::permission():\n";
echo "  - view_daily_report: " . $user->permission('view_daily_report') . "\n";
echo "  - add_daily_report: " . $user->permission('add_daily_report') . "\n";

echo "\nDetailed Role Permissions (DB):\n";
$rolePerms = DB::table('permission_role')
    ->join('permissions', 'permissions.id', 'permission_role.permission_id')
    ->join('role_user', 'role_user.role_id', 'permission_role.role_id')
    ->join('roles', 'roles.id', 'role_user.role_id')
    ->where('role_user.user_id', $user->id)
    ->where('permissions.name', 'like', '%daily_report%')
    ->select('roles.name as role_name', 'permissions.name as perm_name', 'permission_role.permission_type_id')
    ->get();

foreach ($rolePerms as $rp) {
    echo "  - Role: {$rp->role_name}, Permission: {$rp->perm_name}, Type: {$rp->permission_type_id}\n";
}
