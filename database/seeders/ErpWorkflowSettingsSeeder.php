<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\ErpWorkflowSetting;
use Illuminate\Database\Seeder;

class ErpWorkflowSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = Company::all();

        $defaultSettings = [
            [
                'setting_group' => 'inventory',
                'setting_key' => 'stock_out_trigger',
                'setting_value' => 'invoice_approval',
                'datatype' => 'string',
                'default_value' => 'invoice_approval',
                'description' => 'Event that deducts quantities: invoice_approval, delivery_order_dispatch, gate_pass_dispatch.',
                'is_public' => true,
            ],
            [
                'setting_group' => 'inventory',
                'setting_key' => 'costing_method',
                'setting_value' => 'wac',
                'datatype' => 'string',
                'default_value' => 'wac',
                'description' => 'COGS valuation method: wac, fifo, moving_average, specific_cost.',
                'is_public' => true,
            ],
            [
                'setting_group' => 'inventory',
                'setting_key' => 'serial_tracking',
                'setting_value' => 'true',
                'datatype' => 'boolean',
                'default_value' => 'true',
                'description' => 'Enables individual serial number tracking.',
                'is_public' => true,
            ],
            [
                'setting_group' => 'inventory',
                'setting_key' => 'batch_tracking',
                'setting_value' => 'false',
                'datatype' => 'boolean',
                'default_value' => 'false',
                'description' => 'Enables grouping serials under product batches.',
                'is_public' => true,
            ],
            [
                'setting_group' => 'sales',
                'setting_key' => 'invoice_creation_workflow',
                'setting_value' => 'before_dispatch',
                'datatype' => 'string',
                'default_value' => 'before_dispatch',
                'description' => 'Sales workflow ordering: before_dispatch, after_dispatch, independent.',
                'is_public' => true,
            ],
            [
                'setting_group' => 'sales',
                'setting_key' => 'pricing_source',
                'setting_value' => 'invoice',
                'datatype' => 'string',
                'default_value' => 'invoice',
                'description' => 'Defines where product selling prices are entered.',
                'is_public' => true,
            ],
            [
                'setting_group' => 'approvals',
                'setting_key' => 'delivery_order',
                'setting_value' => 'false',
                'datatype' => 'boolean',
                'default_value' => 'false',
                'description' => 'Enforces supervisor approval before DO can be picked.',
                'is_public' => true,
            ],
            [
                'setting_group' => 'approvals',
                'setting_key' => 'invoice',
                'setting_value' => 'true',
                'datatype' => 'boolean',
                'default_value' => 'true',
                'description' => 'Enforces invoice approval before debiting dealer ledger.',
                'is_public' => true,
            ],
            [
                'setting_group' => 'approvals',
                'setting_key' => 'stock_transfer',
                'setting_value' => 'false',
                'datatype' => 'boolean',
                'default_value' => 'false',
                'description' => 'Enforces approval step for inter-warehouse transfers.',
                'is_public' => true,
            ],
            [
                'setting_group' => 'approvals',
                'setting_key' => 'stock_intake',
                'setting_value' => 'false',
                'datatype' => 'boolean',
                'default_value' => 'false',
                'description' => 'Enforces approval step for container/SIV receipts.',
                'is_public' => true,
            ],
            [
                'setting_group' => 'serial',
                'setting_key' => 'prefix',
                'setting_value' => 'BE',
                'datatype' => 'string',
                'default_value' => 'BE',
                'description' => 'Serial number generation prefix.',
                'is_public' => true,
            ],
            [
                'setting_group' => 'serial',
                'setting_key' => 'digit_length',
                'setting_value' => '8',
                'datatype' => 'integer',
                'default_value' => '8',
                'description' => 'Serial number numeric sequence length.',
                'is_public' => true,
            ],
            [
                'setting_group' => 'serial',
                'setting_key' => 'include_year',
                'setting_value' => 'true',
                'datatype' => 'boolean',
                'default_value' => 'true',
                'description' => 'Include year code in generated serials.',
                'is_public' => true,
            ],
            [
                'setting_group' => 'barcode',
                'setting_key' => 'type',
                'setting_value' => 'code128',
                'datatype' => 'string',
                'default_value' => 'code128',
                'description' => 'Configurable barcode encoding engine.',
                'is_public' => true,
            ],
        ];

        foreach ($companies as $company) {
            foreach ($defaultSettings as $setting) {
                ErpWorkflowSetting::firstOrCreate([
                    'company_id' => $company->id,
                    'setting_group' => $setting['setting_group'],
                    'setting_key' => $setting['setting_key'],
                ], [
                    'setting_value' => $setting['setting_value'],
                    'datatype' => $setting['datatype'],
                    'default_value' => $setting['default_value'],
                    'description' => $setting['description'],
                    'is_public' => $setting['is_public'],
                ]);
            }
        }
    }
}
