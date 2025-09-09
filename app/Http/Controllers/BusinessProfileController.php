<?php

namespace App\Http\Controllers;

use App\Models\BusinessProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BusinessProfileController extends Controller
{
    public function index()
    {
        $profiles = auth()->user()->businessProfiles()
            ->latest()
            ->paginate(10);

        return view('business-profiles.index', compact('profiles'));
    }

    public function create()
    {
        return view('business-profiles.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'strn_ntn' => 'nullable|string|max:255',
            'cnic' => 'nullable|string|max:255',
            'address' => 'required|string',
            'province_code' => 'required|string|max:10',
            'branch_name' => 'nullable|string|max:255',
            'branch_code' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'fbr_api_token' => 'nullable|string',
            'whitelisted_ips' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_sandbox' => 'boolean',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        if ($request->whitelisted_ips) {
            $validated['whitelisted_ips'] = array_map('trim', explode(',', $request->whitelisted_ips));
        }

        $validated['user_id'] = auth()->id();

        BusinessProfile::create($validated);

        return redirect()->route('business-profiles.index')
            ->with('success', 'Business profile created successfully.');
    }

    public function show(BusinessProfile $businessProfile)
    {
        $this->authorize('view', $businessProfile);
        
        return view('business-profiles.show', compact('businessProfile'));
    }

    public function edit(BusinessProfile $businessProfile)
    {
        $this->authorize('update', $businessProfile);
        
        return view('business-profiles.edit', compact('businessProfile'));
    }

    public function update(Request $request, BusinessProfile $businessProfile)
    {
        $this->authorize('update', $businessProfile);

        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'strn_ntn' => 'nullable|string|max:255',
            'cnic' => 'nullable|string|max:255',
            'address' => 'required|string',
            'province_code' => 'required|string|max:10',
            'branch_name' => 'nullable|string|max:255',
            'branch_code' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'fbr_api_token' => 'nullable|string',
            'whitelisted_ips' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_sandbox' => 'boolean',
        ]);

        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($businessProfile->logo_path) {
                Storage::disk('public')->delete($businessProfile->logo_path);
            }
            $validated['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        if ($request->whitelisted_ips) {
            $validated['whitelisted_ips'] = array_map('trim', explode(',', $request->whitelisted_ips));
        }

        $businessProfile->update($validated);

        return redirect()->route('business-profiles.index')
            ->with('success', 'Business profile updated successfully.');
    }

    public function destroy(BusinessProfile $businessProfile)
    {
        $this->authorize('delete', $businessProfile);

        if ($businessProfile->logo_path) {
            Storage::disk('public')->delete($businessProfile->logo_path);
        }

        $businessProfile->delete();

        return redirect()->route('business-profiles.index')
            ->with('success', 'Business profile deleted successfully.');
    }
}