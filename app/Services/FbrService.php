<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\FbrQueue;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class FbrService
{
    private $sandboxBaseUrl = 'https://esp.fbr.gov.pk:8244/FBR/v1';
    private $productionBaseUrl = 'https://esp.fbr.gov.pk/FBR/v1';

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
                
                // Generate QR code
                $qrData = [
                    'InvoiceNumber' => $invoice->invoice_number,
                    'USIN' => $responseData['USIN'] ?? '',
                    'DateTime' => $invoice->invoice_date->format('Y-m-d H:i:s'),
                    'Amount' => $invoice->total_amount,
                ];
                
                $qrCode = QrCode::format('png')->size(100)->generate(json_encode($qrData));
                $qrPath = 'qr-codes/' . $invoice->id . '.png';
                \Storage::disk('public')->put($qrPath, $qrCode);

                $invoice->update([
                    'fbr_status' => 'submitted',
                    'fbr_response' => json_encode($responseData),
                    'fbr_invoice_number' => $responseData['USIN'] ?? null,
                    'qr_code' => $qrPath,
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
        
        $items = [];
        foreach ($invoice->invoiceItems as $invoiceItem) {
            $items[] = [
                'ItemCode' => $invoiceItem->item->item_code,
                'ItemName' => $invoiceItem->item->name,
                'HSCode' => $invoiceItem->item->hs_code,
                'UOMCode' => $invoiceItem->item->unit_of_measure,
                'Quantity' => $invoiceItem->quantity,
                'UnitPrice' => $invoiceItem->unit_price,
                'TotalAmount' => $invoiceItem->line_total,
                'GSTRate' => $invoiceItem->tax_rate,
                'TaxAmount' => $invoiceItem->tax_amount,
                'DiscountAmount' => $invoiceItem->discount_amount,
            ];
        }

        return [
            'InvoiceNumber' => $invoice->invoice_number,
            'InvoiceDate' => $invoice->invoice_date->format('Y-m-d'),
            'InvoiceType' => ucfirst(str_replace('_', ' ', $invoice->invoice_type)),
            'DocumentType' => 'Invoice',
            'Seller' => [
                'BusinessName' => $invoice->businessProfile->business_name,
                'STRN' => $invoice->businessProfile->strn_ntn,
                'Address' => $invoice->businessProfile->address,
                'ProvinceCode' => $invoice->businessProfile->province_code,
            ],
            'Buyer' => [
                'Name' => $invoice->customer->name,
                'NTN' => $invoice->customer->ntn_cnic,
                'Address' => $invoice->customer->address,
                'CustomerType' => $invoice->customer->customer_type,
            ],
            'Items' => $items,
            'Summary' => [
                'SubTotal' => $invoice->subtotal,
                'SalesTax' => $invoice->sales_tax,
                'TotalAmount' => $invoice->total_amount,
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