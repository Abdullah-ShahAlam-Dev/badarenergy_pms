<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Module;

$modules = Module::all();
foreach ($modules as $m) {
    echo "ID: {$m->id}, Name: {$m->module_name}, Type: {$m->type}\n";
}
