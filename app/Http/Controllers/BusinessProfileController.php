<?php

namespace App\Http\Controllers;

use App\Models\BusinessProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BusinessProfileController extends Controller
{
    public function index()
    {
        // Get profiles user owns or has access to
        $ownedProfiles = auth()->user()->businessProfiles();
        $accessibleProfiles = auth()->user()->accessibleBusinessProfiles();
        
        $profiles = $ownedProfiles->union($accessibleProfiles)
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

    public function users(BusinessProfile $businessProfile)
    {
        $this->authorize('view', $businessProfile);
        
        $businessProfile->load('users');
        
        // Get users not already added to this business profile
        $existingUserIds = $businessProfile->users->pluck('id');
        $availableUsers = User::whereNotIn('id', $existingUserIds)
            ->where('id', '!=', $businessProfile->user_id)
            ->orderBy('name')
            ->get();

        return view('business-profiles.users', compact('businessProfile', 'availableUsers'));
    }

    public function addUser(Request $request, BusinessProfile $businessProfile)
    {
        $this->authorize('update', $businessProfile);
        
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:viewer,editor,manager',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        // Check if user is already added
        if ($businessProfile->users()->where('user_id', $validated['user_id'])->exists()) {
            return redirect()->back()->with('error', 'User is already added to this business profile.');
        }

        $businessProfile->users()->attach($validated['user_id'], [
            'role' => $validated['role'],
            'permissions' => json_encode($validated['permissions'] ?? []),
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'User added successfully.');
    }

    public function updateUser(Request $request, BusinessProfile $businessProfile, User $user)
    {
        $this->authorize('update', $businessProfile);
        
        $validated = $request->validate([
            'role' => 'required|in:viewer,editor,manager,owner',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
            'is_active' => 'boolean',
        ]);

        // Only owner can assign owner role
        if ($validated['role'] === 'owner' && $user->id !== $businessProfile->user_id) {
            return redirect()->back()->with('error', 'Only the original owner can have owner role.');
        }

        $businessProfile->users()->updateExistingPivot($user->id, [
            'role' => $validated['role'],
            'permissions' => json_encode($validated['permissions'] ?? []),
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->back()->with('success', 'User access updated successfully.');
    }

    public function removeUser(BusinessProfile $businessProfile, User $user)
    {
        $this->authorize('update', $businessProfile);
        
        // Cannot remove the owner
        if ($user->id === $businessProfile->user_id) {
            return redirect()->back()->with('error', 'Cannot remove the business profile owner.');
        }

        $businessProfile->users()->detach($user->id);

        return redirect()->back()->with('success', 'User removed successfully.');
    }
}