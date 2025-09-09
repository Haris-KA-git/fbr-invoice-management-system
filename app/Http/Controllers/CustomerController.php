<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\BusinessProfile;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        $profileIds = auth()->user()->businessProfiles()->pluck('id');
        
        $customers = Customer::whereIn('business_profile_id', $profileIds)
            ->with('businessProfile')
            ->when(request('business_profile_id'), function($query) {
                $query->where('business_profile_id', request('business_profile_id'));
            })
            ->when(request('customer_type'), function($query) {
                $query->where('customer_type', request('customer_type'));
            })
            ->when(request('search'), function($query) {
                $query->where(function($q) {
                    $q->where('name', 'like', '%' . request('search') . '%')
                      ->orWhere('ntn_cnic', 'like', '%' . request('search') . '%')
                      ->orWhere('contact_email', 'like', '%' . request('search') . '%');
                });
            })
            ->latest()
            ->paginate(15);

        $businessProfiles = auth()->user()->businessProfiles;

        return view('customers.index', compact('customers', 'businessProfiles'));
    }

    public function create()
    {
        $businessProfiles = auth()->user()->businessProfiles;
        
        return view('customers.create', compact('businessProfiles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'business_profile_id' => 'required|exists:business_profiles,id',
            'name' => 'required|string|max:255',
            'ntn_cnic' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'contact_phone' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'customer_type' => 'required|in:registered,unregistered',
        ]);

        // Verify business profile belongs to user
        $businessProfile = auth()->user()->businessProfiles()
            ->findOrFail($validated['business_profile_id']);

        Customer::create($validated);

        return redirect()->route('customers.index')
            ->with('success', 'Customer created successfully.');
    }

    public function show(Customer $customer)
    {
        $this->authorize('view', $customer);

        $invoices = $customer->invoices()
            ->with('invoiceItems.item')
            ->latest()
            ->paginate(10);

        return view('customers.show', compact('customer', 'invoices'));
    }

    public function edit(Customer $customer)
    {
        $this->authorize('update', $customer);
        
        $businessProfiles = auth()->user()->businessProfiles;
        
        return view('customers.edit', compact('customer', 'businessProfiles'));
    }

    public function update(Request $request, Customer $customer)
    {
        $this->authorize('update', $customer);

        $validated = $request->validate([
            'business_profile_id' => 'required|exists:business_profiles,id',
            'name' => 'required|string|max:255',
            'ntn_cnic' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'contact_phone' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'customer_type' => 'required|in:registered,unregistered',
        ]);

        // Verify business profile belongs to user
        $businessProfile = auth()->user()->businessProfiles()
            ->findOrFail($validated['business_profile_id']);

        $customer->update($validated);

        return redirect()->route('customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        $this->authorize('delete', $customer);

        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Customer deleted successfully.');
    }
}