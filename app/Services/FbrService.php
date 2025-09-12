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
            $payload = $this->prepareInvoiceData($invoice);
            
            $baseUrl = $businessProfile->is_sandbox ? $this->sandboxBaseUrl : $this->productionBaseUrl;
            $endpoint = $baseUrl . '/validateinvoicedata';

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $businessProfile->fbr_api_token,
                'Content-Type' => 'application/json',
            ])->post($endpoint, $payload);

            if ($response->successful()) {
                $responseData = $response->json();
                
                $invoice->update([
                    'fbr_status' => 'validated',
                    'fbr_response' => json_encode($responseData),
                    'fbr_json_data' => $payload,
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
            Log::error('FBR Validation Error: ' . $e->getMessage());
            
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
            $payload = $this->prepareInvoiceData($invoice);
            
            $baseUrl = $businessProfile->is_sandbox ? $this->sandboxBaseUrl : $this->productionBaseUrl;
            $endpoint = $baseUrl . '/postinvoicedata';

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $businessProfile->fbr_api_token,
                'Content-Type' => 'application/json',
            ])->post($endpoint, $payload);

            if ($response->successful()) {
                $responseData = $response->json();
                
                // Generate QR code with FBR compliant data
                $qrPath = $this->qrCodeService->generateInvoiceQrCode($invoice);

                $invoice->update([
                    'fbr_status' => 'submitted',
                    'fbr_response' => json_encode($responseData),
                    'usin' => $responseData['USIN'] ?? null,
                    'fbr_invoice_number' => $responseData['InvoiceNumber'] ?? null,
                    'fbr_verification_url' => $responseData['VerificationURL'] ?? null,
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
            Log::error('FBR Submission Error: ' . $e->getMessage());
            
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
        foreach ($invoice->invoiceItems as $invoiceItem) {
            $items[] = [
                'ItemCode' => $invoiceItem->item->item_code,
                'ItemName' => $invoiceItem->item->name,
                'ItemDescription' => $invoiceItem->item->description ?? '',
                'HSCode' => $invoiceItem->item->hs_code,
                'PCTCode' => $invoiceItem->item->hs_code, // Pakistan Customs Tariff Code
                'UOM' => $invoiceItem->item->unit_of_measure,
                'UOMCode' => $invoiceItem->item->unit_of_measure,
                'Quantity' => (float) $invoiceItem->quantity,
                'UnitPrice' => (float) $invoiceItem->unit_price,
                'TotalAmount' => (float) $invoiceItem->line_total,
                'TaxableAmount' => (float) ($invoiceItem->quantity * $invoiceItem->unit_price - $invoiceItem->discount_amount),
                'SalesTaxRate' => (float) $invoiceItem->tax_rate,
                'SalesTaxAmount' => (float) $invoiceItem->tax_amount,
                'DiscountRate' => (float) $invoiceItem->discount_rate,
                'DiscountAmount' => (float) $invoiceItem->discount_amount,
                'FurtherTaxRate' => 0.0,
                'FurtherTaxAmount' => 0.0,
                'TotalAmountWithTax' => (float) $invoiceItem->line_total,
            ];
        }

        // FBR DI API v1.12 compliant invoice structure
        return [
            'InvoiceNumber' => $invoice->invoice_number,
            'InvoiceType' => $this->mapInvoiceType($invoice->invoice_type),
            'DocumentType' => 'Invoice',
            'InvoiceDate' => $invoice->invoice_date->format('Y-m-d'),
            'InvoiceTime' => $invoice->created_at->format('H:i:s'),
            'CurrencyCode' => 'PKR',
            'ExchangeRate' => 1.0,
            'InvoiceValue' => (float) $invoice->total_amount,
            'TotalSalesTax' => (float) $invoice->sales_tax,
            'TotalAmount' => (float) $invoice->total_amount,
            'PaymentMode' => 'Cash', // Default payment mode
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
            ],
            'Buyer' => [
                'Name' => $invoice->customer->name,
                'NTN' => $invoice->customer->customer_type === 'registered' ? $invoice->customer->ntn_cnic : '',
                'CNIC' => $invoice->customer->customer_type === 'unregistered' ? $invoice->customer->ntn_cnic : '',
                'STRN' => $invoice->customer->customer_type === 'registered' ? $invoice->customer->ntn_cnic : '',
                'Address' => $invoice->customer->address,
                'City' => $this->extractCity($invoice->customer->address ?? ''),
                'Country' => 'Pakistan',
                'ContactNumber' => $invoice->customer->contact_phone ?? '',
                'EmailAddress' => $invoice->customer->contact_email ?? '',
            ],
            'Items' => $items,
            'PaymentTerms' => 'Immediate',
            'Remarks' => 'FBR Compliant Digital Invoice',
        ];
    }

    private function mapInvoiceType(string $type): string
    {
        return match($type) {
            'sales' => 'Sales Invoice',
            'purchase' => 'Purchase Invoice',
            'debit_note' => 'Debit Note',
            'credit_note' => 'Credit Note',
            default => 'Sales Invoice'
        };
    }

    private function extractCity(string $address): string
    {
        // Simple city extraction - look for common Pakistani cities
        $cities = ['Karachi', 'Lahore', 'Islamabad', 'Rawalpindi', 'Faisalabad', 'Multan', 'Peshawar', 'Quetta', 'Sialkot', 'Gujranwala'];
        
        foreach ($cities as $city) {
            if (stripos($address, $city) !== false) {
                return $city;
            }
        }
        
        // If no city found, try to extract from address parts
        $parts = explode(',', $address);
        return trim($parts[count($parts) - 2] ?? 'Lahore'); // Default to Lahore
    }

    private function extractPostalCode(string $address): string
    {
        // Extract postal code pattern (5 digits)
        if (preg_match('/\b\d{5}\b/', $address, $matches)) {
            return $matches[0];
        }
        return '54000'; // Default postal code
    }
}

            ],
        ];
    }

    public function processQueue()
    {
        $queueItems = FbrQueue::where('status', 'pending')
            ->where('retry_count', '<', 5)
            ->orderBy('created_at')
            ->limit(10)
            ->get();

        foreach ($queueItems as $queueItem) {
            $queueItem->update([
                'status' => 'processing',
                'last_retry_at' => now(),
            ]);

            $invoice = $queueItem->invoice;
            
            if ($queueItem->action === 'validate') {
                $result = $this->validateInvoice($invoice);
            } else {
                $result = $this->submitInvoice($invoice);
            }

            if ($result['success']) {
                $queueItem->update(['status' => 'completed']);
            } else {
                $queueItem->update([
                    'status' => 'pending',
                    'retry_count' => $queueItem->retry_count + 1,
                    'error_message' => $result['message'],
                ]);

                if ($queueItem->retry_count >= 5) {
                    $queueItem->update(['status' => 'failed']);
                }
            }
        }
    }
}