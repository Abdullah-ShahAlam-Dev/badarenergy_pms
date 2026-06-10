<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$users = User::with('roles')->get();
echo "Users roles check:\n";
$noRolesCount = 0;
foreach ($users as $u) {
    $roleNames = $u->roles->pluck('name')->toArray();
    if (empty($roleNames)) {
        $noRolesCount++;
        echo "User ID: {$u->id}, Name: {$u->name}, Email: {$u->email} has NO roles\n";
    }
}
echo "Total users with no roles: $noRolesCount\n";
