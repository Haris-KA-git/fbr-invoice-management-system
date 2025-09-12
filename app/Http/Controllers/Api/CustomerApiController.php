<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\BusinessProfile;
use App\Services\CustomerImportService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Response;

class CustomerApiController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:10240',
            'business_profile_id' => 'required|exists:business_profiles,id'
        ]);

        // Check access to business profile
        $user = auth()->user();
        if (!$user->hasRole('Admin') && !in_array($request->business_profile_id, $user->getAccessibleBusinessProfileIds())) {
            return response()->json(['error' => 'You do not have access to this business profile.'], 403);
        }

        try {
            $results = CustomerImportService::importFromFile(
                $request->file('file'), 
                $request->business_profile_id
            );

            return response()->json([
                'success' => true,
                'message' => 'Import completed successfully',
                'results' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function export(Request $request)
    {
        $request->validate([
            'business_profile_id' => 'nullable|exists:business_profiles,id'
        ]);

        $user = auth()->user();
        
        // Get accessible business profile IDs
        if ($user->hasRole('Admin')) {
            $profileIds = BusinessProfile::pluck('id')->toArray();
        } else {
            $profileIds = $user->getAccessibleBusinessProfileIds();
        }

        $query = Customer::with('businessProfile')
            ->whereIn('business_profile_id', $profileIds);

        if ($request->business_profile_id) {
            $query->where('business_profile_id', $request->business_profile_id);
        }

        $customers = $query->get();

        $csvData = [];
        $csvData[] = ['Name', 'NTN/CNIC', 'Address', 'Contact Phone', 'Contact Email', 'Customer Type', 'Business Profile'];

        foreach ($customers as $customer) {
            $csvData[] = [
                $customer->name,
                $customer->ntn_cnic,
                $customer->address,
                $customer->contact_phone,
                $customer->contact_email,
                $customer->customer_type,
                $customer->businessProfile->business_name,
            ];
        }

        $filename = 'customers-export-' . date('Y-m-d-H-i-s') . '.csv';

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