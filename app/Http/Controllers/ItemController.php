<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\BusinessProfile;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index()
    {
        $profileIds = auth()->user()->businessProfiles()->pluck('id');
        
        $items = Item::whereIn('business_profile_id', $profileIds)
            ->with('businessProfile')
            ->when(request('business_profile_id'), function($query) {
                $query->where('business_profile_id', request('business_profile_id'));
            })
            ->when(request('search'), function($query) {
                $query->where(function($q) {
                    $q->where('name', 'like', '%' . request('search') . '%')
                      ->orWhere('item_code', 'like', '%' . request('search') . '%')
                      ->orWhere('description', 'like', '%' . request('search') . '%');
                });
            })
            ->when(request('status'), function($query) {
                if (request('status') === 'active') {
                    $query->where('is_active', true);
                } elseif (request('status') === 'inactive') {
                    $query->where('is_active', false);
                }
            })
            ->latest()
            ->paginate(15);

        $businessProfiles = auth()->user()->businessProfiles;

        return view('items.index', compact('items', 'businessProfiles'));
    }

    public function create()
    {
        $businessProfiles = auth()->user()->businessProfiles;
        
        return view('items.create', compact('businessProfiles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'business_profile_id' => 'required|exists:business_profiles,id',
            'item_code' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'hs_code' => 'nullable|string|max:255',
            'unit_of_measure' => 'required|string|max:255',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'price' => 'required|numeric|min:0',
            'sro_references' => 'nullable|string',
        ]);

        // Verify business profile belongs to user
        $businessProfile = auth()->user()->businessProfiles()
            ->findOrFail($validated['business_profile_id']);

        // Check if item code is unique for this business profile
        $exists = Item::where('business_profile_id', $validated['business_profile_id'])
            ->where('item_code', $validated['item_code'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['item_code' => 'Item code already exists for this business profile.']);
        }

        if ($request->sro_references) {
            $validated['sro_references'] = array_map('trim', explode(',', $request->sro_references));
        }

        Item::create($validated);

        return redirect()->route('items.index')
            ->with('success', 'Item created successfully.');
    }

    public function show(Item $item)
    {
        $this->authorize('view', $item);
        
        return view('items.show', compact('item'));
    }

    public function edit(Item $item)
    {
        $this->authorize('update', $item);
        
        $businessProfiles = auth()->user()->businessProfiles;
        
        return view('items.edit', compact('item', 'businessProfiles'));
    }

    public function update(Request $request, Item $item)
    {
        $this->authorize('update', $item);

        $validated = $request->validate([
            'business_profile_id' => 'required|exists:business_profiles,id',
            'item_code' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'hs_code' => 'nullable|string|max:255',
            'unit_of_measure' => 'required|string|max:255',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'price' => 'required|numeric|min:0',
            'sro_references' => 'nullable|string',
        ]);

        // Verify business profile belongs to user
        $businessProfile = auth()->user()->businessProfiles()
            ->findOrFail($validated['business_profile_id']);

        // Check if item code is unique for this business profile (excluding current item)
        $exists = Item::where('business_profile_id', $validated['business_profile_id'])
            ->where('item_code', $validated['item_code'])
            ->where('id', '!=', $item->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['item_code' => 'Item code already exists for this business profile.']);
        }

        if ($request->sro_references) {
            $validated['sro_references'] = array_map('trim', explode(',', $request->sro_references));
        }

        $item->update($validated);

        return redirect()->route('items.index')
            ->with('success', 'Item updated successfully.');
    }

    public function destroy(Item $item)
    {
        $this->authorize('delete', $item);

        $item->delete();

        return redirect()->route('items.index')
            ->with('success', 'Item deleted successfully.');
    }
}