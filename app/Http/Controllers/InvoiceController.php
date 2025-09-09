<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\BusinessProfile;
use App\Models\Customer;
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

    public function index()
    {
        $user = auth()->user();
        $ownedProfileIds = $user->businessProfiles()->pluck('id');
        $accessibleProfileIds = $user->accessibleBusinessProfiles()->pluck('id');
        $profileIds = $ownedProfileIds->merge($accessibleProfileIds)->unique();
        
        $invoices = Invoice::whereIn('business_profile_id', $profileIds)
            ->with(['customer', 'businessProfile', 'invoiceItems'])
            ->when(request('business_profile_id'), function($query) {
                $query->where('business_profile_id', request('business_profile_id'));
            })
            ->when(request('status'), function($query) {
                $query->where('status', request('status'));
            })
            ->when(request('fbr_status'), function($query) {
                $query->where('fbr_status', request('fbr_status'));
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

        $businessProfiles = $user->allAccessibleBusinessProfiles();

        return view('invoices.index', compact('invoices', 'businessProfiles'));
    }

    public function create()
    {
        $user = auth()->user();
        $businessProfiles = $user->allAccessibleBusinessProfiles();
        
        if ($businessProfiles->isEmpty()) {
            return redirect()->route('business-profiles.create')
                ->with('error', 'Please create a business profile first.');
        }

        $customers = Customer::whereIn('business_profile_id', $businessProfiles->pluck('id'))->get();
        $items = Item::whereIn('business_profile_id', $businessProfiles->pluck('id'))->get();

        return view('invoices.create', compact('businessProfiles', 'customers', 'items'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'business_profile_id' => 'required|exists:business_profiles,id',
            'customer_id' => 'required|exists:customers,id',
            'invoice_date' => 'required|date',
            'invoice_type' => 'required|in:sales,purchase,debit_note,credit_note',
            'save_as_draft' => 'boolean',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.tax_rate' => 'required|numeric|min:0|max:100',
        ]);

        // Verify user has access to business profile
        $user = auth()->user();
        $hasAccess = $user->businessProfiles()->where('id', $validated['business_profile_id'])->exists() ||
                    $user->hasBusinessProfileAccess($validated['business_profile_id'], 'create_invoices');

        if (!$hasAccess) {
            abort(403, 'You do not have permission to create invoices for this business profile.');
        }

        // Verify customer belongs to business profile
        $customer = Customer::where('id', $validated['customer_id'])
            ->where('business_profile_id', $validated['business_profile_id'])
            ->firstOrFail();

        DB::transaction(function () use ($validated, $user) {
            // Generate invoice number
            $businessProfile = BusinessProfile::findOrFail($validated['business_profile_id']);
            $year = date('Y', strtotime($validated['invoice_date']));
            $lastInvoice = Invoice::where('business_profile_id', $validated['business_profile_id'])
                ->whereYear('invoice_date', $year)
                ->orderBy('id', 'desc')
                ->first();
            
            $nextNumber = $lastInvoice ? (intval(substr($lastInvoice->invoice_number, -6)) + 1) : 1;
            $invoiceNumber = 'INV-' . $year . '-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

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
                'user_id' => $user->id,
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

            // Queue for FBR if active
            if ($invoice->status === 'active') {
                $this->fbrService->queueInvoice($invoice);
            }
        });

        $message = $request->has('save_as_draft') ? 'Invoice saved as draft successfully.' : 'Invoice created and queued for FBR submission.';
        
        return redirect()->route('invoices.index')->with('success', $message);
    }

    public function show(Invoice $invoice)
    {
        $this->authorize('view', $invoice);
        
        $invoice->load(['businessProfile', 'customer', 'invoiceItems.item', 'discardedBy']);
        
        return view('invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        $this->authorize('update', $invoice);
        
        // Only allow editing of draft, pending, or failed invoices
        if (!in_array($invoice->status, ['draft']) && !in_array($invoice->fbr_status, ['pending', 'failed'])) {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Cannot edit invoices that have been submitted to FBR.');
        }

        $user = auth()->user();
        $businessProfiles = $user->allAccessibleBusinessProfiles();
        $customers = Customer::whereIn('business_profile_id', $businessProfiles->pluck('id'))->get();
        $items = Item::whereIn('business_profile_id', $businessProfiles->pluck('id'))->get();

        return view('invoices.edit', compact('invoice', 'businessProfiles', 'customers', 'items'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);
        
        // Only allow editing of draft, pending, or failed invoices
        if (!in_array($invoice->status, ['draft']) && !in_array($invoice->fbr_status, ['pending', 'failed'])) {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Cannot edit invoices that have been submitted to FBR.');
        }

        $validated = $request->validate([
            'business_profile_id' => 'required|exists:business_profiles,id',
            'customer_id' => 'required|exists:customers,id',
            'invoice_date' => 'required|date',
            'invoice_type' => 'required|in:sales,purchase,debit_note,credit_note',
            'save_as_draft' => 'boolean',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.tax_rate' => 'required|numeric|min:0|max:100',
        ]);

        DB::transaction(function () use ($validated, $invoice, $request) {
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
                'fbr_status' => 'pending',
            ]);

            // Delete existing items and recreate
            $invoice->invoiceItems()->delete();

            // Create new invoice items
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

            // Queue for FBR if active
            if ($invoice->status === 'active') {
                $this->fbrService->queueInvoice($invoice);
            }
        });

        $message = $request->has('save_as_draft') ? 'Invoice saved as draft successfully.' : 'Invoice updated and queued for FBR submission.';
        
        return redirect()->route('invoices.show', $invoice)->with('success', $message);
    }

    public function destroy(Invoice $invoice)
    {
        $this->authorize('delete', $invoice);

        // Only allow deletion of draft invoices
        if ($invoice->status !== 'draft') {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Only draft invoices can be deleted. Use discard for active invoices.');
        }

        $invoice->delete();

        return redirect()->route('invoices.index')
            ->with('success', 'Invoice deleted successfully.');
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
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Invoice has already been submitted to FBR.');
        }

        $result = $this->fbrService->validateInvoice($invoice);
        
        if ($result['success']) {
            return redirect()->route('invoices.show', $invoice)
                ->with('success', 'Invoice submitted to FBR successfully.');
        } else {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'FBR submission failed: ' . $result['message']);
        }
    }

    public function activate(Invoice $invoice)
    {
        $this->authorize('update', $invoice);
        
        if ($invoice->status !== 'draft') {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Only draft invoices can be activated.');
        }

        $invoice->update(['status' => 'active']);
        
        // Queue for FBR submission
        $this->fbrService->queueInvoice($invoice);

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice activated and queued for FBR submission.');
    }

    public function discard(Invoice $invoice)
    {
        $this->authorize('update', $invoice);
        
        if ($invoice->status === 'discarded') {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Invoice is already discarded.');
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
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Only discarded invoices can be restored.');
        }

        $invoice->update([
            'status' => 'active',
            'discard_reason' => null,
            'discarded_at' => null,
            'discarded_by' => null,
        ]);

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice restored successfully.');
    }
}