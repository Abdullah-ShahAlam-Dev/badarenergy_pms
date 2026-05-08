<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

$userId = 1; // Assuming Admin is 1
$user = User::find($userId);
if (!$user) {
    die("User $userId not found.\n");
}

// Mocking session and auth for the helper functions
auth()->login($user);
session(['user' => $user]);

echo "User: {$user->name} (ID: {$user->id})\n";
echo "Roles: " . implode(', ', user_roles()) . "\n";
echo "Modules: " . implode(', ', user_modules()) . "\n";

$sidebarUserPermissions = sidebar_user_perms();
echo "Sidebar Permissions for Daily Reports:\n";
echo "  - view_daily_report: " . ($sidebarUserPermissions['view_daily_report'] ?? 'MISSING') . "\n";
echo "  - view_all_daily_reports: " . ($sidebarUserPermissions['view_all_daily_reports'] ?? 'MISSING') . "\n";

echo "\nPermission Check via User::permission():\n";
echo "  - view_daily_report: " . $user->permission('view_daily_report') . "\n";
echo "  - view_all_daily_reports: " . $user->permission('view_all_daily_reports') . "\n";

echo "\nRole-based Permissions Check (DB):\n";
$rolePerms = DB::table('permission_role')
    ->join('permissions', 'permissions.id', 'permission_role.permission_id')
    ->join('role_user', 'role_user.role_id', 'permission_role.role_id')
    ->where('role_user.user_id', $user->id)
    ->where('permissions.name', 'like', '%daily_report%')
    ->select('permissions.name', 'permission_role.permission_type_id')
    ->get();

foreach ($rolePerms as $rp) {
    echo "  - {$rp->name}: {$rp->permission_type_id}\n";
}

echo "\nModule Status (DB):\n";
$moduleStatus = DB::table('module_settings')
    ->where('module_name', 'daily_reports')
    ->get();

foreach ($moduleStatus as $ms) {
    echo "  - Type: {$ms->type}, Status: {$ms->status}, Company: {$ms->company_id}\n";
}
