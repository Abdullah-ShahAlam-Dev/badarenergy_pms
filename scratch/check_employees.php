<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\EmployeeDetails;
use Illuminate\Support\Facades\DB;

$users = User::all();
echo "Total Users: " . $users->count() . "\n";

$employees = EmployeeDetails::all();
echo "Total EmployeeDetails: " . $employees->count() . "\n";

echo "\nChecking for users without employee details:\n";
$usersWithoutDetails = User::doesntHave('employee')->get();
echo "Users without employee relationship: " . $usersWithoutDetails->count() . "\n";
foreach ($usersWithoutDetails as $u) {
    echo "ID: {$u->id}, Name: {$u->name}, Email: {$u->email}, Company ID: {$u->company_id}\n";
}

echo "\nChecking for users with mismatching company_id in employee_details:\n";
$mismatched = DB::table('users')
    ->join('employee_details', 'users.id', '=', 'employee_details.user_id')
    ->whereRaw('users.company_id != employee_details.company_id')
    ->select('users.id', 'users.name', 'users.company_id as user_company', 'employee_details.company_id as emp_company')
    ->get();

echo "Mismatched company_id count: " . $mismatched->count() . "\n";
foreach ($mismatched as $m) {
    echo "User ID: {$m->id}, Name: {$m->name}, User Company: {$m->user_company}, Emp Details Company: {$m->emp_company}\n";
}
