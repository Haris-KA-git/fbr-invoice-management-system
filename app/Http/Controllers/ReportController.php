<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Item;
use App\Models\BusinessProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function salesReport(Request $request)
    {
        $profileIds = auth()->user()->businessProfiles()->pluck('id');
        
        $query = Invoice::whereIn('business_profile_id', $profileIds)
            ->with(['customer', 'businessProfile']);

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

        $invoices = $query->orderBy('invoice_date', 'desc')->paginate(15);

        // Summary statistics
        $totalAmount = $query->sum('total_amount');
        $totalTax = $query->sum('sales_tax');
        $totalInvoices = $query->count();

        $businessProfiles = auth()->user()->businessProfiles;

        return view('reports.sales', compact(
            'invoices', 
            'totalAmount', 
            'totalTax', 
            'totalInvoices', 
            'businessProfiles'
        ));
    }

    public function customerReport(Request $request)
    {
        $profileIds = auth()->user()->businessProfiles()->pluck('id');
        
        $customers = Customer::whereIn('business_profile_id', $profileIds)
            ->withCount('invoices')
            ->withSum('invoices', 'total_amount')
            ->with('businessProfile')
            ->when($request->business_profile_id, function($query) use ($request) {
                $query->where('business_profile_id', $request->business_profile_id);
            })
            ->when($request->customer_type, function($query) use ($request) {
                $query->where('customer_type', $request->customer_type);
            })
            ->orderBy('invoices_sum_total_amount', 'desc')
            ->paginate(15);

        $businessProfiles = auth()->user()->businessProfiles;

        return view('reports.customers', compact('customers', 'businessProfiles'));
    }

    public function itemReport(Request $request)
    {
        $profileIds = auth()->user()->businessProfiles()->pluck('id');
        
        $items = Item::whereIn('business_profile_id', $profileIds)
            ->withCount('invoiceItems')
            ->withSum('invoiceItems', 'quantity')
            ->withSum('invoiceItems', 'line_total')
            ->with('businessProfile')
            ->when($request->business_profile_id, function($query) use ($request) {
                $query->where('business_profile_id', $request->business_profile_id);
            })
            ->orderBy('invoice_items_sum_line_total', 'desc')
            ->paginate(15);

        $businessProfiles = auth()->user()->businessProfiles;

        return view('reports.items', compact('items', 'businessProfiles'));
    }

    public function taxReport(Request $request)
    {
        $profileIds = auth()->user()->businessProfiles()->pluck('id');
        
        $query = Invoice::whereIn('business_profile_id', $profileIds)
            ->where('fbr_status', 'submitted');

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

        // Tax summary by month
        $monthlyTax = $query->select(
                DB::raw('YEAR(invoice_date) as year'),
                DB::raw('MONTH(invoice_date) as month'),
                DB::raw('SUM(sales_tax) as total_tax'),
                DB::raw('SUM(total_amount) as total_amount'),
                DB::raw('COUNT(*) as invoice_count')
            )
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        // Tax summary by business profile
        $profileTax = Invoice::whereIn('business_profile_id', $profileIds)
            ->where('fbr_status', 'submitted')
            ->select('business_profile_id')
            ->selectRaw('SUM(sales_tax) as total_tax')
            ->selectRaw('SUM(total_amount) as total_amount')
            ->selectRaw('COUNT(*) as invoice_count')
            ->with('businessProfile')
            ->groupBy('business_profile_id')
            ->get();

        $businessProfiles = auth()->user()->businessProfiles;

        return view('reports.tax', compact('monthlyTax', 'profileTax', 'businessProfiles'));
    }

    public function exportSales(Request $request)
    {
        $profileIds = auth()->user()->businessProfiles()->pluck('id');
        
        $invoices = Invoice::whereIn('business_profile_id', $profileIds)
            ->with(['customer', 'businessProfile', 'invoiceItems.item'])
            ->when($request->business_profile_id, function($query) use ($request) {
                $query->where('business_profile_id', $request->business_profile_id);
            })
            ->when($request->date_from, function($query) use ($request) {
                $query->whereDate('invoice_date', '>=', $request->date_from);
            })
            ->when($request->date_to, function($query) use ($request) {
                $query->whereDate('invoice_date', '<=', $request->date_to);
            })
            ->orderBy('invoice_date', 'desc')
            ->get();

        $filename = 'sales_report_' . now()->format('Y_m_d_H_i_s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($invoices) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, [
                'Invoice Number',
                'FBR Invoice Number',
                'Date',
                'Business Profile',
                'Customer',
                'Customer Type',
                'Invoice Type',
                'Items Count',
                'Subtotal',
                'Tax',
                'Total Amount',
                'FBR Status'
            ]);

            foreach ($invoices as $invoice) {
                fputcsv($file, [
                    $invoice->invoice_number,
                    $invoice->fbr_invoice_number ?: 'N/A',
                    $invoice->invoice_date->format('Y-m-d'),
                    $invoice->businessProfile->business_name,
                    $invoice->customer->name,
                    ucfirst($invoice->customer->customer_type),
                    ucfirst(str_replace('_', ' ', $invoice->invoice_type)),
                    $invoice->invoiceItems->count(),
                    $invoice->subtotal,
                    $invoice->sales_tax,
                    $invoice->total_amount,
                    ucfirst($invoice->fbr_status)
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}