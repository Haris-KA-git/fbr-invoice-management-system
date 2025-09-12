<?php

namespace App\Services;

use App\Models\Invoice;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

class QrCodeService
{
    public function generateInvoiceQrCode(Invoice $invoice): string
    {
        // FBR DI API v1.12 compliant QR code data
        $qrData = [
            'InvoiceNumber' => $invoice->invoice_number,
            'USIN' => $invoice->usin ?? '',
            'STRN' => $invoice->businessProfile->strn_ntn ?? '',
            'CustomerNTN' => $invoice->customer->ntn_cnic ?? '',
            'InvoiceDate' => $invoice->invoice_date->format('Y-m-d'),
            'TotalAmount' => number_format($invoice->total_amount, 2),
            'SalesTax' => number_format($invoice->sales_tax, 2),
            'BusinessName' => $invoice->businessProfile->business_name,
            'CustomerName' => $invoice->customer->name,
            'VerificationURL' => $invoice->fbr_verification_url ?? config('app.url') . '/verify/' . $invoice->id,
        ];

        // Create QR code content as per FBR requirements
        $qrContent = "Invoice: {$qrData['InvoiceNumber']}\n";
        $qrContent .= "USIN: {$qrData['USIN']}\n";
        $qrContent .= "Business: {$qrData['BusinessName']}\n";
        $qrContent .= "STRN: {$qrData['STRN']}\n";
        $qrContent .= "Customer: {$qrData['CustomerName']}\n";
        $qrContent .= "Date: {$qrData['InvoiceDate']}\n";
        $qrContent .= "Amount: PKR {$qrData['TotalAmount']}\n";
        $qrContent .= "Tax: PKR {$qrData['SalesTax']}\n";
        $qrContent .= "Verify: {$qrData['VerificationURL']}";

        // Generate QR code
        $qrCode = QrCode::format('png')
            ->size(200)
            ->margin(2)
            ->errorCorrection('M')
            ->generate($qrContent);

        // Save QR code
        $qrPath = 'qr-codes/invoice-' . $invoice->id . '.png';
        Storage::disk('public')->put($qrPath, $qrCode);

        // Update invoice with QR code path
        $invoice->update([
            'qr_code_path' => $qrPath,
            'fbr_verification_url' => $qrData['VerificationURL'],
        ]);

        return $qrPath;
    }

    public function getQrCodeData(Invoice $invoice): array
    {
        return [
            'InvoiceNumber' => $invoice->invoice_number,
            'USIN' => $invoice->usin ?? '',
            'STRN' => $invoice->businessProfile->strn_ntn ?? '',
            'CustomerNTN' => $invoice->customer->ntn_cnic ?? '',
            'InvoiceDate' => $invoice->invoice_date->format('Y-m-d'),
            'TotalAmount' => number_format($invoice->total_amount, 2),
            'SalesTax' => number_format($invoice->sales_tax, 2),
            'BusinessName' => $invoice->businessProfile->business_name,
            'CustomerName' => $invoice->customer->name,
            'VerificationURL' => $invoice->fbr_verification_url ?? config('app.url') . '/verify/' . $invoice->id,
        ];
    }
}