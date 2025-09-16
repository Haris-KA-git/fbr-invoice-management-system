<?php

namespace App\Http\Controllers;

use App\Models\BusinessProfile;
use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Get accessible business profile IDs
        if ($user->hasRole('Admin')) {
            $profileIds = BusinessProfile::pluck('id')->toArray();
        } else {
            $profileIds = $user->getAccessibleBusinessProfileIds();
        }

        $query = Item::with('businessProfile')
            ->whereIn('business_profile_id', $profileIds);

        // Apply filters
        if ($request->business_profile_id) {
            $query->where('business_profile_id', $request->business_profile_id);
        }

        if ($request->status) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('item_code', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        $items = $query->latest()->paginate(15);
        $businessProfiles = BusinessProfile::whereIn('id', $profileIds)->get();

        return view('items.index', compact('items', 'businessProfiles'));
    }

    public function create()
    {
        $user = auth()->user();
        
        if ($user->hasRole('Admin')) {
            $profileIds = BusinessProfile::pluck('id')->toArray();
        } else {
            $profileIds = $user->getAccessibleBusinessProfileIds();
        }

        $businessProfiles = BusinessProfile::whereIn('id', $profileIds)->get();
        $uoms = \App\Models\Uom::active()->orderBy('category')->orderBy('name')->get();

        return view('items.create', compact('businessProfiles', 'uoms'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'business_profile_id' => 'required|exists:business_profiles,id',
            'item_code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'hs_code' => 'required|string|max:20',
            'uom_id' => 'required|exists:uoms,id',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'price' => 'required|numeric|min:0',
            'sro_references' => 'nullable|string',
        ]);

        // Check access to business profile
        $user = auth()->user();
        if (!$user->hasRole('Admin') && !in_array($validated['business_profile_id'], $user->getAccessibleBusinessProfileIds())) {
            abort(403, 'You do not have access to this business profile.');
        }

        // Check for duplicate item code within business profile
        $existingItem = Item::where('business_profile_id', $validated['business_profile_id'])
            ->where('item_code', $validated['item_code'])
            ->first();

        if ($existingItem) {
            return back()->withInput()
                ->withErrors(['item_code' => 'Item code already exists for this business profile.']);
        }

        // Process SRO references
        if ($validated['sro_references']) {
            $validated['sro_references'] = array_map('trim', explode(',', $validated['sro_references']));
        }

        Item::create($validated);

        return redirect()->route('items.index')
            ->with('success', 'Item created successfully.');
    }

    public function show(Item $item)
    {
        $this->authorize('view', $item);
        
        $item->load('businessProfile');
        
        return view('items.show', compact('item'));
    }

    public function edit(Item $item)
    {
        $this->authorize('update', $item);
        
        $user = auth()->user();
        
        if ($user->hasRole('Admin')) {
            $profileIds = BusinessProfile::pluck('id')->toArray();
        } else {
            $profileIds = $user->getAccessibleBusinessProfileIds();
        }

        $businessProfiles = BusinessProfile::whereIn('id', $profileIds)->get();
        $uoms = \App\Models\Uom::active()->orderBy('category')->orderBy('name')->get();

        return view('items.edit', compact('item', 'businessProfiles', 'uoms'));
    }

    public function update(Request $request, Item $item)
    {
        $this->authorize('update', $item);

        $validated = $request->validate([
            'business_profile_id' => 'required|exists:business_profiles,id',
            'item_code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'hs_code' => 'required|string|max:20',
            'uom_id' => 'required|exists:uoms,id',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'price' => 'required|numeric|min:0',
            'sro_references' => 'nullable|string',
        ]);

        // Check for duplicate item code within business profile (excluding current item)
        $existingItem = Item::where('business_profile_id', $validated['business_profile_id'])
            ->where('item_code', $validated['item_code'])
            ->where('id', '!=', $item->id)
            ->first();

        if ($existingItem) {
            return back()->withInput()
                ->withErrors(['item_code' => 'Item code already exists for this business profile.']);
        }

        // Process SRO references
        if ($validated['sro_references']) {
            $validated['sro_references'] = array_map('trim', explode(',', $validated['sro_references']));
        }

        $item->update($validated);

        return redirect()->route('items.show', $item)
            ->with('success', 'Item updated successfully.');
    }

    public function destroy(Item $item)
    {
        $this->authorize('delete', $item);

        // Check if item is used in any invoices
        if ($item->invoiceItems()->count() > 0) {
            return back()->with('error', 'Cannot delete item that is used in invoices.');
        }

        $item->delete();

        return redirect()->route('items.index')
            ->with('success', 'Item deleted successfully.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:10240',
            'business_profile_id' => 'required|exists:business_profiles,id'
        ]);

        // Check access to business profile
        $user = auth()->user();
        if (!$user->hasRole('Admin') && !in_array($request->business_profile_id, $user->getAccessibleBusinessProfileIds())) {
            return back()->with('error', 'You do not have access to this business profile.');
        }

        try {
            $results = \App\Services\ItemImportService::importFromFile(
                $request->file('file'), 
                $request->business_profile_id
            );

            return back()->with('success', 
                "Import completed! Imported: {$results['imported']}, Updated: {$results['updated']}, Skipped: {$results['skipped']}"
            );
        } catch (\Exception $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
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
        $csvData[] = ['Item Code', 'Name', 'Description', 'HS Code', 'Unit of Measure', 'Price', 'GST Rate (%)', 'Business Profile', 'Business Profile ID'];

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
                $item->business_profile_id,
            ];
        }

        $filename = 'items-export-' . date('Y-m-d-H-i-s') . '.csv';

        return \Illuminate\Support\Facades\Response::streamDownload(function() use ($csvData) {
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