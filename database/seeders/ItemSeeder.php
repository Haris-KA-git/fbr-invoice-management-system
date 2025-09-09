<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\BusinessProfile;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $businessProfiles = BusinessProfile::all();

        $items = [
            [
                'item_code' => 'LAPTOP001',
                'name' => 'Dell Laptop Core i5',
                'description' => 'Dell Inspiron 15 3000 Series with Intel Core i5 processor, 8GB RAM, 256GB SSD',
                'hs_code' => '8471.30.00',
                'unit_of_measure' => 'PCS',
                'tax_rate' => 17.00,
                'price' => 85000.00,
                'sro_references' => ['SRO-1125(I)/2019', 'SRO-350(I)/2020'],
            ],
            [
                'item_code' => 'MOBILE001',
                'name' => 'Samsung Galaxy A54',
                'description' => 'Samsung Galaxy A54 5G, 128GB Storage, 6GB RAM',
                'hs_code' => '8517.12.00',
                'unit_of_measure' => 'PCS',
                'tax_rate' => 17.00,
                'price' => 65000.00,
                'sro_references' => ['SRO-1125(I)/2019'],
            ],
            [
                'item_code' => 'PRINTER001',
                'name' => 'HP LaserJet Pro',
                'description' => 'HP LaserJet Pro M404dn Monochrome Laser Printer',
                'hs_code' => '8443.32.10',
                'unit_of_measure' => 'PCS',
                'tax_rate' => 17.00,
                'price' => 45000.00,
            ],
            [
                'item_code' => 'CHAIR001',
                'name' => 'Office Executive Chair',
                'description' => 'Ergonomic Executive Office Chair with Lumbar Support',
                'hs_code' => '9401.30.00',
                'unit_of_measure' => 'PCS',
                'tax_rate' => 17.00,
                'price' => 25000.00,
            ],
            [
                'item_code' => 'DESK001',
                'name' => 'Office Desk Wooden',
                'description' => 'Executive Office Desk made of high-quality wood',
                'hs_code' => '9403.30.00',
                'unit_of_measure' => 'PCS',
                'tax_rate' => 17.00,
                'price' => 35000.00,
            ],
            [
                'item_code' => 'CONSULT001',
                'name' => 'IT Consultation Service',
                'description' => 'Professional IT consultation and advisory services',
                'unit_of_measure' => 'HR',
                'tax_rate' => 16.00,
                'price' => 5000.00,
            ],
            [
                'item_code' => 'TRAINING001',
                'name' => 'Software Training',
                'description' => 'Professional software training and development',
                'unit_of_measure' => 'DAY',
                'tax_rate' => 16.00,
                'price' => 15000.00,
            ],
            [
                'item_code' => 'RICE001',
                'name' => 'Basmati Rice Premium',
                'description' => 'Premium quality Basmati Rice, Grade A',
                'hs_code' => '1006.30.00',
                'unit_of_measure' => 'KG',
                'tax_rate' => 0.00,
                'price' => 250.00,
            ],
        ];

        foreach ($businessProfiles as $profile) {
            foreach ($items as $itemData) {
                Item::firstOrCreate([
                    'business_profile_id' => $profile->id,
                    'item_code' => $itemData['item_code'],
                ], array_merge($itemData, [
                    'business_profile_id' => $profile->id,
                    'is_active' => true,
                ]));
            }
        }
    }
}