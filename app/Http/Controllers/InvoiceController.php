<?php

namespace App\Http\Controllers;

use App\Models\BusinessProfile;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Get accessible business profile IDs (owned + shared)
        $ownedProfileIds = $user->businessProfiles()->pluck('id');
        $accessibleProfileIds = $user->accessibleBusinessProfiles()->pluck('id');
        $profileIds = $ownedProfileIds->merge($accessibleProfileIds)->unique();
        
        if ($profileIds->isEmpty()) {
            $stats = [
                'customers' => 0,
                'items' => 0,
                'invoices' => 0,
                'pending_invoices' => 0,
                'total_amount' => 0,
            ];
            $monthlyData = collect();
            $recentInvoices = collect();
        } else {
            $stats = [
                'customers' => Customer::whereIn('business_profile_id', $profileIds)->count(),
                'items' => Item::whereIn('business_profile_id', $profileIds)->count(),
                'invoices' => Invoice::whereIn('business_profile_id', $profileIds)->where('status', '!=', 'discarded')->count(),
                'pending_invoices' => Invoice::whereIn('business_profile_id', $profileIds)
                    ->where('fbr_status', 'pending')
                    ->where('status', '!=', 'discarded')
                    ->count(),
                'total_amount' => Invoice::whereIn('business_profile_id', $profileIds)
                    ->where('fbr_status', 'submitted')
                    ->where('status', '!=', 'discarded')
                    ->sum('total_amount'),
            ];

            // Monthly invoice data for chart
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Only draft invoices can be activated.');
        }

        $invoice->update(['status' => 'active']);
        
        // Queue for FBR submission
        app(FbrService::class)->queueInvoice($invoice);

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

        return view('dashboard', compact('stats', 'monthlyData', 'recentInvoices'));
    }
}