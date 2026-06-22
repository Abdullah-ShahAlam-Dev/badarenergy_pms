<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ModuleSetting;

echo "--- MODULE SETTINGS ---\n";
foreach (ModuleSetting::where('status', 'active')->get() as $ms) {
    echo "ID: {$ms->id}, Name: {$ms->module_name}, Type: {$ms->type}, Status: {$ms->status}\n";
}
