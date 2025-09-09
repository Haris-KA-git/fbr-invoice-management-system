<?php

namespace App\Http\Controllers;

use App\Models\BusinessProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BusinessProfileController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        if ($user->hasRole('Admin')) {
            // Admins can see all business profiles
            $ownedProfiles = BusinessProfile::with('user')->get();
            $sharedProfiles = collect(); // Admins don't need shared profiles view
        } else {
            $ownedProfiles = $user->businessProfiles()->with('user')->get();
            $sharedProfiles = $user->accessibleBusinessProfiles()->with('user')->get();
        }

        return view('business-profiles.index', compact('ownedProfiles', 'sharedProfiles'));
    }

    public function create()
    {
        // Check if user can create more profiles
        if (!auth()->user()->canCreateBusinessProfile()) {
            return redirect()->route('business-profiles.index')
                ->with('error', 'You have reached your business profile limit.');
        }

        return view('business-profiles.create');
    }

    public function store(Request $request)
    {
        // Check if user can create more profiles
        if (!auth()->user()->canCreateBusinessProfile()) {
            return redirect()->route('business-profiles.index')
                ->with('error', 'You have reached your business profile limit.');
        }

        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'strn_ntn' => 'nullable|string|max:20',
            'cnic' => 'nullable|string|max:20',
            'address' => 'required|string|max:500',
            'province_code' => 'required|string|max:2',
            'branch_name' => 'nullable|string|max:255',
            'branch_code' => 'nullable|string|max:20',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'fbr_api_token' => 'nullable|string',
            'whitelisted_ips' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
            'is_sandbox' => 'boolean',
        ]);

        // Process whitelisted IPs
        if ($validated['whitelisted_ips']) {
            $validated['whitelisted_ips'] = array_map('trim', explode(',', $validated['whitelisted_ips']));
        }

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $validated['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        $validated['user_id'] = auth()->id();
        $validated['is_sandbox'] = $request->has('is_sandbox');

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
            'strn_ntn' => 'nullable|string|max:20',
            'cnic' => 'nullable|string|max:20',
            'address' => 'required|string|max:500',
            'province_code' => 'required|string|max:2',
            'branch_name' => 'nullable|string|max:255',
            'branch_code' => 'nullable|string|max:20',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'fbr_api_token' => 'nullable|string',
            'whitelisted_ips' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
            'is_sandbox' => 'boolean',
        ]);

        // Process whitelisted IPs
        if ($validated['whitelisted_ips']) {
            $validated['whitelisted_ips'] = array_map('trim', explode(',', $validated['whitelisted_ips']));
        }

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($businessProfile->logo_path) {
                Storage::disk('public')->delete($businessProfile->logo_path);
            }
            $validated['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        $validated['is_sandbox'] = $request->has('is_sandbox');

        $businessProfile->update($validated);

        return redirect()->route('business-profiles.show', $businessProfile)
            ->with('success', 'Business profile updated successfully.');
    }

    public function destroy(BusinessProfile $businessProfile)
    {
        $this->authorize('delete', $businessProfile);

        // Check if profile has any data
        if ($businessProfile->customers()->count() > 0 || 
            $businessProfile->items()->count() > 0 || 
            $businessProfile->invoices()->count() > 0) {
            return back()->with('error', 'Cannot delete business profile with existing data.');
        }

        // Delete logo if exists
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
        
        $businessProfile->load(['users', 'user']);
        
        // Get users that are not already added to this business profile
        $existingUserIds = $businessProfile->users()->pluck('users.id')->toArray();
        $existingUserIds[] = $businessProfile->user_id; // Exclude owner
        
        $availableUsers = User::whereNotIn('id', $existingUserIds)
            ->where('is_active', true)
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
        ]);

        // Check if user is already added
        if ($businessProfile->users()->where('users.id', $validated['user_id'])->exists()) {
            return back()->with('error', 'User is already added to this business profile.');
        }

        $businessProfile->users()->attach($validated['user_id'], [
            'role' => $validated['role'],
            'permissions' => json_encode($validated['permissions'] ?? []),
            'is_active' => true,
        ]);

        return back()->with('success', 'User added successfully.');
    }

    public function updateUser(Request $request, BusinessProfile $businessProfile, User $user)
    {
        $this->authorize('update', $businessProfile);

        $validated = $request->validate([
            'role' => 'required|in:viewer,editor,manager,owner',
            'permissions' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $businessProfile->users()->updateExistingPivot($user->id, [
            'role' => $validated['role'],
            'permissions' => json_encode($validated['permissions'] ?? []),
            'is_active' => $request->has('is_active'),
        ]);

        return back()->with('success', 'User access updated successfully.');
    }

    public function removeUser(BusinessProfile $businessProfile, User $user)
    {
        $this->authorize('update', $businessProfile);

        // Cannot remove the owner
        if ($user->id === $businessProfile->user_id) {
            return back()->with('error', 'Cannot remove the business profile owner.');
        }

        $businessProfile->users()->detach($user->id);

        return back()->with('success', 'User removed successfully.');
    }
}