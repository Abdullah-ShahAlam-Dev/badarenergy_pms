<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\UnitType;
use Illuminate\Database\Seeder;

class InventoryModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $companies = Company::select('id')->get();

        foreach ($companies as $company) {
            $companyId = $company->id;

            // 1. Seed or find the Unit of Measure "Piece"
            $unit = UnitType::firstOrCreate([
                'company_id' => $companyId,
                'unit_type' => 'Piece',
            ], [
                'default' => 0
            ]);

            // 2. Seed primary locations
            $locations = [
                [
                    'name' => 'Head Office Warehouse',
                    'code' => 'HO-WH',
                    'type' => 'warehouse',
                    'address' => 'Head Office, Karachi',
                ],
                [
                    'name' => 'Saddar Outlet',
                    'code' => 'SD-OL',
                    'type' => 'outlet',
                    'address' => 'Saddar, Karachi',
                ],
                [
                    'name' => 'Lahore Outlet',
                    'code' => 'LH-OL',
                    'type' => 'outlet',
                    'address' => 'Lahore, Punjab',
                ],
            ];

            foreach ($locations as $loc) {
                Warehouse::firstOrCreate([
                    'company_id' => $companyId,
                    'code' => $loc['code'],
                ], [
                    'name' => $loc['name'],
                    'type' => $loc['type'],
                    'address' => $loc['address'],
                    'is_active' => true,
                ]);
            }

            // 3. Seed standard battery models
            $batteryModels = [
                // 12.8V Range
                ['name' => '12.8V 100AH Battery', 'voltage' => '12.8V', 'capacity' => '100AH', 'product_code' => 'BAT-12.8-100', 'barcode' => '1281000001', 'price' => 15000],
                ['name' => '12.8V 200AH Battery', 'voltage' => '12.8V', 'capacity' => '200AH', 'product_code' => 'BAT-12.8-200', 'barcode' => '1282000002', 'price' => 28000],
                ['name' => '12.8V 280AH Battery', 'voltage' => '12.8V', 'capacity' => '280AH', 'product_code' => 'BAT-12.8-280', 'barcode' => '1282800003', 'price' => 38000],

                // 25.6V Range
                ['name' => '25.6V 100AH Battery', 'voltage' => '25.6V', 'capacity' => '100AH', 'product_code' => 'BAT-25.6-100', 'barcode' => '2561000004', 'price' => 29000],
                ['name' => '25.6V 200AH Battery', 'voltage' => '25.6V', 'capacity' => '200AH', 'product_code' => 'BAT-25.6-200', 'barcode' => '2562000005', 'price' => 54000],
                ['name' => '25.6V 280AH Battery', 'voltage' => '25.6V', 'capacity' => '280AH', 'product_code' => 'BAT-25.6-280', 'barcode' => '2562800006', 'price' => 74000],

                // 48V Range
                ['name' => '48V 100AH Battery', 'voltage' => '48V', 'capacity' => '100AH', 'product_code' => 'BAT-48-100', 'barcode' => '4801000007', 'price' => 55000],
                ['name' => '48V 200AH Battery', 'voltage' => '48V', 'capacity' => '200AH', 'product_code' => 'BAT-48-200', 'barcode' => '4802000008', 'price' => 98000],
                ['name' => '48V 280AH Battery', 'voltage' => '48V', 'capacity' => '280AH', 'product_code' => 'BAT-48-280', 'barcode' => '4802800009', 'price' => 135000],

                // 51.2V Range
                ['name' => '51.2V 100AH Battery', 'voltage' => '51.2V', 'capacity' => '100AH', 'product_code' => 'BAT-51.2-100', 'barcode' => '5121000010', 'price' => 59000],
                ['name' => '51.2V 200AH Battery', 'voltage' => '51.2V', 'capacity' => '200AH', 'product_code' => 'BAT-51.2-200', 'barcode' => '5122000011', 'price' => 105000],
                ['name' => '51.2V 280AH Battery', 'voltage' => '51.2V', 'capacity' => '280AH', 'product_code' => 'BAT-51.2-280', 'barcode' => '5122800012', 'price' => 145000],

                // Tiger Range
                ['name' => 'Tiger 12.8V 100AH', 'voltage' => '12.8V', 'capacity' => '100AH', 'product_code' => 'TGR-12.8-100', 'barcode' => '9001000013', 'price' => 18000],
                ['name' => 'Tiger 25.6V 200AH', 'voltage' => '25.6V', 'capacity' => '200AH', 'product_code' => 'TGR-25.6-200', 'barcode' => '9002000014', 'price' => 58000],

                // Tiger Pro Range
                ['name' => 'Tiger Pro 48V 200AH', 'voltage' => '48V', 'capacity' => '200AH', 'product_code' => 'TGRPRO-48-200', 'barcode' => '9012000015', 'price' => 110000],
                ['name' => 'Tiger Pro 51.2V 280AH', 'voltage' => '51.2V', 'capacity' => '280AH', 'product_code' => 'TGRPRO-51.2-280', 'barcode' => '9012800016', 'price' => 155000],

                // SK16S Range
                ['name' => 'SK16S 48V 100AH', 'voltage' => '48V', 'capacity' => '100AH', 'product_code' => 'SK16S-48-100', 'barcode' => '8001000017', 'price' => 62000],
                ['name' => 'SK16S 51.2V 280AH', 'voltage' => '51.2V', 'capacity' => '280AH', 'product_code' => 'SK16S-51.2-280', 'barcode' => '8002800018', 'price' => 150000],

                // PDU Range
                ['name' => 'PDU 48V Cabinet', 'voltage' => '48V', 'capacity' => 'N/A', 'product_code' => 'PDU-48V', 'barcode' => '7000480019', 'price' => 45000],
            ];

            foreach ($batteryModels as $model) {
                Product::firstOrCreate([
                    'company_id' => $companyId,
                    'product_code' => $model['product_code'],
                ], [
                    'name' => $model['name'],
                    'voltage' => $model['voltage'],
                    'capacity' => $model['capacity'],
                    'barcode' => $model['barcode'],
                    'price' => $model['price'],
                    'allow_purchase' => 1,
                    'unit_id' => $unit->id,
                    'description' => $model['name'] . ' battery module.',
                    'type' => 'imported', // Default category
                    'is_serialized' => true,
                ]);
            }
        }
    }
}
