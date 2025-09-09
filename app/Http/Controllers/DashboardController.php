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

        // Admins can see all business profiles, others see only accessible ones
        if ($user->hasRole('Admin')) {
            $profileIds = BusinessProfile::pluck('id');
        } else {
            // Get accessible business profile IDs (owned + shared)
            $profileIds = collect($user->getAccessibleBusinessProfileIds());
        $profileIds = $ownedProfileIds->merge($accessibleProfileIds)->unique();
        $profileIds = $ownedProfileIds->merge($accessibleProfileIds)->unique();
        
        if ($profileIds->isEmpty()) {
            $stats = [
                'customers' => 0,
                'items' => 0,
                'invoices' => 0,
                'draft_invoices' => 0,
                'active_invoices' => 0,
                'pending_invoices' => 0,
                'submitted_invoices' => 0,
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
            $monthlyData = Invoice::whereIn('business_profile_id', $profileIds)
                ->where('status', '!=', 'discarded')
                ->selectRaw('YEAR(invoice_date) as year, MONTH(invoice_date) as month, COUNT(*) as count')
                ->whereYear('invoice_date', date('Y'))
                ->groupBy('month', 'year')
                ->orderBy('month')
                ->get();

            // Recent invoices
            $recentInvoices = Invoice::whereIn('business_profile_id', $profileIds)
                ->where('status', '!=', 'discarded')
                ->with(['customer', 'businessProfile'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        }

        return view('dashboard', compact('stats', 'monthlyData', 'recentInvoices'));
    }
}