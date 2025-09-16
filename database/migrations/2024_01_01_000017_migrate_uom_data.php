<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Item;
use App\Models\Uom;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrate existing unit_of_measure data to uom_id
        $items = Item::whereNull('uom_id')->get();
        
        foreach ($items as $item) {
            $uomCode = $this->mapLegacyUomToCode($item->unit_of_measure);
            $uom = Uom::where('code', $uomCode)->first();
            
            if ($uom) {
                $item->update(['uom_id' => $uom->id]);
            } else {
                // Default to NAR (Number of articles) if no match found
                $defaultUom = Uom::where('code', 'NAR')->first();
                if ($defaultUom) {
                    $item->update(['uom_id' => $defaultUom->id]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reset uom_id to null
        Item::whereNotNull('uom_id')->update(['uom_id' => null]);
    }

    private function mapLegacyUomToCode($legacyUom): string
    {
        return match(strtoupper($legacyUom)) {
            'PCS', 'PIECES' => 'NAR',
            'KG', 'KILOGRAM' => 'KGM',
            'LTR', 'LITER', 'LITRE' => 'LTR',
            'MTR', 'METER', 'METRE' => 'MTR',
            'SQM', 'SQUARE METER' => 'MTK',
            'HR', 'HOUR' => 'HUR',
            'DAY', 'DAYS' => 'DAY',
            'SET', 'SETS' => 'SET',
            'TON', 'TONS' => 'TNE',
            'GRAM', 'GRAMS' => 'GRM',
            default => 'NAR'
        };
    }
};