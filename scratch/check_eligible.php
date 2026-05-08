<?php

use App\Models\User;
use Illuminate\Support\Facades\Cache;

$allEmployees = User::onlyEmployee()->get();
echo "Total employees from scope: " . $allEmployees->count() . "\n";

foreach ($allEmployees as $emp) {
    Cache::forget('permission-add_daily_report-' . $emp->id);
}

$eligibleEmployees = $allEmployees->filter(function ($user) {
    $p = $user->permission('add_daily_report');
    return $p != 'none' && $p != false;
});

echo "Eligible employees: " . $eligibleEmployees->count() . "\n";
foreach ($eligibleEmployees as $ee) {
    echo "  - ID: {$ee->id}, Name: {$ee->name}, Perm: " . $ee->permission('add_daily_report') . "\n";
}
