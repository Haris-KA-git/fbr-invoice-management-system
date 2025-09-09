<?php

namespace App\Http\Controllers;

use App\Models\BusinessProfile;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class ReportController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Get accessible business profile IDs
        if ($user->hasRole('Admin')) {
            $profileIds = BusinessProfile::pluck('id')->toArray();
        } else {
            $profileIds = $user->getAccessibleBusinessProfileIds();
        }
        
        $businessProfiles = BusinessProfile::whereIn('id', $profileIds)->get();
        
        return view('reports.index', compact('businessProfiles'));
    }

    public function salesReport(Request $request)
    {
        $user = auth()->user();
        
        if ($user->hasRole('Admin')) {
            $profileIds = BusinessProfile::pluck('id')->toArray();
        } else {
            $profileIds = $user->getAccessibleBusinessProfileIds();
        }

        $query = Invoice::with(['customer', 'businessProfile'])
            ->whereIn('business_profile_id', $profileIds)
            ->where('status', '!=', 'discarded');

        // Apply filters
        if ($request->business_profile_id) {
            $query->where('business_profile_id', $request->business_profile_id);
        }

        if ($request->date_from) {
            $query->whereDate('invoice_date', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('invoice_date', '<=', $request->date_to);
        }

        if ($request->fbr_status) {
            $query->where('fbr_status', $request->fbr_status);
        }

        $invoices = $query->latest('invoice_date')->paginate(20);
        
        // Summary statistics
        $totalInvoices = $query->count();
        $totalAmount = $query->sum('total_amount');
        $totalTax = $query->sum('sales_tax');
        
        $businessProfiles = BusinessProfile::whereIn('id', $profileIds)->get();

        return view('reports.sales', compact('invoices', 'businessProfiles', 'totalInvoices', 'totalAmount', 'totalTax'));
    }

    public function customerReport(Request $request)
    {
        $user = auth()->user();
        
        if ($user->hasRole('Admin')) {
            $profileIds = BusinessProfile::pluck('id')->toArray();
        } else {
            $profileIds = $user->getAccessibleBusinessProfileIds();
        }

        $query = Customer::with('businessProfile')
            ->whereIn('business_profile_id', $profileIds)
            ->withCount('invoices')
            ->withSum('invoices', 'total_amount');

        // Apply filters
        if ($request->business_profile_id) {
            $query->where('business_profile_id', $request->business_profile_id);
        }

        if ($request->customer_type) {
            $query->where('customer_type', $request->customer_type);
        }

        $customers = $query->orderBy('invoices_sum_total_amount', 'desc')->paginate(20);
        $businessProfiles = BusinessProfile::whereIn('id', $profileIds)->get();

        return view('reports.customers', compact('customers', 'businessProfiles'));
    }

    public function itemReport(Request $request)
    {
        $user = auth()->user();
        
        if ($user->hasRole('Admin') || $user->can('view reports')) {
            if ($user->hasRole('Admin')) {
                $profileIds = BusinessProfile::pluck('id')->toArray();
            } else {
                $profileIds = $user->getAccessibleBusinessProfileIds();
            }

            $query = Item::with('businessProfile')
                ->whereIn('business_profile_id', $profileIds)
                ->withCount('invoiceItems')
                ->withSum('invoiceItems', 'quantity')
                ->withSum('invoiceItems', 'line_total');

            // Apply filters
            if ($request->business_profile_id) {
                $query->where('business_profile_id', $request->business_profile_id);
            }

            $items = $query->orderBy('invoice_items_sum_line_total', 'desc')->paginate(20);
            $businessProfiles = BusinessProfile::whereIn('id', $profileIds)->get();

            return view('reports.items', compact('items', 'businessProfiles'));
        }

        abort(403, 'You do not have permission to view reports.');
    }

    public function taxReport(Request $request)
    {
        $user = auth()->user();
        
        if ($user->hasRole('Admin')) {
            $profileIds = BusinessProfile::pluck('id')->toArray();
        } else {
            $profileIds = $user->getAccessibleBusinessProfileIds();
        }

        // Monthly tax summary
        $monthlyTax = Invoice::whereIn('business_profile_id', $profileIds)
            ->where('fbr_status', 'submitted')
            ->where('status', '!=', 'discarded')
            ->select(
                DB::raw('YEAR(invoice_date) as year'),
                DB::raw('MONTH(invoice_date) as month'),
                DB::raw('COUNT(*) as invoice_count'),
                DB::raw('SUM(total_amount) as total_amount'),
                DB::raw('SUM(sales_tax) as total_tax')
            )
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        // Business profile tax summary
        $profileTax = Invoice::with('businessProfile')
            ->whereIn('business_profile_id', $profileIds)
            ->where('fbr_status', 'submitted')
            ->where('status', '!=', 'discarded')
            ->select(
                'business_profile_id',
                DB::raw('COUNT(*) as invoice_count'),
                DB::raw('SUM(total_amount) as total_amount'),
                DB::raw('SUM(sales_tax) as total_tax')
            )
            ->groupBy('business_profile_id')
            ->orderBy('total_tax', 'desc')
            ->get();

        return view('reports.tax', compact('monthlyTax', 'profileTax'));
    }

    public function exportSales(Request $request)
    {
        $user = auth()->user();
        
        if ($user->hasRole('Admin')) {
            $profileIds = BusinessProfile::pluck('id')->toArray();
        } else {
            $profileIds = $user->getAccessibleBusinessProfileIds();
        }

        $query = Invoice::with(['customer', 'businessProfile'])
            ->whereIn('business_profile_id', $profileIds)
            ->where('status', '!=', 'discarded');

        // Apply same filters as sales report
        if ($request->business_profile_id) {
            $query->where('business_profile_id', $request->business_profile_id);
        }

        if ($request->date_from) {
            $query->whereDate('invoice_date', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('invoice_date', '<=', $request->date_to);
        }

        if ($request->fbr_status) {
            $query->where('fbr_status', $request->fbr_status);
        }

        $invoices = $query->latest('invoice_date')->get();

        $csvData = [];
        $csvData[] = ['Invoice Number', 'Date', 'Customer', 'Business Profile', 'Type', 'Amount', 'Tax', 'FBR Status'];

        foreach ($invoices as $invoice) {
            $csvData[] = [
                $invoice->invoice_number,
                $invoice->invoice_date->format('Y-m-d'),
                $invoice->customer->name,
                $invoice->businessProfile->business_name,
                ucfirst(str_replace('_', ' ', $invoice->invoice_type)),
                $invoice->total_amount,
                $invoice->sales_tax,
                ucfirst($invoice->fbr_status),
            ];
        }

        $filename = 'sales-report-' . date('Y-m-d') . '.csv';

        return Response::streamDownload(function() use ($csvData) {
            $file = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}