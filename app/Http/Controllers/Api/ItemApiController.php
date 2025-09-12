<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\BusinessProfile;
use App\Services\ItemImportService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Response;

class ItemApiController extends Controller
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
            $results = ItemImportService::importFromFile(
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

        $query = Item::with('businessProfile')
            ->whereIn('business_profile_id', $profileIds);

        if ($request->business_profile_id) {
            $query->where('business_profile_id', $request->business_profile_id);
        }

        $items = $query->get();

        $csvData = [];
        $csvData[] = ['Item Code', 'Name', 'Description', 'HS Code', 'Unit of Measure', 'Price', 'GST Rate (%)', 'Business Profile'];

        foreach ($items as $item) {
            $csvData[] = [
                $item->item_code,
                $item->name,
                $item->description,
                $item->hs_code,
                $item->unit_of_measure,
                $item->price,
                $item->tax_rate,
                $item->businessProfile->business_name,
            ];
        }

        $filename = 'items-export-' . date('Y-m-d-H-i-s') . '.csv';

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