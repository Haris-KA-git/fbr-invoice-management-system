<?php

namespace App\Services;

use App\Models\Item;
use App\Models\BusinessProfile;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;

class ItemImportService implements ToModel, WithHeadingRow, WithValidation
{
    use Importable;

    private $businessProfileId;
    private $results = [
        'imported' => 0,
        'updated' => 0,
        'skipped' => 0,
        'errors' => []
    ];

    public function __construct($businessProfileId)
    {
        $this->businessProfileId = $businessProfileId;
    }

    public function model(array $row)
    {
        try {
            // Skip empty rows
            if (empty($row['name']) || empty($row['hs_code'])) {
                $this->results['skipped']++;
                return null;
            }

            // Check for existing item by HS Code
            $existingItem = Item::where('business_profile_id', $this->businessProfileId)
                ->where('hs_code', $row['hs_code'])
                ->first();

            // Generate item code if not provided
            $itemCode = $row['item_code'] ?? $this->generateItemCode($row['name']);

            $itemData = [
                'business_profile_id' => $this->businessProfileId,
                'item_code' => $itemCode,
                'name' => $row['name'],
                'description' => $row['description'] ?? null,
                'hs_code' => $row['hs_code'],
                'unit_of_measure' => strtoupper($row['unit_of_measure'] ?? 'PCS'),
                'tax_rate' => $row['gst_rate'] ?? $row['tax_rate'] ?? 17.00,
                'price' => $row['price'] ?? 0,
                'is_active' => true,
            ];

            if ($existingItem) {
                $existingItem->update($itemData);
                $this->results['updated']++;
                return null;
            } else {
                // Check for duplicate item code
                $duplicateCode = Item::where('business_profile_id', $this->businessProfileId)
                    ->where('item_code', $itemCode)
                    ->exists();
                
                if ($duplicateCode) {
                    $itemData['item_code'] = $this->generateUniqueItemCode($row['name']);
                }

                $this->results['imported']++;
                return new Item($itemData);
            }
        } catch (\Exception $e) {
            $this->results['errors'][] = "Row error: " . $e->getMessage();
            $this->results['skipped']++;
            return null;
        }
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'hs_code' => 'required|string|max:20',
            'unit_of_measure' => 'nullable|string|max:10',
            'price' => 'nullable|numeric|min:0',
            'gst_rate' => 'nullable|numeric|min:0|max:100',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
        ];
    }

    private function generateItemCode($name)
    {
        $code = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 6));
        return $code . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
    }

    private function generateUniqueItemCode($name)
    {
        do {
            $code = $this->generateItemCode($name);
        } while (Item::where('business_profile_id', $this->businessProfileId)
                    ->where('item_code', $code)->exists());
        
        return $code;
    }

    public function getResults()
    {
        return $this->results;
    }

    public static function importFromFile(UploadedFile $file, $businessProfileId)
    {
        $service = new self($businessProfileId);
        Excel::import($service, $file);
        return $service->getResults();
    }
}