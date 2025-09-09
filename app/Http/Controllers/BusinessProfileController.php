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
        
        // Get owned business profiles
        $ownedProfiles = $user->businessProfiles()->with(['customers', 'items', 'invoices'])->get();
        
        // Get shared business profiles
        $sharedProfiles = $user->accessibleBusinessProfiles()
            ->with(['customers', 'items', 'invoices'])
            ->get();

        return view('business-profiles.index', compact('ownedProfiles', 'sharedProfiles'));
    }

    public function create()
    {
        $user = auth()->user();
        
        // Check if user can create more business profiles
        if (!$user->canCreateBusinessProfile()) {
            return redirect()->route('business-profiles.index')
                ->with('error', 'You have reached your business profile limit (' . $user->business_profile_limit . '). Contact an administrator to increase your limit.');
        }

        return view('business-profiles.create');
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        
        // Check if user can create more business profiles
        if (!$user->canCreateBusinessProfile()) {
            return redirect()->route('business-profiles.index')
                ->with('error', 'You have reached your business profile limit (' . $user->business_profile_limit . '). Contact an administrator to increase your limit.');
        }

        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'strn_ntn' => 'nullable|string|max:255',
            'cnic' => 'nullable|string|max:255',
            'address' => 'required|string',
            'province_code' => 'required|string|max:2',
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

        $validated['user_id'] = $user->id;
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
            'strn_ntn' => 'nullable|string|max:255',
            'cnic' => 'nullable|string|max:255',
            'address' => 'required|string',
            'province_code' => 'required|string|max:2',
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

        $validated['is_sandbox'] = $request->has('is_sandbox');

        $businessProfile->update($validated);

        return redirect()->route('business-profiles.index')
            ->with('success', 'Business profile updated successfully.');
    }

    public function destroy(BusinessProfile $businessProfile)
    {
        $this->authorize('delete', $businessProfile);

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
        
        // Only owners can manage users
        if ($businessProfile->user_id !== auth()->id()) {
            abort(403, 'Only the business profile owner can manage users.');
        }

        $availableUsers = User::whereNotIn('id', $businessProfile->users()->pluck('users.id'))
            ->where('id', '!=', $businessProfile->user_id)
            ->where('is_active', true)
            ->get();

        return view('business-profiles.users', compact('businessProfile', 'availableUsers'));
    }

    public function addUser(Request $request, BusinessProfile $businessProfile)
    {
        $this->authorize('update', $businessProfile);
        
        // Only owners can manage users
        if ($businessProfile->user_id !== auth()->id()) {
            abort(403, 'Only the business profile owner can manage users.');
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:viewer,editor,manager',
            'permissions' => 'array',
        ]);

        $businessProfile->users()->attach($validated['user_id'], [
            'role' => $validated['role'],
            'permissions' => json_encode($validated['permissions'] ?? []),
            'is_active' => true,
        ]);

        return redirect()->route('business-profiles.users', $businessProfile)
            ->with('success', 'User added successfully.');
    }

    public function updateUser(Request $request, BusinessProfile $businessProfile, User $user)
    {
        $this->authorize('update', $businessProfile);
        
        // Only owners can manage users
        if ($businessProfile->user_id !== auth()->id()) {
            abort(403, 'Only the business profile owner can manage users.');
        }

        $validated = $request->validate([
            'role' => 'required|in:viewer,editor,manager,owner',
            'permissions' => 'array',
            'is_active' => 'boolean',
        ]);

        $businessProfile->users()->updateExistingPivot($user->id, [
            'role' => $validated['role'],
            'permissions' => json_encode($validated['permissions'] ?? []),
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('business-profiles.users', $businessProfile)
            ->with('success', 'User access updated successfully.');
    }

    public function removeUser(BusinessProfile $businessProfile, User $user)
    {
        $this->authorize('update', $businessProfile);
        
        // Only owners can manage users
        if ($businessProfile->user_id !== auth()->id()) {
            abort(403, 'Only the business profile owner can manage users.');
        }

        // Cannot remove the owner
        if ($user->id === $businessProfile->user_id) {
            return redirect()->route('business-profiles.users', $businessProfile)
                ->with('error', 'Cannot remove the business profile owner.');
        }

        $businessProfile->users()->detach($user->id);

        return redirect()->route('business-profiles.users', $businessProfile)
            ->with('success', 'User removed successfully.');
    }
}