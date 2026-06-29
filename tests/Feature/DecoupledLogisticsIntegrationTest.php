<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\ProductSerial;
use App\Models\Invoice;
use App\Models\InvoiceItems;
use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderLine;
use App\Models\DeliveryOrderLineSerial;
use App\Models\Inventory;
use App\Models\StockMovement;
use App\Models\SerialTransaction;
use App\Models\DealerLedger;
use App\Services\DeliveryOrderService;
use App\Services\InvoiceInventoryService;
use App\Facades\WorkflowConfig;
use App\Enums\SerialStatus;
use App\Enums\DeliveryOrderStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DecoupledLogisticsIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected Warehouse $warehouse;
    protected Product $product;
    protected ProductSerial $serial1;
    protected ProductSerial $serial2;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create company
        $this->company = Company::create([
            'company_name' => 'Badar Energy HO',
            'company_email' => 'ho@badar.com',
            'company_phone' => '12345678',
            'website' => 'badar.com',
            'address' => 'Karachi HQ',
            'timezone' => 'UTC',
            'locale' => 'en',
        ]);

        // 2. Create warehouse
        $this->warehouse = Warehouse::create([
            'company_id' => $this->company->id,
            'name' => 'Karachi Main Depot',
            'code' => 'KHI-01',
            'type' => 'warehouse',
            'address' => 'Plot 4, Sector 15',
            'is_active' => true,
        ]);

        // 3. Create product (serialized)
        $this->product = Product::create([
            'company_id' => $this->company->id,
            'name' => 'BE-12V-100AH Battery',
            'price' => 25000.00,
            'allow_purchase' => true,
        ]);
        // Worksuite specific column check mock if is_serialized doesn't exist
        $this->product->update(['is_serialized' => true]);

        // 4. Seed initial inventory quantities
        Inventory::create([
            'company_id' => $this->company->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 10.00,
            'quantity_faulty' => 0.00,
            'quantity_in_transit' => 0.00,
            'average_cost' => 15000.00,
        ]);

        // 5. Create serials
        $this->serial1 = ProductSerial::create([
            'company_id' => $this->company->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'serial_number' => 'BE-0001',
            'status' => SerialStatus::AVAILABLE->value,
        ]);

        $this->serial2 = ProductSerial::create([
            'company_id' => $this->company->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'serial_number' => 'BE-0002',
            'status' => SerialStatus::AVAILABLE->value,
        ]);
    }

    /** @test */
    public function it_executes_legacy_flow_cleanly_when_trigger_is_invoice_approval()
    {
        // 1. Force stock_out_trigger to legacy flow
        WorkflowConfig::set('inventory', 'stock_out_trigger', 'invoice_approval', $this->company->id);

        // 2. Create and approve invoice
        $invoice = Invoice::create([
            'company_id' => $this->company->id,
            'client_id' => 999, // Dummy dealer ID
            'warehouse_id' => $this->warehouse->id,
            'invoice_number' => 'INV-001',
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'status' => 'unpaid',
            'sub_total' => 50000.00,
            'total' => 50000.00,
        ]);

        $itemsData = [
            [
                'product_id' => $this->product->id,
                'quantity' => 2.00,
                'serials' => ['BE-0001', 'BE-0002'],
            ]
        ];

        $service = resolve(InvoiceInventoryService::class);
        $service->syncInvoiceInventory($invoice, $itemsData);

        // 3. Verify stock was deducted at invoice level
        $inventory = Inventory::where('product_id', $this->product->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->first();
        $this->assertEquals(8.00, $inventory->quantity); // 10.00 - 2.00 = 8.00

        // 4. Verify serial status updated to sold
        $this->assertEquals(SerialStatus::SOLD->value, $this->serial1->fresh()->status);
        $this->assertEquals(SerialStatus::SOLD->value, $this->serial2->fresh()->status);

        // 5. Verify stock movement was created
        $movementCount = StockMovement::where('reference_type', 'invoice')
            ->where('reference_id', $invoice->id)
            ->count();
        $this->assertEquals(1, $movementCount);

        // 6. Revert invoice and verify stock returned to normal
        $service->revertInvoiceInventory($invoice);
        $this->assertEquals(10.00, $inventory->fresh()->quantity);
        $this->assertEquals(SerialStatus::AVAILABLE->value, $this->serial1->fresh()->status);
    }

    /** @test */
    public function it_executes_decoupled_flow_without_double_stock_deduction_under_feature_flag()
    {
        // 1. Force stock_out_trigger to decoupled logistics flow
        WorkflowConfig::set('inventory', 'stock_out_trigger', 'delivery_order_dispatch', $this->company->id);

        // 2. Create Delivery Order
        $do = DeliveryOrder::create([
            'company_id' => $this->company->id,
            'source_type' => 'sales',
            'status' => DeliveryOrderStatus::APPROVED->value,
            'issue_date' => now(),
            'warehouse_id' => $this->warehouse->id,
        ]);

        // Add line items
        $line = DeliveryOrderLine::create([
            'delivery_order_id' => $do->id,
            'product_id' => $this->product->id,
            'quantity_requested' => 2.00,
            'quantity_dispatched' => 0.00,
        ]);

        // 3. Reserve Serials using DeliveryOrderService
        $doService = resolve(DeliveryOrderService::class);
        $doService->reserveSerials($do, [
            $line->id => [$this->serial1->id, $this->serial2->id]
        ]);

        // Verify serial status transitions to reserved
        $this->assertEquals(SerialStatus::RESERVED->value, $this->serial1->fresh()->status);

        // 4. Dispatch the Delivery Order
        $doService->dispatch($do);

        // Verify DO status updated to dispatched
        $this->assertEquals(DeliveryOrderStatus::DISPATCHED->value, $do->fresh()->status);

        // Verify stock deducted at DO level
        $inventory = Inventory::where('product_id', $this->product->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->first();
        $this->assertEquals(8.00, $inventory->quantity); // 10.00 - 2.00 = 8.00

        // Verify serial status transitions to dispatched
        $this->assertEquals(SerialStatus::DISPATCHED->value, $this->serial1->fresh()->status);

        // Verify DO stock movement was created
        $doMovement = StockMovement::where('reference_type', 'delivery_order')
            ->where('reference_id', $do->id)
            ->first();
        $this->assertNotNull($doMovement);
        $this->assertEquals('out', $doMovement->type);

        // 5. Approve Sales Invoice linking to this DO
        $invoice = Invoice::create([
            'company_id' => $this->company->id,
            'client_id' => 999,
            'warehouse_id' => $this->warehouse->id,
            'invoice_number' => 'INV-002',
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'status' => 'unpaid',
            'sub_total' => 50000.00,
            'total' => 50000.00,
        ]);

        // Link DO to invoice
        $do->update(['invoice_id' => $invoice->id]);

        $itemsData = [
            [
                'product_id' => $this->product->id,
                'quantity' => 2.00,
                'serials' => ['BE-0001', 'BE-0002'],
            ]
        ];

        // Sync Invoice WMS
        $invService = resolve(InvoiceInventoryService::class);
        $invService->syncInvoiceInventory($invoice, $itemsData);

        // 6. PROVE NO DOUBLE DEDUCTION OCCURS
        $this->assertEquals(8.00, $inventory->fresh()->quantity); // Quantity remains 8.00!

        // Prove no invoice stock movement was created (only DO movement exists)
        $invoiceMovements = StockMovement::where('reference_type', 'invoice')
            ->where('reference_id', $invoice->id)
            ->count();
        $this->assertEquals(0, $invoiceMovements); // Zero invoice-level deductions!

        // Prove serial status updated to sold
        $this->assertEquals(SerialStatus::SOLD->value, $this->serial1->fresh()->status);
        $this->assertEquals($invoice->id, $this->serial1->fresh()->invoice_id);
    }
}
