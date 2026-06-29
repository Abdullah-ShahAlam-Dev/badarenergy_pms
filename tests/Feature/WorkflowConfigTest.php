<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\ErpWorkflowSetting;
use App\Facades\WorkflowConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class WorkflowConfigTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test company
        $this->company = Company::create([
            'company_name' => 'Test Company',
            'company_email' => 'test@company.com',
            'company_phone' => '12345678',
            'website' => 'company.com',
            'address' => 'Test Address',
            'timezone' => 'UTC',
            'locale' => 'en',
        ]);
    }

    /** @test */
    public function it_can_set_and_get_workflow_settings()
    {
        // 1. Get fallback default value
        $costMethod = WorkflowConfig::get('inventory', 'costing_method', 'wac', $this->company->id);
        $this->assertEquals('wac', $costMethod);

        // 2. Set dynamic setting value
        WorkflowConfig::set('inventory', 'costing_method', 'fifo', $this->company->id);

        // 3. Retrieve updated setting value
        $updatedMethod = WorkflowConfig::get('inventory', 'costing_method', 'wac', $this->company->id);
        $this->assertEquals('fifo', $updatedMethod);
    }

    /** @test */
    public function it_supports_has_method_checks()
    {
        // 1. Check a non-existent setting key
        $this->assertFalse(WorkflowConfig::has('inventory', 'non_existent_key', $this->company->id));

        // 2. Add setting and verify check
        WorkflowConfig::set('inventory', 'non_existent_key', 'test_value', $this->company->id);
        $this->assertTrue(WorkflowConfig::has('inventory', 'non_existent_key', $this->company->id));
    }

    /** @test */
    public function it_caches_retrieved_settings_to_prevent_duplicate_queries()
    {
        // 1. Set config setting
        WorkflowConfig::set('inventory', 'serial_tracking', 'true', $this->company->id);

        // Clear settings cache manually first
        WorkflowConfig::clearCache($this->company->id);

        // 2. Check cache exists after retrieval
        $this->assertFalse(Cache::has("company_{$this->company->id}_erp_workflow_settings"));

        WorkflowConfig::get('inventory', 'serial_tracking', 'false', $this->company->id);

        $this->assertTrue(Cache::has("company_{$this->company->id}_erp_workflow_settings"));
    }

    /** @test */
    public function it_invalidates_cache_automatically_via_observer()
    {
        // 1. Set settings record and populate cache
        WorkflowConfig::set('inventory', 'serial_tracking', 'true', $this->company->id);
        WorkflowConfig::get('inventory', 'serial_tracking', 'false', $this->company->id);

        $this->assertTrue(Cache::has("company_{$this->company->id}_erp_workflow_settings"));

        // 2. Update record directly via model to trigger observer
        $setting = ErpWorkflowSetting::where('company_id', $this->company->id)
            ->where('setting_group', 'inventory')
            ->where('setting_key', 'serial_tracking')
            ->firstOrFail();

        $setting->update(['setting_value' => 'false']);

        // Cache must be cleared instantly
        $this->assertFalse(Cache::has("company_{$this->company->id}_erp_workflow_settings"));
    }
}
