<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Customer;
use App\Models\Item;
use App\Models\BusinessProfile;
use App\Services\FbrService;
use App\Services\InvoicePdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    protected $fbrService;
    protected $pdfService;

    public function __construct(FbrService $fbrService, InvoicePdfService $pdfService)
    {
        $this->fbrService = $fbrService;
        $this->pdfService = $pdfService;
    }

    public function index()
    {
        $profileIds = auth()->user()->businessProfiles()->pluck('id');
        
        $invoices = Invoice::whereIn('business_profile_id', $profileIds)
            ->with(['customer', 'businessProfile', 'invoiceItems'])
            ->when(request('status'), function($query) {
                $query->where('fbr_status', request('status'));
            })
            ->when(request('search'), function($query) {
                $query->where(function($q) {
                    $q->where('invoice_number', 'like', '%' . request('search') . '%')
                      ->orWhereHas('customer', function($customerQuery) {
                          $customerQuery->where('name', 'like', '%' . request('search') . '%');
                      });
                });
            })
            ->latest()
            ->paginate(15);

        return view('invoices.index', compact('invoices'));
    }

    public function create()
    {
        $profileIds = auth()->user()->businessProfiles()->pluck('id');
        $businessProfiles = auth()->user()->businessProfiles;
        $customers = Customer::whereIn('business_profile_id', $profileIds)
            ->with('businessProfile')
            ->orderBy('name')
            ->get();
        $items = Item::whereIn('business_profile_id', $profileIds)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('invoices.create', compact('businessProfiles', 'customers', 'items'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'business_profile_id' => 'required|exists:business_profiles,id',
            'customer_id' => 'required|exists:customers,id',
            'invoice_date' => 'required|date',
            'invoice_type' => 'required|in:sales,purchase,debit_note,credit_note',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.item_name' => 'required|string',
            'items.*.item_code' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.tax_rate' => 'required|numeric|min:0|max:100',
            'items.*.tax_amount' => 'required|numeric|min:0',
            'items.*.line_total' => 'required|numeric|min:0',
        ]);

        // Verify business profile and customer belong to user
        $businessProfile = auth()->user()->businessProfiles()
            ->findOrFail($validated['business_profile_id']);

        $customer = Customer::where('business_profile_id', $validated['business_profile_id'])
            ->findOrFail($validated['customer_id']);

        try {
            DB::transaction(function () use ($validated, $businessProfile) {
                // Generate invoice number
                $invoiceNumber = $this->generateInvoiceNumber($businessProfile->id);

                // Calculate totals from submitted data
                $subtotal = 0;
                $totalDiscount = 0;
                $totalTax = 0;

                foreach ($validated['items'] as $itemData) {
                    $quantity = $itemData['quantity'];
                    $unitPrice = $itemData['unit_price'];
                    $discountRate = $itemData['discount_rate'] ?? 0;
                    $taxRate = $itemData['tax_rate'];

                    $lineSubtotal = $quantity * $unitPrice;
                    $discountAmount = ($lineSubtotal * $discountRate) / 100;
                    $afterDiscount = $lineSubtotal - $discountAmount;
                    $taxAmount = ($afterDiscount * $taxRate) / 100;

                    $subtotal += $afterDiscount;
                    $totalDiscount += $discountAmount;
                    $totalTax += $taxAmount;
                }

                $totalAmount = $subtotal + $totalTax;

                // Create invoice
                $invoice = Invoice::create([
                    'business_profile_id' => $validated['business_profile_id'],
                    'customer_id' => $validated['customer_id'],
                    'user_id' => auth()->id(),
                    'invoice_number' => $invoiceNumber,
                    'invoice_date' => $validated['invoice_date'],
                    'invoice_type' => $validated['invoice_type'],
                    'subtotal' => $subtotal,
                    'discount_amount' => $totalDiscount,
                    'sales_tax' => $totalTax,
                    'total_amount' => $totalAmount,
                    'fbr_status' => 'pending',
                ]);

                // Create invoice items
                foreach ($validated['items'] as $itemData) {
                    $item = Item::findOrFail($itemData['item_id']);
                    $quantity = $itemData['quantity'];
                    $unitPrice = $itemData['unit_price'];
                    $discountRate = $itemData['discount_rate'] ?? 0;
                    $taxRate = $itemData['tax_rate'];

                    $lineSubtotal = $quantity * $unitPrice;
                    $discountAmount = ($lineSubtotal * $discountRate) / 100;
                    $afterDiscount = $lineSubtotal - $discountAmount;
                    $taxAmount = ($afterDiscount * $taxRate) / 100;
                    $lineTotal = $afterDiscount + $taxAmount;

                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'item_id' => $item->id,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'discount_rate' => $discountRate,
                        'discount_amount' => $discountAmount,
                        'tax_rate' => $taxRate,
                        'tax_amount' => $taxAmount,
                        'line_total' => $lineTotal,
                    ]);
                }

                // Queue for FBR submission
                $this->fbrService->queueInvoice($invoice);
            });

            return redirect()->route('invoices.index')
                ->with('success', 'Invoice created successfully and queued for FBR submission.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create invoice: ' . $e->getMessage());
        }
    }

    public function show(Invoice $invoice)
    {
        $this->authorize('view', $invoice);
        
        $invoice->load(['customer', 'businessProfile', 'invoiceItems.item']);
        
        return view('invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        $this->authorize('update', $invoice);
        
        // Only allow editing of pending or failed invoices
        if (!in_array($invoice->fbr_status, ['pending', 'failed'])) {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Cannot edit invoices that have been submitted to FBR.');
        }

        $profileIds = auth()->user()->businessProfiles()->pluck('id');
        $businessProfiles = auth()->user()->businessProfiles;
        $customers = Customer::whereIn('business_profile_id', $profileIds)
            ->with('businessProfile')
            ->orderBy('name')
            ->get();
        $items = Item::whereIn('business_profile_id', $profileIds)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('invoices.edit', compact('invoice', 'businessProfiles', 'customers', 'items'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        // Only allow editing of pending or failed invoices
        if (!in_array($invoice->fbr_status, ['pending', 'failed'])) {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Cannot edit invoices that have been submitted to FBR.');
        }

        $validated = $request->validate([
            'business_profile_id' => 'required|exists:business_profiles,id',
            'customer_id' => 'required|exists:customers,id',
            'invoice_date' => 'required|date',
            'invoice_type' => 'required|in:sales,purchase,debit_note,credit_note',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.item_name' => 'required|string',
            'items.*.item_code' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.tax_rate' => 'required|numeric|min:0|max:100',
            'items.*.tax_amount' => 'required|numeric|min:0',
            'items.*.line_total' => 'required|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($validated, $invoice) {
                // Calculate totals from submitted data
                $subtotal = 0;
                $totalDiscount = 0;
                $totalTax = 0;

                foreach ($validated['items'] as $itemData) {
                    $quantity = $itemData['quantity'];
                    $unitPrice = $itemData['unit_price'];
                    $discountRate = $itemData['discount_rate'] ?? 0;
                    $taxRate = $itemData['tax_rate'];

                    $lineSubtotal = $quantity * $unitPrice;
                    $discountAmount = ($lineSubtotal * $discountRate) / 100;
                    $afterDiscount = $lineSubtotal - $discountAmount;
                    $taxAmount = ($afterDiscount * $taxRate) / 100;

                    $subtotal += $afterDiscount;
                    $totalDiscount += $discountAmount;
                    $totalTax += $taxAmount;
                }

                $totalAmount = $subtotal + $totalTax;

                // Update invoice
                $invoice->update([
                    'business_profile_id' => $validated['business_profile_id'],
                    'customer_id' => $validated['customer_id'],
                    'invoice_date' => $validated['invoice_date'],
                    'invoice_type' => $validated['invoice_type'],
                    'subtotal' => $subtotal,
                    'discount_amount' => $totalDiscount,
                    'sales_tax' => $totalTax,
                    'total_amount' => $totalAmount,
                    'fbr_status' => 'pending',
                    'fbr_response' => null,
                    'fbr_error_message' => null,
                ]);

                // Delete existing invoice items
                $invoice->invoiceItems()->delete();

                // Create new invoice items
                foreach ($validated['items'] as $itemData) {
                    $item = Item::findOrFail($itemData['item_id']);
                    $quantity = $itemData['quantity'];
                    $unitPrice = $itemData['unit_price'];
                    $discountRate = $itemData['discount_rate'] ?? 0;
                    $taxRate = $itemData['tax_rate'];

                    $lineSubtotal = $quantity * $unitPrice;
                    $discountAmount = ($lineSubtotal * $discountRate) / 100;
                    $afterDiscount = $lineSubtotal - $discountAmount;
                    $taxAmount = ($afterDiscount * $taxRate) / 100;
                    $lineTotal = $afterDiscount + $taxAmount;

                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'item_id' => $item->id,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'discount_rate' => $discountRate,
                        'discount_amount' => $discountAmount,
                        'tax_rate' => $taxRate,
                        'tax_amount' => $taxAmount,
                        'line_total' => $lineTotal,
                    ]);
                }

                // Queue for FBR submission
                $this->fbrService->queueInvoice($invoice);
            });

            return redirect()->route('invoices.show', $invoice)
                ->with('success', 'Invoice updated successfully and queued for FBR submission.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update invoice: ' . $e->getMessage());
        }
    }

    public function downloadPdf(Invoice $invoice)
    {
        $this->authorize('view', $invoice);
        
        return $this->pdfService->generatePdf($invoice);
    }

    public function submitToFbr(Invoice $invoice)
    {
        $this->authorize('update', $invoice);
        
        if ($invoice->fbr_status === 'submitted') {
            return redirect()->back()->with('error', 'Invoice has already been submitted to FBR.');
        }

        $result = $this->fbrService->validateInvoice($invoice);
        
        if ($result['success']) {
            return redirect()->back()->with('success', 'Invoice submitted to FBR successfully.');
        } else {
            return redirect()->back()->with('error', 'Failed to submit invoice to FBR: ' . $result['message']);
        }
    }

    public function destroy(Invoice $invoice)
    {
        $this->authorize('delete', $invoice);

        // Only allow deletion of pending or failed invoices
        if (!in_array($invoice->fbr_status, ['pending', 'failed'])) {
            return redirect()->route('invoices.index')
                ->with('error', 'Cannot delete invoices that have been submitted to FBR.');
        }

        try {
            DB::transaction(function () use ($invoice) {
                // Delete invoice items first
                $invoice->invoiceItems()->delete();
                
                // Delete FBR queue entries
                $invoice->fbrQueue()->delete();
                
                // Delete the invoice
                $invoice->delete();
            });

            return redirect()->route('invoices.index')
                ->with('success', 'Invoice deleted successfully.');

        } catch (\Exception $e) {
            return redirect()->route('invoices.index')
                ->with('error', 'Failed to delete invoice: ' . $e->getMessage());
        }
    }

    private function generateInvoiceNumber($businessProfileId)
    {
        $prefix = 'INV-' . date('Y') . '-';
        $lastInvoice = Invoice::where('business_profile_id', $businessProfileId)
            ->where('invoice_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNumber = (int) str_replace($prefix, '', $lastInvoice->invoice_number);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }
}