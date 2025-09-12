<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\BusinessProfile;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;

class CustomerImportService implements ToModel, WithHeadingRow, WithValidation
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
            if (empty($row['name']) || empty($row['ntn_cnic'])) {
                $this->results['skipped']++;
                return null;
            }

            // Check for existing customer by NTN/CNIC
            $existingCustomer = Customer::where('business_profile_id', $this->businessProfileId)
                ->where('ntn_cnic', $row['ntn_cnic'])
                ->first();

            $customerData = [
                'business_profile_id' => $this->businessProfileId,
                'name' => $row['name'],
                'ntn_cnic' => $row['ntn_cnic'],
                'address' => $row['address'] ?? null,
                'contact_phone' => $row['contact_phone'] ?? null,
                'contact_email' => $row['contact_email'] ?? null,
                'customer_type' => $this->determineCustomerType($row['ntn_cnic']),
                'is_active' => true,
            ];

            if ($existingCustomer) {
                $existingCustomer->update($customerData);
                $this->results['updated']++;
                return null;
            } else {
                $this->results['imported']++;
                return new Customer($customerData);
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
            'ntn_cnic' => 'required|string|max:20',
            'address' => 'nullable|string|max:500',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
        ];
    }

    private function determineCustomerType($ntnCnic)
    {
        // If NTN/CNIC is 13 digits, it's likely a business NTN
        // If it's 15 digits with dashes, it's likely a CNIC
        if (strlen(str_replace('-', '', $ntnCnic)) === 13 && !str_contains($ntnCnic, '-')) {
            return 'registered';
        }
        return 'unregistered';
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