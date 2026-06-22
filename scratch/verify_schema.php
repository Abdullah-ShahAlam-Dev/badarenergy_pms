<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Permission;

echo "--- inventories table ---\n";
$cols = DB::select('SHOW COLUMNS FROM inventories');
foreach ($cols as $c) {
    echo "  {$c->Field}  {$c->Type}  Null={$c->Null}  Key={$c->Key}\n";
}

echo "\n--- inventories indexes ---\n";
$idx = DB::select('SHOW INDEX FROM inventories');
foreach ($idx as $i) {
    echo "  {$i->Key_name}: col={$i->Column_name} unique=" . ($i->Non_unique ? 'no' : 'yes') . "\n";
}

echo "\n--- stock_movements table ---\n";
$cols = DB::select('SHOW COLUMNS FROM stock_movements');
foreach ($cols as $c) {
    echo "  {$c->Field}  {$c->Type}  Null={$c->Null}  Key={$c->Key}\n";
}

echo "\n--- Seeded permissions ---\n";
$names = ['view_inventory', 'adjust_inventory', 'view_warehouses', 'add_warehouses', 'edit_warehouses', 'delete_warehouses'];
$perms = Permission::whereIn('name', $names)->get();
foreach ($perms as $p) {
    echo "  [{$p->id}] {$p->name} => module_id={$p->module_id}\n";
}

echo "\n--- inventories + stock_movements actual data ---\n";
$invRows = DB::table('inventories')->get();
foreach ($invRows as $row) {
    echo "  inventory id={$row->id} product_id={$row->product_id} warehouse_id={$row->warehouse_id} qty={$row->quantity}\n";
}
$movRows = DB::table('stock_movements')->get();
foreach ($movRows as $row) {
    echo "  movement id={$row->id} type={$row->type} qty={$row->quantity} balance_after={$row->balance_after} remarks={$row->remarks}\n";
}
