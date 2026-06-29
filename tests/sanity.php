<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Facades\WorkflowConfig;
use App\Models\ErpWorkflowSetting;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\ProductSerial;
use App\Models\Inventory;
use App\Models\StockMovement;
use App\Models\SerialTransaction;
use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderLine;
use App\Models\Invoice;
use App\Models\User;
use App\Services\SerialGeneratorService;
use App\Services\BarcodeGeneratorService;
use App\Services\DeliveryOrderService;
use App\Services\InvoiceInventoryService;
use App\Enums\SerialStatus;
use App\Enums\DeliveryOrderStatus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

echo "==================================================\n";
echo "       RUNNING FULL PHASE 3 SANITY TESTS          \n";
echo "==================================================\n";

DB::beginTransaction();

try {
    // 1. Resolve or create test company environment
    $companyId = 1;

    // Resolve a valid user for client_id foreign key
    $client = User::first();
    if (!$client) {
        // Create a dummy user to satisfy foreign key constraint if none exist
        $client = User::create([
            'name' => 'Sanity Test Client',
            'email' => 'client@sanity.com',
            'password' => bcrypt('password'),
            'company_id' => $companyId,
        ]);
    }

    // 2. Create test warehouse and product
    $warehouse = Warehouse::firstOrCreate([
        'company_id' => $companyId,
        'name' => 'Integration Test Warehouse',
    ], [
        'code' => 'ITW-01',
        'type' => 'warehouse',
        'address' => 'Sanity Test Plot',
        'is_active' => true,
    ]);

    $product = Product::firstOrCreate([
        'company_id' => $companyId,
        'name' => 'Integration Test Battery 12V',
    ], [
        'price' => 12000.00,
        'allow_purchase' => true,
    ]);
    $product->update(['is_serialized' => true]);

    // Seed/Reset inventory quantity to 10
    $inventory = Inventory::updateOrCreate([
        'company_id' => $companyId,
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
    ], [
        'quantity' => 10.00,
        'quantity_faulty' => 0.00,
        'quantity_in_transit' => 0.00,
        'average_cost' => 8000.00,
    ]);

    // Create serials
    $serial1 = ProductSerial::create([
        'company_id' => $companyId,
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'serial_number' => 'SANITY-0001',
        'status' => SerialStatus::AVAILABLE->value,
    ]);

    $serial2 = ProductSerial::create([
        'company_id' => $companyId,
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'serial_number' => 'SANITY-0002',
        'status' => SerialStatus::AVAILABLE->value,
    ]);

    // ==================================================
    // TEST 1: LEGACY INVOICE-DRIVEN FLOW (trigger = invoice_approval)
    // ==================================================
    echo "\n[Test 1] Executing Legacy Invoice Flow...\n";
    WorkflowConfig::set('inventory', 'stock_out_trigger', 'invoice_approval', $companyId);

    $invoice = Invoice::create([
        'company_id' => $companyId,
        'client_id' => $client->id,
        'warehouse_id' => $warehouse->id,
        'invoice_number' => 88811101, // Integer numeric invoice number
        'issue_date' => now(),
        'due_date' => now()->addDays(30),
        'status' => 'unpaid',
        'sub_total' => 24000.00,
        'total' => 24000.00,
    ]);

    $itemsData = [
        [
            'product_id' => $product->id,
            'quantity' => 2.00,
            'serials' => ['SANITY-0001', 'SANITY-0002'],
        ]
    ];

    $invService = resolve(InvoiceInventoryService::class);
    $invService->syncInvoiceInventory($invoice, $itemsData);

    // Verify stock is reduced to 8
    $inventory->refresh();
    echo " -> Stock after invoice approved: " . $inventory->quantity . " (Expected: 8.00)\n";
    if ($inventory->quantity != 8.00) throw new Exception("Legacy stock out deduction failed!");

    // Verify serials status is sold
    echo " -> Serial 1 status: " . $serial1->fresh()->status . " (Expected: sold)\n";
    if ($serial1->fresh()->status !== 'sold') throw new Exception("Legacy serial sold transition failed!");

    // Revert and check stock returned to 10
    $invService->revertInvoiceInventory($invoice);
    $inventory->refresh();
    echo " -> Stock after invoice reverted: " . $inventory->quantity . " (Expected: 10.00)\n";
    if ($inventory->quantity != 10.00) throw new Exception("Legacy stock out rollback failed!");

    // ==================================================
    // TEST 2: DECOUPLED LOGISTICS FLOW (trigger = delivery_order_dispatch)
    // ==================================================
    echo "\n[Test 2] Executing Decoupled Logistics Flow (Feature Flag ON)...\n";
    WorkflowConfig::set('inventory', 'stock_out_trigger', 'delivery_order_dispatch', $companyId);

    // 1. Create DO
    $do = DeliveryOrder::create([
        'company_id' => $companyId,
        'source_type' => 'sales',
        'status' => DeliveryOrderStatus::APPROVED->value,
        'issue_date' => now(),
        'warehouse_id' => $warehouse->id,
    ]);

    $line = DeliveryOrderLine::create([
        'delivery_order_id' => $do->id,
        'product_id' => $product->id,
        'quantity_requested' => 2.00,
        'quantity_dispatched' => 0.00,
    ]);

    $doService = resolve(DeliveryOrderService::class);

    // 2. Reserve Serials
    $doService->reserveSerials($do, [
        $line->id => [$serial1->id, $serial2->id]
    ]);
    echo " -> Serial 1 status after reservation: " . $serial1->fresh()->status . " (Expected: reserved)\n";
    if ($serial1->fresh()->status !== 'reserved') throw new Exception("Decoupled serial reservation failed!");

    // 3. Dispatch DO
    $doService->dispatch($do);
    echo " -> DO status after dispatch: " . $do->fresh()->status . " (Expected: dispatched)\n";
    if ($do->fresh()->status !== 'dispatched') throw new Exception("Decoupled DO status dispatch update failed!");

    // 4. Verify stock deducted at DO level
    $inventory->refresh();
    echo " -> Stock after DO dispatched: " . $inventory->quantity . " (Expected: 8.00)\n";
    if ($inventory->quantity != 8.00) throw new Exception("Decoupled DO stock deduction failed!");

    // Verify serials status transitioned to dispatched
    echo " -> Serial 1 status after DO dispatched: " . $serial1->fresh()->status . " (Expected: dispatched)\n";
    if ($serial1->fresh()->status !== 'dispatched') throw new Exception("Decoupled serial status dispatch update failed!");

    // Verify stock movement and serial transactions exist for DO
    $doMovement = StockMovement::where('reference_type', 'delivery_order')->where('reference_id', $do->id)->first();
    echo " -> DO StockMovement reference type: " . ($doMovement ? $doMovement->reference_type : 'None') . " (Expected: delivery_order)\n";
    if (!$doMovement) throw new Exception("DO StockMovement log creation failed!");

    // Check transaction was logged under dispatch event
    $doSerialTx = SerialTransaction::where('source_document_type', 'delivery_order')->where('source_document_id', $do->id)->where('event_type', 'dispatch')->first();
    echo " -> DO SerialTransaction reference: " . ($doSerialTx ? $doSerialTx->event_type : 'None') . " (Expected: dispatch)\n";
    if (!$doSerialTx) throw new Exception("DO SerialTransaction log creation failed!");

    // 5. Create Sales Invoice and link it to DO
    $invoice2 = Invoice::create([
        'company_id' => $companyId,
        'client_id' => $client->id,
        'warehouse_id' => $warehouse->id,
        'invoice_number' => 88811102, // Integer numeric invoice number
        'issue_date' => now(),
        'due_date' => now()->addDays(30),
        'status' => 'unpaid',
        'sub_total' => 24000.00,
        'total' => 24000.00,
    ]);
    $do->update(['invoice_id' => $invoice2->id]);

    // Approve invoice (trigger syncInvoiceInventory)
    $invService->syncInvoiceInventory($invoice2, $itemsData);

    // 6. PROVE NO DOUBLE STOCK DEDUCTION OCCURS
    $inventory->refresh();
    echo " -> Stock after matching Invoice approved: " . $inventory->quantity . " (Expected: 8.00 - NO double deduction!)\n";
    if ($inventory->quantity != 8.00) throw new Exception("DOUBLE STOCK DEDUCTION DETECTED!");

    // Verify serials status transitions to sold
    echo " -> Serial 1 status after matching Invoice approved: " . $serial1->fresh()->status . " (Expected: sold)\n";
    if ($serial1->fresh()->status !== 'sold') throw new Exception("Decoupled matching invoice serial transition failed!");

    // Revert dispatch and check stock returned to 10
    $doService->cancelDispatch($do);
    $inventory->refresh();
    echo " -> Stock after DO dispatch cancelled: " . $inventory->quantity . " (Expected: 10.00)\n";
    if ($inventory->quantity != 10.00) throw new Exception("Decoupled DO cancellation stock restoration failed!");

    // ==================================================
    // TEST 3: WMS RECEIVING ENGINE (Sprint 3)
    // ==================================================
    echo "\n[Test 3] Executing Stock Intake Voucher Receiving Engine...\n";
    
    // Create test batch
    $batch = \App\Models\ProductBatch::create([
        'company_id' => $companyId,
        'product_id' => $product->id,
        'batch_number' => 'BATCH-2026-X',
        'manufacturing_date' => now(),
        'expiry_date' => now()->addYears(2),
    ]);

    // Scenario A: Auto-Approval (setting approvals.stock_intake = false)
    WorkflowConfig::set('approvals', 'stock_intake', false, $companyId);
    
    $intakeData = [
        'warehouse_id' => $warehouse->id,
        'intake_date' => now()->format('Y-m-d'),
        'shipment_id' => null,
        'remarks' => 'Sanity receiving test autocommitted',
    ];

    $itemsData = [
        [
            'product_id' => $product->id,
            'quantity_declared' => 5.00,
            'quantity_received' => 5.00,
            'unit_cost' => 9000.00,
            'batch_id' => $batch->id,
        ]
    ];

    $intakeService = resolve(\App\Services\StockIntakeService::class);
    $voucher = $intakeService->createVoucher($intakeData, $itemsData);

    echo " -> Voucher 1 Auto-Generated Number: " . $voucher->voucher_number . "\n";
    echo " -> Voucher 1 Status: " . $voucher->status . " (Expected: completed)\n";
    if ($voucher->status !== 'completed') throw new Exception("Voucher auto-approval failed when approval setting is OFF!");

    // Verify inventory increased (10 starting + 5 received = 15)
    $inventory->refresh();
    echo " -> Stock after intake voucher completed: " . $inventory->quantity . " (Expected: 15.00)\n";
    if ($inventory->quantity != 15.00) throw new Exception("Inventory increment failed on intake approval!");

    // Verify WAC calculation (starting: 10 qty at 8000 cost = 80000; received: 5 qty at 9000 cost = 45000; total: 125000 / 15 = 8333.33)
    echo " -> Calculated WAC: " . $inventory->average_cost . " (Expected: ~8333.33)\n";
    if (abs($inventory->average_cost - 8333.33) > 1.00) throw new Exception("Weighted Average Cost calculation incorrect!");

    // Verify 5 serial numbers generated
    $serialCount = \App\Models\ProductSerial::where('intake_voucher_id', $voucher->id)->count();
    echo " -> Generated serials count: " . $serialCount . " (Expected: 5)\n";
    if ($serialCount !== 5) throw new Exception("Sequential serial generation failed on intake approval!");

    // Verify stock movement logged
    $movement = StockMovement::where('reference_type', 'stock_intake_voucher')->where('reference_id', $voucher->id)->first();
    echo " -> StockMovement logged: " . ($movement ? $movement->type : 'None') . " (Expected: in)\n";
    if (!$movement || $movement->type !== 'in') throw new Exception("StockMovement logging failed on intake!");

    // Verify serial transactions logged
    $txCount = SerialTransaction::where('source_document_type', 'stock_intake_voucher')->where('source_document_id', $voucher->id)->count();
    echo " -> SerialTransaction logs count: " . $txCount . " (Expected: 5)\n";
    if ($txCount !== 5) throw new Exception("SerialTransaction history logging failed!");

    // Scenario B: Supervisor Approval required (setting approvals.stock_intake = true)
    WorkflowConfig::set('approvals', 'stock_intake', true, $companyId);
    
    $voucher2 = $intakeService->createVoucher($intakeData, $itemsData);
    echo " -> Voucher 2 Status: " . $voucher2->status . " (Expected: pending)\n";
    if ($voucher2->status !== 'pending') throw new Exception("Voucher should be in pending status when approval setting is ON!");

    // Verify inventory did NOT increase yet (still 15)
    $inventory->refresh();
    echo " -> Stock before voucher 2 approved: " . $inventory->quantity . " (Expected: 15.00)\n";
    if ($inventory->quantity != 15.00) throw new Exception("Stock increased prematurely for pending voucher!");

    // Approve voucher
    $intakeService->approveVoucher($voucher2);
    echo " -> Voucher 2 Status after approval: " . $voucher2->fresh()->status . " (Expected: completed)\n";
    if ($voucher2->fresh()->status !== 'completed') throw new Exception("Voucher status update failed on approval!");

    // Verify inventory increased (15 starting + 5 received = 20)
    $inventory->refresh();
    echo " -> Stock after voucher 2 approved: " . $inventory->quantity . " (Expected: 20.00)\n";
    if ($inventory->quantity != 20.00) throw new Exception("Inventory increment failed on manual voucher approval!");


    echo "\n=== ALL INTEGRATION VERIFICATION TESTS PASSED SUCCESSFULLY! ===\n";

} catch (Exception $e) {
    echo "\n❌ SANITY TEST FAILED: " . $e->getMessage() . "\n";
} finally {
    // Always roll back database transaction to avoid polluting database state
    DB::rollBack();
    echo "Database changes safely rolled back.\n";
}
