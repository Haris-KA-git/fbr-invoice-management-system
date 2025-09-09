<?php

namespace App\Http\Controllers;

use App\Models\BusinessProfile;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Item;
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

    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Get accessible business profile IDs
        if ($user->hasRole('Admin')) {
            $profileIds = BusinessProfile::pluck('id')->toArray();
        } else {
            $profileIds = $user->getAccessibleBusinessProfileIds();
        }

        $query = Invoice::with(['customer', 'businessProfile', 'invoiceItems'])
            ->whereIn('business_profile_id', $profileIds);

        // Apply filters
        if ($request->business_profile_id) {
            $query->where('business_profile_id', $request->business_profile_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->fbr_status) {
            $query->where('fbr_status', $request->fbr_status);
        }

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('invoice_number', 'like', '%' . $request->search . '%')
                  ->orWhere('fbr_invoice_number', 'like', '%' . $request->search . '%')
                  ->orWhereHas('customer', function($customerQuery) use ($request) {
                      $customerQuery->where('name', 'like', '%' . $request->search . '%');
                  });
            });
        }

        $invoices = $query->latest()->paginate(15);
        
        $businessProfiles = BusinessProfile::whereIn('id', $profileIds)->get();

        return view('invoices.index', compact('invoices', 'businessProfiles'));
    }

    public function create(Request $request)
    {
        $user = auth()->user();
        
        if ($user->hasRole('Admin')) {
            $profileIds = BusinessProfile::pluck('id')->toArray();
        } else {
            $profileIds = $user->getAccessibleBusinessProfileIds();
        }

        $businessProfiles = BusinessProfile::whereIn('id', $profileIds)->get();
        $customers = Customer::whereIn('business_profile_id', $profileIds)->get();
        $items = Item::whereIn('business_profile_id', $profileIds)->where('is_active', true)->get();

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
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.tax_rate' => 'required|numeric|min:0|max:100',
        ]);

        // Check access to business profile
        $user = auth()->user();
        if (!$user->hasRole('Admin') && !in_array($validated['business_profile_id'], $user->getAccessibleBusinessProfileIds())) {
            abort(403, 'You do not have access to this business profile.');
        }

        DB::beginTransaction();
        try {
            // Generate invoice number
            $businessProfile = BusinessProfile::find($validated['business_profile_id']);
            $lastInvoice = Invoice::where('business_profile_id', $validated['business_profile_id'])
                ->whereYear('invoice_date', date('Y', strtotime($validated['invoice_date'])))
                ->orderBy('id', 'desc')
                ->first();
            
            $nextNumber = $lastInvoice ? (int)substr($lastInvoice->invoice_number, -6) + 1 : 1;
            $invoiceNumber = 'INV-' . date('Y', strtotime($validated['invoice_date'])) . '-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

            // Calculate totals
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

            // Create invoice
            $invoice = Invoice::create([
                'business_profile_id' => $validated['business_profile_id'],
                'customer_id' => $validated['customer_id'],
                'user_id' => auth()->id(),
                'invoice_number' => $invoiceNumber,
                'invoice_date' => $validated['invoice_date'],
                'invoice_type' => $validated['invoice_type'],
                'status' => $request->has('save_as_draft') ? 'draft' : 'active',
                'subtotal' => $subtotal,
                'discount_amount' => $totalDiscount,
                'sales_tax' => $totalTax,
                'total_amount' => $subtotal + $totalTax,
                'fbr_status' => 'pending',
            ]);

            // Create invoice items
            foreach ($validated['items'] as $itemData) {
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
                    'item_id' => $itemData['item_id'],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount_rate' => $discountRate,
                    'discount_amount' => $discountAmount,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $taxAmount,
                    'line_total' => $lineTotal,
                ]);
            }

            // Queue for FBR submission if not draft
            if (!$request->has('save_as_draft')) {
                $this->fbrService->queueInvoice($invoice);
            }

            DB::commit();

            return redirect()->route('invoices.index')
                ->with('success', 'Invoice created successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Error creating invoice: ' . $e->getMessage());
        }
    }

    public function show(Invoice $invoice)
    {
        $this->authorize('view', $invoice);
        
        $invoice->load(['businessProfile', 'customer', 'user', 'invoiceItems.item', 'discardedBy']);
        
        return view('invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        $this->authorize('update', $invoice);
        
        $user = auth()->user();
        
        if ($user->hasRole('Admin')) {
            $profileIds = BusinessProfile::pluck('id')->toArray();
        } else {
            $profileIds = $user->getAccessibleBusinessProfileIds();
        }

        $businessProfiles = BusinessProfile::whereIn('id', $profileIds)->get();
        $customers = Customer::whereIn('business_profile_id', $profileIds)->get();
        $items = Item::whereIn('business_profile_id', $profileIds)->where('is_active', true)->get();

        $invoice->load(['invoiceItems.item']);

        return view('invoices.edit', compact('invoice', 'businessProfiles', 'customers', 'items'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        $validated = $request->validate([
            'business_profile_id' => 'required|exists:business_profiles,id',
            'customer_id' => 'required|exists:customers,id',
            'invoice_date' => 'required|date',
            'invoice_type' => 'required|in:sales,purchase,debit_note,credit_note',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.tax_rate' => 'required|numeric|min:0|max:100',
        ]);

        DB::beginTransaction();
        try {
            // Calculate totals
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

            // Update invoice
            $invoice->update([
                'business_profile_id' => $validated['business_profile_id'],
                'customer_id' => $validated['customer_id'],
                'invoice_date' => $validated['invoice_date'],
                'invoice_type' => $validated['invoice_type'],
                'status' => $request->has('save_as_draft') ? 'draft' : 'active',
                'subtotal' => $subtotal,
                'discount_amount' => $totalDiscount,
                'sales_tax' => $totalTax,
                'total_amount' => $subtotal + $totalTax,
            ]);

            // Delete existing items and create new ones
            $invoice->invoiceItems()->delete();

            foreach ($validated['items'] as $itemData) {
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
                    'item_id' => $itemData['item_id'],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount_rate' => $discountRate,
                    'discount_amount' => $discountAmount,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $taxAmount,
                    'line_total' => $lineTotal,
                ]);
            }

            DB::commit();

            return redirect()->route('invoices.show', $invoice)
                ->with('success', 'Invoice updated successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Error updating invoice: ' . $e->getMessage());
        }
    }

    public function destroy(Invoice $invoice)
    {
        $this->authorize('delete', $invoice);

        if ($invoice->status !== 'draft') {
            return back()->with('error', 'Only draft invoices can be deleted.');
        }

        $invoice->delete();

        return redirect()->route('invoices.index')
            ->with('success', 'Draft invoice deleted successfully.');
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
            return back()->with('error', 'Invoice has already been submitted to FBR.');
        }

        $result = $this->fbrService->queueInvoice($invoice);

        if ($result) {
            return back()->with('success', 'Invoice queued for FBR submission.');
        } else {
            return back()->with('error', 'Failed to queue invoice for FBR submission.');
        }
    }

    public function discard(Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        if ($invoice->status === 'discarded') {
            return back()->with('error', 'Invoice is already discarded.');
        }

        return view('invoices.discard', compact('invoice'));
    }

    public function storeDiscard(Request $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        $validated = $request->validate([
            'discard_reason' => 'required|string|max:1000',
        ]);

        $invoice->update([
            'status' => 'discarded',
            'discard_reason' => $validated['discard_reason'],
            'discarded_at' => now(),
            'discarded_by' => auth()->id(),
        ]);

        return redirect()->route('invoices.index')
            ->with('success', 'Invoice discarded successfully.');
    }

    public function restore(Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        if ($invoice->status !== 'discarded') {
            return back()->with('error', 'Only discarded invoices can be restored.');
        }

        $invoice->update([
            'status' => 'active',
            'discard_reason' => null,
            'discarded_at' => null,
            'discarded_by' => null,
        ]);

        return back()->with('success', 'Invoice restored successfully.');
    }

    public function activate(Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        if ($invoice->status !== 'draft') {
            return back()->with('error', 'Only draft invoices can be activated.');
        }

        $invoice->update(['status' => 'active']);
        
        // Queue for FBR submission
        $this->fbrService->queueInvoice($invoice);

        return back()->with('success', 'Invoice activated and queued for FBR submission.');
    }
}