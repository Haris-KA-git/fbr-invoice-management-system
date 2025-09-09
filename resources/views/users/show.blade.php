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
        $businessProfiles = $user->businessProfiles()->count();
        
        // Get stats for user's business profiles
        $profileIds = $user->businessProfiles()->pluck('id');
        
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
            'invoices' => Invoice::whereIn('business_profile_id', $profileIds)->count(),
            'pending_invoices' => Invoice::whereIn('business_profile_id', $profileIds)
                ->where('fbr_status', 'pending')->count(),
            'total_amount' => Invoice::whereIn('business_profile_id', $profileIds)
                ->where('fbr_status', 'submitted')
                ->sum('total_amount'),
        ];

        // Monthly invoice data for chart
        $monthlyData = Invoice::whereIn('business_profile_id', $profileIds)
            ->select(
                DB::raw('MONTH(invoice_date) as month'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total_amount) as total')
            )
            ->whereYear('invoice_date', date('Y'))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Recent invoices
        $recentInvoices = Invoice::whereIn('business_profile_id', $profileIds)
            ->with(['customer', 'businessProfile'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        }

        return view('dashboard', compact('stats', 'monthlyData', 'recentInvoices'));
    }
}