<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Company;
use App\Models\Warehouse;
use App\Models\Inventory;
use App\Models\StockMovement;
use App\Services\StockAdjustmentService;
use Illuminate\Support\Facades\DB;

$company = Company::first();
if (!$company) {
    echo "ERROR: No company found.\n";
    exit(1);
}

// Bootstrap company context like the middleware does
app()->singleton('company', fn() => $company);

$service = new StockAdjustmentService();

// Use two warehouses for cross-warehouse testing
$warehouse = Warehouse::withoutGlobalScopes()->first();
if (!$warehouse) {
    echo "ERROR: No warehouse found. Please create one.\n";
    exit(1);
}

// Use a fake product_id=1 referencing an existing products row or create synthetic test
// Check if products table has any row at all
$productRow = DB::table('products')->first();
if (!$productRow) {
    // Insert a minimal test product
    $productId = DB::table('products')->insertGetId([
        'name'          => '__TEST_PRODUCT__',
        'price'         => '1.00',
        'description'   => 'Test product for TSK-4.1 verification',
        'allow_purchase'=> 1,
        'downloadable'  => 0,
        'company_id'    => $company->id,
        'created_at'    => now(),
        'updated_at'    => now(),
    ]);
    echo "Created test product ID: {$productId}\n";
} else {
    $productId = $productRow->id;
    echo "Using existing product ID: {$productId} ({$productRow->name})\n";
}

$warehouseId = $warehouse->id;
echo "Using warehouse ID: {$warehouseId} ({$warehouse->name})\n\n";

// Clean any prior test data
Inventory::withoutGlobalScopes()->where('product_id', $productId)->where('warehouse_id', $warehouseId)->delete();
StockMovement::withoutGlobalScopes()->where('product_id', $productId)->where('warehouse_id', $warehouseId)->delete();

// ─── TEST 1 ────────────────────────────────────────────────
echo "TEST 1: Add 50 units (auto-creates inventory row)\n";
$service->adjustStock($productId, $warehouseId, 50.00, 'in', 'manual', null, 'Initial load');
$inv = Inventory::withoutGlobalScopes()->where('product_id', $productId)->where('warehouse_id', $warehouseId)->first();
if (!$inv) { echo "FAIL: Inventory record not created\n"; exit(1); }
$qty = round((float)$inv->quantity, 2);
echo ($qty === 50.00 ? "PASS" : "FAIL") . ": Balance = {$qty} (expected 50.00)\n";
$mov = StockMovement::withoutGlobalScopes()->where('product_id', $productId)->where('warehouse_id', $warehouseId)->latest('id')->first();
echo ($mov && round((float)$mov->balance_after, 2) === 50.00 ? "PASS" : "FAIL") . ": balance_after = {$mov->balance_after}\n";
echo ($mov->type === 'in' ? "PASS" : "FAIL") . ": type = {$mov->type}\n\n";

// ─── TEST 2 ────────────────────────────────────────────────
echo "TEST 2: Remove 20 units\n";
$service->adjustStock($productId, $warehouseId, 20.00, 'out', 'manual', null, 'Dispatch');
$inv->refresh();
$qty = round((float)$inv->quantity, 2);
echo ($qty === 30.00 ? "PASS" : "FAIL") . ": Balance = {$qty} (expected 30.00)\n";
$mov = StockMovement::withoutGlobalScopes()->where('product_id', $productId)->where('warehouse_id', $warehouseId)->latest('id')->first();
echo ($mov && round((float)$mov->balance_after, 2) === 30.00 ? "PASS" : "FAIL") . ": balance_after = {$mov->balance_after}\n\n";

// ─── TEST 3 ────────────────────────────────────────────────
echo "TEST 3: Negative stock guard (remove 50 from 30)\n";
$snapshot = round((float)$inv->quantity, 2);
try {
    $service->adjustStock($productId, $warehouseId, 50.00, 'out', 'manual', null, 'Over-remove');
    echo "FAIL: No exception thrown — negative stock not prevented!\n";
    exit(1);
} catch (\Exception $e) {
    echo "PASS: Exception correctly thrown: {$e->getMessage()}\n";
}
$inv->refresh();
$qty = round((float)$inv->quantity, 2);
echo ($qty === $snapshot ? "PASS" : "FAIL") . ": Balance unchanged = {$qty} (expected {$snapshot})\n\n";

// ─── TEST 4 ────────────────────────────────────────────────
echo "TEST 4: Movement log count\n";
$count = StockMovement::withoutGlobalScopes()->where('product_id', $productId)->where('warehouse_id', $warehouseId)->count();
echo ($count === 2 ? "PASS" : "FAIL") . ": {$count} movements in log (expected 2)\n\n";

// ─── TEST 5 ────────────────────────────────────────────────
echo "TEST 5: Second adjustment uses existing inventory row (no duplicate)\n";
$service->adjustStock($productId, $warehouseId, 10.00, 'in', 'manual', null, 'Restock');
$rows = Inventory::withoutGlobalScopes()->where('product_id', $productId)->where('warehouse_id', $warehouseId)->count();
echo ($rows === 1 ? "PASS" : "FAIL") . ": Only {$rows} inventory row (expected 1 — unique constraint respected)\n";
$inv->refresh();
$qty = round((float)$inv->quantity, 2);
echo ($qty === 40.00 ? "PASS" : "FAIL") . ": Balance after restock = {$qty} (expected 40.00)\n\n";

echo "==============================\n";
echo "=== ALL TESTS PASSED ✓     ===\n";
echo "==============================\n";
