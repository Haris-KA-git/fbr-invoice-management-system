<?php

namespace Database\Seeders;

use App\Models\Uom;
use Illuminate\Database\Seeder;

class UomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $uoms = [
            // Weight Category
            ['name' => 'Kilogram', 'code' => 'KGM', 'category' => 'Weight', 'notes' => 'Standard unit of mass'],
            ['name' => 'Gram', 'code' => 'GRM', 'category' => 'Weight', 'notes' => 'Sub-unit of weight'],
            ['name' => 'Carat', 'code' => 'CTM', 'category' => 'Weight', 'notes' => 'For gemstones'],
            ['name' => 'Ton', 'code' => 'TNE', 'category' => 'Weight', 'notes' => 'Metric ton'],
            
            // Length Category
            ['name' => 'Meter', 'code' => 'MTR', 'category' => 'Length', 'notes' => 'Standard unit of length'],
            ['name' => 'Centimeter', 'code' => 'CMT', 'category' => 'Length', 'notes' => 'Sub-unit of length'],
            ['name' => 'Millimeter', 'code' => 'MMT', 'category' => 'Length', 'notes' => 'Sub-unit of length'],
            ['name' => 'Kilometer', 'code' => 'KMT', 'category' => 'Length', 'notes' => 'Multiple of meter'],
            
            // Area Category
            ['name' => 'Square meter', 'code' => 'MTK', 'category' => 'Area', 'notes' => 'For area-based items'],
            ['name' => 'Square centimeter', 'code' => 'CMK', 'category' => 'Area', 'notes' => 'Sub-unit of area'],
            ['name' => 'Square foot', 'code' => 'FTK', 'category' => 'Area', 'notes' => 'Imperial area unit'],
            
            // Volume Category
            ['name' => 'Cubic meter', 'code' => 'MTQ', 'category' => 'Volume', 'notes' => 'For volume-based items'],
            ['name' => 'Liter', 'code' => 'LTR', 'category' => 'Volume', 'notes' => 'For liquid measurement'],
            ['name' => 'Milliliter', 'code' => 'MLT', 'category' => 'Volume', 'notes' => 'Sub-unit of liter'],
            ['name' => 'Gallon', 'code' => 'GLL', 'category' => 'Volume', 'notes' => 'Imperial volume unit'],
            
            // Count Category
            ['name' => 'Piece / Unit', 'code' => 'NAR', 'category' => 'Count', 'notes' => 'Number of articles (pieces)'],
            ['name' => 'Pair', 'code' => 'PR', 'category' => 'Count', 'notes' => '2 units'],
            ['name' => 'Dozen', 'code' => 'DZN', 'category' => 'Count', 'notes' => '12 units'],
            ['name' => 'Thousand pieces', 'code' => 'T3', 'category' => 'Count', 'notes' => '1000 units'],
            ['name' => 'Pack', 'code' => 'PAC', 'category' => 'Count', 'notes' => 'Packaged items'],
            ['name' => 'Set', 'code' => 'SET', 'category' => 'Count', 'notes' => 'Collection of items'],
            ['name' => 'Carton', 'code' => 'CT', 'category' => 'Count', 'notes' => 'Carton packaging'],
            ['name' => 'Box', 'code' => 'BX', 'category' => 'Count', 'notes' => 'Box packaging'],
            
            // Time Category
            ['name' => 'Hour', 'code' => 'HUR', 'category' => 'Time', 'notes' => 'For services'],
            ['name' => 'Day', 'code' => 'DAY', 'category' => 'Time', 'notes' => 'For services'],
            ['name' => 'Week', 'code' => 'WEE', 'category' => 'Time', 'notes' => 'For services'],
            ['name' => 'Month', 'code' => 'MON', 'category' => 'Time', 'notes' => 'For subscriptions'],
            ['name' => 'Year', 'code' => 'ANN', 'category' => 'Time', 'notes' => 'Annual services'],
            
            // Energy Category
            ['name' => '1000 Kilowatt Hours', 'code' => 'MWH', 'category' => 'Energy', 'notes' => 'Electricity unit (FBR standard)'],
            ['name' => 'Kilowatt Hour', 'code' => 'KWH', 'category' => 'Energy', 'notes' => 'Electricity sub-unit'],
            
            // Special Categories
            ['name' => 'Barrel', 'code' => 'BLL', 'category' => 'Volume', 'notes' => 'Oil/petroleum products'],
            ['name' => 'Foot', 'code' => 'FOT', 'category' => 'Length', 'notes' => 'Imperial length unit'],
            ['name' => 'Inch', 'code' => 'INH', 'category' => 'Length', 'notes' => 'Imperial length sub-unit'],
        ];

        foreach ($uoms as $uom) {
            Uom::firstOrCreate(
                ['code' => $uom['code']],
                $uom
            );
        }
    }
}