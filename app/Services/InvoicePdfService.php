<?php

namespace App\Services;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class InvoicePdfService
{
    public function generatePdf(Invoice $invoice)
    {
        $invoice->load(['businessProfile', 'customer', 'invoiceItems.item']);
        
        $data = [
            'invoice' => $invoice,
            'qrCodePath' => ($invoice->qr_code_path && $invoice->fbr_status === 'submitted') ? 
                Storage::disk('public')->path($invoice->qr_code_path) : null,
        ];

        $pdf = Pdf::loadView('invoices.pdf', $data);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('invoice-' . $invoice->invoice_number . '.pdf');
    }

    public function generatePdfContent(Invoice $invoice)
    {
        $invoice->load(['businessProfile', 'customer', 'invoiceItems.item']);
        
        $data = [
            'invoice' => $invoice,
            'qrCodePath' => ($invoice->qr_code_path && $invoice->fbr_status === 'submitted') ? 
                Storage::disk('public')->path($invoice->qr_code_path) : null,
        ];

        $pdf = Pdf::loadView('invoices.pdf', $data);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->output();
    }
}