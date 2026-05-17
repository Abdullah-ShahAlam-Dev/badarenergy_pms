<?php
define('LARAVEL_START', microtime(true));
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Company;
use App\Models\ModuleSetting;
use App\Models\User;

$companies = Company::all();
$moduleName = 'work_order';

foreach ($companies as $company) {
    ModuleSetting::firstOrCreate([
        'company_id' => $company->id,
        'module_name' => $moduleName,
        'type' => 'admin'
    ], [
        'status' => 'active'
    ]);

    ModuleSetting::firstOrCreate([
        'company_id' => $company->id,
        'module_name' => $moduleName,
        'type' => 'employee'
    ], [
        'status' => 'active'
    ]);
}

// Reset permission sync for all users so the sync-user-permissions command will pick them up!
User::query()->update(['permission_sync' => 0]);

echo "SUCCESSFULLY SYNCED MODULE SETTINGS AND RESET PERMISSION SYNC FOR ALL USERS!\n";
