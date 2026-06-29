<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\SerialGenerationLog;
use App\Services\SerialGeneratorService;
use App\Facades\WorkflowConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SerialGeneratorTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected Warehouse $warehouse;
    protected Product $product;
    protected SerialGeneratorService $service;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test records
        $this->company = Company::create([
            'company_name' => 'Test Company',
            'company_email' => 'test@company.com',
            'company_phone' => '12345678',
            'website' => 'company.com',
            'address' => 'Test Address',
            'timezone' => 'UTC',
            'locale' => 'en',
        ]);

        $this->warehouse = Warehouse::create([
            'company_id' => $this->company->id,
            'warehouse_name' => 'Test Warehouse',
            'address' => 'Warehouse Address',
            'status' => 'active',
        ]);

        $this->product = Product::create([
            'company_id' => $this->company->id,
            'name' => 'Test Battery Model',
            'price' => 500.00,
            'allow_purchase' => true,
        ]);

        $this->service = resolve(SerialGeneratorService::class);
    }

    /** @test */
    public function it_generates_sequential_serials_successfully()
    {
        // 1. Override serial settings
        WorkflowConfig::set('serial', 'prefix', 'BE', $this->company->id);
        WorkflowConfig::set('serial', 'digit_length', 4, $this->company->id);
        WorkflowConfig::set('serial', 'include_year', false, $this->company->id);

        // 2. Generate 3 serials
        $serials = $this->service->generate(
            $this->product->id,
            $this->warehouse->id,
            $this->company->id,
            3
        );

        $this->assertCount(3, $serials);
        $this->assertEquals('BE0001', $serials[0]->serial_number);
        $this->assertEquals('BE0002', $serials[1]->serial_number);
        $this->assertEquals('BE0003', $serials[2]->serial_number);

        // 3. Confirm sequence counter was updated in DB
        $log = SerialGenerationLog::where('company_id', $this->company->id)
            ->where('prefix', 'BE')
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(3, $log->last_sequence);
    }

    /** @test */
    public function it_supports_year_prefixes_in_generated_serials()
    {
        // 1. Enforce include_year true
        WorkflowConfig::set('serial', 'prefix', 'TEST', $this->company->id);
        WorkflowConfig::set('serial', 'digit_length', 3, $this->company->id);
        WorkflowConfig::set('serial', 'include_year', true, $this->company->id);

        $yearCode = date('y');

        // 2. Generate single serial
        $serials = $this->service->generate(
            $this->product->id,
            $this->warehouse->id,
            $this->company->id,
            1
        );

        $this->assertCount(1, $serials);
        $this->assertEquals("TEST{$yearCode}001", $serials[0]->serial_number);
    }
}
