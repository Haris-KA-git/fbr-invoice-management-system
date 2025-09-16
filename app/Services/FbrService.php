<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\FbrQueue;
use App\Services\QrCodeService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FbrService
{
    private $sandboxBaseUrl = 'https://esp.fbr.gov.pk:8244/FBR/v1';
    private $productionBaseUrl = 'https://esp.fbr.gov.pk/FBR/v1';
    private $qrCodeService;

    public function __construct(QrCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
    }

    public function queueInvoice(Invoice $invoice)
    {
        $payload = $this->prepareInvoiceData($invoice);
        
        FbrQueue::create([
            'invoice_id' => $invoice->id,
            'action' => 'validate',
            'payload' => $payload,
            'status' => 'pending',
        ]);

        return true;
    }

    public function validateInvoice(Invoice $invoice)
    {
        try {
            $businessProfile = $invoice->businessProfile;
            
            if (!$businessProfile->fbr_api_token) {
                throw new \Exception('FBR API token not configured for this business profile');
            }

            $payload = $this->prepareInvoiceData($invoice);
            
            $baseUrl = $businessProfile->is_sandbox ? $this->sandboxBaseUrl : $this->productionBaseUrl;
            $endpoint = $baseUrl . '/validateinvoicedata';

            $response = Http::timeout(config('fbr.api_timeout', 30))
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $businessProfile->fbr_api_token,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])->post($endpoint, $payload);

            if ($response->successful()) {
                $responseData = $response->json();
                
                $invoice->update([
                    'fbr_status' => 'validated',
                    'fbr_response' => json_encode($responseData),
                    'fbr_json_data' => $payload,
                    'fbr_error_message' => null,
                ]);

                // Queue for submission
                FbrQueue::create([
                    'invoice_id' => $invoice->id,
                    'action' => 'submit',
                    'payload' => $payload,
                    'status' => 'pending',
                ]);

                return ['success' => true, 'data' => $responseData];
            } else {
                $errorMessage = $response->body();
                
                $invoice->update([
                    'fbr_status' => 'failed',
                    'fbr_error_message' => $errorMessage,
                ]);

                return ['success' => false, 'message' => $errorMessage];
            }
        } catch (\Exception $e) {
            Log::error('FBR Validation Error: ' . $e->getMessage(), [
                'invoice_id' => $invoice->id,
                'business_profile_id' => $invoice->business_profile_id,
            ]);
            
            $invoice->update([
                'fbr_status' => 'failed',
                'fbr_error_message' => $e->getMessage(),
            ]);

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function submitInvoice(Invoice $invoice)
    {
        try {
            $businessProfile = $invoice->businessProfile;
            
            if (!$businessProfile->fbr_api_token) {
                throw new \Exception('FBR API token not configured for this business profile');
            }

            $payload = $this->prepareInvoiceData($invoice);
            
            $baseUrl = $businessProfile->is_sandbox ? $this->sandboxBaseUrl : $this->productionBaseUrl;
            $endpoint = $baseUrl . '/postinvoicedata';

            $response = Http::timeout(config('fbr.api_timeout', 30))
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $businessProfile->fbr_api_token,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])->post($endpoint, $payload);

            if ($response->successful()) {
                $responseData = $response->json();
                
                // Generate QR code with FBR compliant data
                $qrPath = $this->qrCodeService->generateInvoiceQrCode($invoice);

                $invoice->update([
                    'fbr_status' => 'submitted',
                    'fbr_response' => json_encode($responseData),
                    'usin' => $responseData['USIN'] ?? null,
                    'fbr_invoice_number' => $responseData['InvoiceNumber'] ?? $invoice->invoice_number,
                    'fbr_verification_url' => $responseData['VerificationURL'] ?? config('app.url') . '/verify/' . $invoice->id,
                    'fbr_error_message' => null,
                ]);

                return ['success' => true, 'data' => $responseData];
            } else {
                $errorMessage = $response->body();
                
                $invoice->update([
                    'fbr_status' => 'failed',
                    'fbr_error_message' => $errorMessage,
                ]);

                return ['success' => false, 'message' => $errorMessage];
            }
        } catch (\Exception $e) {
            Log::error('FBR Submission Error: ' . $e->getMessage(), [
                'invoice_id' => $invoice->id,
                'business_profile_id' => $invoice->business_profile_id,
            ]);
            
            $invoice->update([
                'fbr_status' => 'failed',
                'fbr_error_message' => $e->getMessage(),
            ]);

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function prepareInvoiceData(Invoice $invoice)
    {
        $invoice->load(['businessProfile', 'customer', 'invoiceItems.item']);
        
        // FBR DI API v1.12 compliant structure
        $items = [];
        $totalTaxableAmount = 0;
        $totalSalesTax = 0;
        $totalFED = 0;
        $totalFurtherTax = 0;
        $totalWithheldTax = 0;

        foreach ($invoice->invoiceItems as $invoiceItem) {
            $quantity = (float) $invoiceItem->quantity;
            $unitPrice = (float) $invoiceItem->unit_price;
            $discountRate = (float) $invoiceItem->discount_rate;
            $taxRate = (float) $invoiceItem->tax_rate;

            // Calculate amounts as per FBR requirements
            $totalBeforeDiscount = $quantity * $unitPrice;
            $discountAmount = ($totalBeforeDiscount * $discountRate) / 100;
            $taxableAmount = $totalBeforeDiscount - $discountAmount;
            $salesTaxAmount = ($taxableAmount * $taxRate) / 100;
            $totalAmountWithTax = $taxableAmount + $salesTaxAmount;

            $items[] = [
                'ItemCode' => $invoiceItem->item->item_code,
                'ItemName' => $invoiceItem->item->name,
                'ItemDescription' => $invoiceItem->item->description ?? '',
                'HSCode' => $invoiceItem->item->hs_code,
                'PCTCode' => $invoiceItem->item->hs_code, // Pakistan Customs Tariff Code
                'UOM' => $invoiceItem->item->unit_of_measure,
                'UOMCode' => $this->mapUOMCode($invoiceItem->item->unit_of_measure),
                'Quantity' => $quantity,
                'UnitPrice' => $unitPrice,
                'TotalAmount' => $totalBeforeDiscount,
                'TaxableAmount' => $taxableAmount,
                'SalesTaxRate' => $taxRate,
                'SalesTaxAmount' => $salesTaxAmount,
                'DiscountRate' => $discountRate,
                'DiscountAmount' => $discountAmount,
                'FEDRate' => 0.0,
                'FEDAmount' => 0.0,
                'FurtherTaxRate' => 0.0,
                'FurtherTaxAmount' => 0.0,
                'WithheldTaxRate' => 0.0,
                'WithheldTaxAmount' => 0.0,
                'TotalAmountWithTax' => $totalAmountWithTax,
                'InvoiceType' => $this->mapInvoiceTypeCode($invoice->invoice_type),
                'RefUSIN' => null, // For credit/debit notes
            ];

            $totalTaxableAmount += $taxableAmount;
            $totalSalesTax += $salesTaxAmount;
        }

        // FBR DI API v1.12 compliant invoice structure
        return [
            'InvoiceNumber' => $invoice->invoice_number,
            'InvoiceType' => $this->mapInvoiceTypeCode($invoice->invoice_type),
            'DocumentType' => $this->mapDocumentType($invoice->invoice_type),
            'InvoiceDate' => $invoice->invoice_date->format('Y-m-d'),
            'InvoiceTime' => $invoice->created_at->format('H:i:s'),
            'CurrencyCode' => 'PKR',
            'ExchangeRate' => 1.0,
            'InvoiceValue' => (float) $invoice->total_amount,
            'TotalSalesTax' => $totalSalesTax,
            'TotalFEDAmount' => $totalFED,
            'TotalFurtherTax' => $totalFurtherTax,
            'TotalWithheldTax' => $totalWithheldTax,
            'TotalAmount' => (float) $invoice->total_amount,
            'PaymentMode' => $this->getPaymentMode($invoice),
            'RefUSIN' => null, // For credit/debit notes
            'Seller' => [
                'BusinessName' => $invoice->businessProfile->business_name,
                'NTN' => $invoice->businessProfile->strn_ntn ?? '',
                'STRN' => $invoice->businessProfile->strn_ntn ?? '',
                'Address' => $invoice->businessProfile->address,
                'City' => $this->extractCity($invoice->businessProfile->address),
                'Country' => 'Pakistan',
                'ProvinceCode' => $invoice->businessProfile->province_code ?? '01',
                'PostalCode' => $this->extractPostalCode($invoice->businessProfile->address),
                'ContactNumber' => $invoice->businessProfile->contact_phone ?? '',
                'EmailAddress' => $invoice->businessProfile->contact_email ?? '',
                'BranchName' => $invoice->businessProfile->branch_name ?? '',
                'BranchCode' => $invoice->businessProfile->branch_code ?? '',
            ],
            'Buyer' => [
                'Name' => $invoice->customer->name,
                'NTN' => $invoice->customer->customer_type === 'registered' ? $invoice->customer->ntn_cnic : '',
                'CNIC' => $invoice->customer->customer_type === 'unregistered' ? $invoice->customer->ntn_cnic : '',
                'STRN' => $invoice->customer->customer_type === 'registered' ? $invoice->customer->ntn_cnic : '',
                'Address' => $invoice->customer->address ?? '',
                'City' => $this->extractCity($invoice->customer->address ?? ''),
                'Country' => 'Pakistan',
                'ContactNumber' => $invoice->customer->contact_phone ?? '',
                'EmailAddress' => $invoice->customer->contact_email ?? '',
                'BuyerType' => $invoice->customer->customer_type === 'registered' ? 'Registered' : 'Unregistered',
            ],
            'Items' => $items,
            'PaymentTerms' => 'Immediate',
            'Remarks' => 'FBR Compliant Digital Invoice - Generated via Expert Digital Invoice',
            'InvoiceCategory' => 'Normal',
            'SpecialProcedure' => 'Normal',
            'TaxInclusiveAmount' => (float) $invoice->total_amount,
            'TotalTaxableAmount' => $totalTaxableAmount,
            'TotalQuantity' => $invoice->invoiceItems->sum('quantity'),
            'TotalItems' => $invoice->invoiceItems->count(),
        ];
    }

    private function mapInvoiceTypeCode(string $type): string
    {
        return match($type) {
            'sales' => '01',
            'purchase' => '02',
            'debit_note' => '03',
            'credit_note' => '04',
            default => '01'
        };
    }

    private function mapDocumentType(string $type): string
    {
        return match($type) {
            'sales' => 'Invoice',
            'purchase' => 'Purchase Invoice',
            'debit_note' => 'Debit Note',
            'credit_note' => 'Credit Note',
            default => 'Invoice'
        };
    }

    private function mapUOMCode(string $uom): string
    {
        return match(strtoupper($uom)) {
            'PCS', 'PIECES' => 'PCS',
            'KG', 'KILOGRAM' => 'KGM',
            'LTR', 'LITER', 'LITRE' => 'LTR',
            'MTR', 'METER', 'METRE' => 'MTR',
            'SQM', 'SQUARE METER' => 'MTK',
            'HR', 'HOUR' => 'HUR',
            'DAY', 'DAYS' => 'DAY',
            'SET', 'SETS' => 'SET',
            'TON', 'TONS' => 'TNE',
            'GRAM', 'GRAMS' => 'GRM',
            default => strtoupper($uom)
        };
    }

    private function getPaymentMode(Invoice $invoice): string
    {
        // Default to Cash, can be extended based on business requirements
        return 'Cash';
    }

    private function extractCity(string $address): string
    {
        // Enhanced city extraction for Pakistani cities
        $cities = [
            'Karachi', 'Lahore', 'Islamabad', 'Rawalpindi', 'Faisalabad', 'Multan', 
            'Peshawar', 'Quetta', 'Sialkot', 'Gujranwala', 'Hyderabad', 'Sargodha',
            'Bahawalpur', 'Sukkur', 'Larkana', 'Sheikhupura', 'Jhang', 'Rahim Yar Khan',
            'Gujrat', 'Kasur', 'Mardan', 'Mingora', 'Dera Ghazi Khan', 'Sahiwal',
            'Nawabshah', 'Okara', 'Mirpur Khas', 'Chiniot', 'Kamoke', 'Mandi Bahauddin',
            'Jhelum', 'Sadiqabad', 'Jacobabad', 'Shikarpur', 'Khanewal', 'Hafizabad'
        ];
        
        foreach ($cities as $city) {
            if (stripos($address, $city) !== false) {
                return $city;
            }
        }
        
        // If no city found, try to extract from address parts
        $parts = array_map('trim', explode(',', $address));
        
        // Look for city-like patterns (capitalized words)
        foreach ($parts as $part) {
            if (preg_match('/^[A-Z][a-z]+(\s+[A-Z][a-z]+)*$/', trim($part))) {
                return trim($part);
            }
        }
        
        return 'Lahore'; // Default city
    }

    private function extractPostalCode(string $address): string
    {
        // Extract Pakistani postal code pattern (5 digits)
        if (preg_match('/\b\d{5}\b/', $address, $matches)) {
            return $matches[0];
        }
        
        // Default postal codes by major cities
        $defaultCodes = [
            'Karachi' => '74000',
            'Lahore' => '54000',
            'Islamabad' => '44000',
            'Rawalpindi' => '46000',
            'Faisalabad' => '38000',
            'Multan' => '60000',
            'Peshawar' => '25000',
            'Quetta' => '87000',
        ];
        
        $city = $this->extractCity($address);
        return $defaultCodes[$city] ?? '54000';
    }

    public function processQueue()
    {
        $maxRetries = config('fbr.max_retries', 3);
        
        $queueItems = FbrQueue::where('status', 'pending')
            ->where('retry_count', '<', $maxRetries)
            ->orderBy('created_at')
            ->limit(10)
            ->get();

        foreach ($queueItems as $queueItem) {
            $queueItem->update([
                'status' => 'processing',
                'last_retry_at' => now(),
            ]);

            $invoice = $queueItem->invoice;
            
            if (!$invoice) {
                $queueItem->update(['status' => 'failed', 'error_message' => 'Invoice not found']);
                continue;
            }

            try {
                if ($queueItem->action === 'validate') {
                    $result = $this->validateInvoice($invoice);
                } else {
                    $result = $this->submitInvoice($invoice);
                }

                if ($result['success']) {
                    $queueItem->update([
                        'status' => 'completed',
                        'error_message' => null,
                    ]);
                } else {
                    $queueItem->update([
                        'status' => 'pending',
                        'retry_count' => $queueItem->retry_count + 1,
                        'error_message' => $result['message'],
                    ]);

                    if ($queueItem->retry_count >= $maxRetries) {
                        $queueItem->update(['status' => 'failed']);
                    }
                }
            } catch (\Exception $e) {
                Log::error('FBR Queue Processing Error: ' . $e->getMessage(), [
                    'queue_item_id' => $queueItem->id,
                    'invoice_id' => $invoice->id,
                ]);

                $queueItem->update([
                    'status' => 'pending',
                    'retry_count' => $queueItem->retry_count + 1,
                    'error_message' => $e->getMessage(),
                ]);

                if ($queueItem->retry_count >= $maxRetries) {
                    $queueItem->update(['status' => 'failed']);
                }
            }
        }
    }

    public function getInvoiceStatus(Invoice $invoice)
    {
        try {
            $businessProfile = $invoice->businessProfile;
            
            if (!$businessProfile->fbr_api_token || !$invoice->usin) {
                return ['success' => false, 'message' => 'Missing FBR token or USIN'];
            }

            $baseUrl = $businessProfile->is_sandbox ? $this->sandboxBaseUrl : $this->productionBaseUrl;
            $endpoint = $baseUrl . '/getinvoicestatus';

            $response = Http::timeout(config('fbr.api_timeout', 30))
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $businessProfile->fbr_api_token,
                    'Content-Type' => 'application/json',
                ])->post($endpoint, [
                    'USIN' => $invoice->usin,
                ]);

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            } else {
                return ['success' => false, 'message' => $response->body()];
            }
        } catch (\Exception $e) {
            Log::error('FBR Status Check Error: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function cancelInvoice(Invoice $invoice, string $reason)
    {
        try {
            $businessProfile = $invoice->businessProfile;
            
            if (!$businessProfile->fbr_api_token || !$invoice->usin) {
                return ['success' => false, 'message' => 'Missing FBR token or USIN'];
            }

            $baseUrl = $businessProfile->is_sandbox ? $this->sandboxBaseUrl : $this->productionBaseUrl;
            $endpoint = $baseUrl . '/cancelinvoice';

            $response = Http::timeout(config('fbr.api_timeout', 30))
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $businessProfile->fbr_api_token,
                    'Content-Type' => 'application/json',
                ])->post($endpoint, [
                    'USIN' => $invoice->usin,
                    'CancellationReason' => $reason,
                ]);

            if ($response->successful()) {
                $invoice->update([
                    'status' => 'discarded',
                    'discard_reason' => $reason,
                    'discarded_at' => now(),
                    'discarded_by' => auth()->id(),
                ]);

                return ['success' => true, 'data' => $response->json()];
            } else {
                return ['success' => false, 'message' => $response->body()];
            }
        } catch (\Exception $e) {
            Log::error('FBR Cancellation Error: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function validateBusinessProfile($businessProfile)
    {
        $errors = [];

        if (!$businessProfile->fbr_api_token) {
            $errors[] = 'FBR API token is required';
        }

        if (!$businessProfile->strn_ntn) {
            $errors[] = 'STRN/NTN is required for FBR compliance';
        }

        if (!$businessProfile->province_code) {
            $errors[] = 'Province code is required';
        }

        if (!$businessProfile->address) {
            $errors[] = 'Business address is required';
        }

        if (empty($businessProfile->whitelisted_ips)) {
            $errors[] = 'At least one whitelisted IP is required';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    public function testConnection($businessProfile)
    {
        try {
            $baseUrl = $businessProfile->is_sandbox ? $this->sandboxBaseUrl : $this->productionBaseUrl;
            $endpoint = $baseUrl . '/ping';

            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $businessProfile->fbr_api_token,
                    'Content-Type' => 'application/json',
                ])->get($endpoint);

            return [
                'success' => $response->successful(),
                'status_code' => $response->status(),
                'message' => $response->successful() ? 'Connection successful' : $response->body()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}