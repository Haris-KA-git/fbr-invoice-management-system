<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="h3 mb-0">Customers</h2>
                <p class="text-muted mb-0">Manage your customer database</p>
            </div>
            <a href="{{ route('customers.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Add Customer
            </a>
        </div>
    </x-slot>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <select name="business_profile_id" class="form-select">
                        <option value="">All Business Profiles</option>
                        @foreach($businessProfiles as $profile)
                            <option value="{{ $profile->id }}" {{ request('business_profile_id') == $profile->id ? 'selected' : '' }}>
                                {{ $profile->business_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="customer_type" class="form-select">
                        <option value="">All Types</option>
                        <option value="registered" {{ request('customer_type') == 'registered' ? 'selected' : '' }}>Registered</option>
                        <option value="unregistered" {{ request('customer_type') == 'unregistered' ? 'selected' : '' }}>Unregistered</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search customers..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Customers Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>NTN/CNIC</th>
                            <th>Contact</th>
                            <th>Type</th>
                            <th>Business Profile</th>
                            <th>Invoices</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($customers as $customer)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                            <i class="bi bi-person text-white"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $customer->name }}</h6>
                                            @if($customer->address)
                                                <small class="text-muted">{{ Str::limit($customer->address, 30) }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $customer->ntn_cnic ?: 'N/A' }}</td>
                                <td>
                                    @if($customer->contact_email)
                                        <small class="d-block">{{ $customer->contact_email }}</small>
                                    @endif
                                    @if($customer->contact_phone)
                                        <small class="d-block text-muted">{{ $customer->contact_phone }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $customer->customer_type == 'registered' ? 'bg-success' : 'bg-info' }}">
                                        {{ ucfirst($customer->customer_type) }}
                                    </span>
                                </td>
                                <td>{{ $customer->businessProfile->business_name }}</td>
                                <td>
                                    <span class="badge bg-primary">{{ $customer->invoices()->count() }}</span>
                                </td>
                                <td>
                                    <span class="badge {{ $customer->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $customer->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('customers.show', $customer) }}" class="btn btn-outline-primary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('customers.edit', $customer) }}" class="btn btn-outline-secondary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form method="POST" action="{{ route('customers.destroy', $customer) }}" style="display: inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete" onclick="return confirm('Are you sure?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="bi bi-people display-1 text-muted mb-3"></i>
                                    <h4>No customers found</h4>
                                    <p class="text-muted">Start by adding your first customer</p>
                                    <a href="{{ route('customers.create') }}" class="btn btn-primary">
                                        <i class="bi bi-plus-circle me-2"></i>Add Customer
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($customers->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $customers->links() }}
        </div>
    @endif
</x-app-layout>