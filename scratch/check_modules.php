<?php
use App\Models\ModuleSetting;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

$modules = ModuleSetting::all();
foreach ($modules as $module) {
    echo $module->module_name . " - " . $module->type . " - " . $module->status . "\n";
}
