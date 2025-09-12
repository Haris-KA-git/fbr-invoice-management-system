<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function verify(Invoice $invoice)
    {
        $invoice->load(['businessProfile', 'customer', 'invoiceItems.item']);
        
        return view('invoices.verify', compact('invoice'));
    }
}