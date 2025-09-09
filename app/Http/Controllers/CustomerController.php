<?php

namespace App\Http\Controllers;

use App\Models\BusinessProfile;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
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

        $query = Customer::with('businessProfile')
            ->whereIn('business_profile_id', $profileIds);

        // Apply filters
        if ($request->business_profile_id) {
            $query->where('business_profile_id', $request->business_profile_id);
        }

        if ($request->customer_type) {
            $query->where('customer_type', $request->customer_type);
        }

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('ntn_cnic', 'like', '%' . $request->search . '%')
                  ->orWhere('contact_email', 'like', '%' . $request->search . '%');
            });
        }

        $customers = $query->latest()->paginate(15);
        $businessProfiles = BusinessProfile::whereIn('id', $profileIds)->get();

        return view('customers.index', compact('customers', 'businessProfiles'));
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

        return view('customers.create', compact('businessProfiles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'business_profile_id' => 'required|exists:business_profiles,id',
            'name' => 'required|string|max:255',
            'ntn_cnic' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'customer_type' => 'required|in:registered,unregistered',
        ]);

        // Check access to business profile
        $user = auth()->user();
        if (!$user->hasRole('Admin') && !in_array($validated['business_profile_id'], $user->getAccessibleBusinessProfileIds())) {
            abort(403, 'You do not have access to this business profile.');
        }

        Customer::create($validated);

        return redirect()->route('customers.index')
            ->with('success', 'Customer created successfully.');
    }

    public function show(Customer $customer)
    {
        $this->authorize('view', $customer);
        
        $customer->load('businessProfile');
        $invoices = $customer->invoices()->with('invoiceItems')->latest()->paginate(10);

        return view('customers.show', compact('customer', 'invoices'));
    }

    public function edit(Customer $customer)
    {
        $this->authorize('update', $customer);
        
        $user = auth()->user();
        
        if ($user->hasRole('Admin')) {
            $profileIds = BusinessProfile::pluck('id')->toArray();
        } else {
            $profileIds = $user->getAccessibleBusinessProfileIds();
        }

        $businessProfiles = BusinessProfile::whereIn('id', $profileIds)->get();

        return view('customers.edit', compact('customer', 'businessProfiles'));
    }

    public function update(Request $request, Customer $customer)
    {
        $this->authorize('update', $customer);

        $validated = $request->validate([
            'business_profile_id' => 'required|exists:business_profiles,id',
            'name' => 'required|string|max:255',
            'ntn_cnic' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'customer_type' => 'required|in:registered,unregistered',
        ]);

        $customer->update($validated);

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        $this->authorize('delete', $customer);

        // Check if customer has invoices
        if ($customer->invoices()->count() > 0) {
            return back()->with('error', 'Cannot delete customer with existing invoices.');
        }

        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Customer deleted successfully.');
    }
}