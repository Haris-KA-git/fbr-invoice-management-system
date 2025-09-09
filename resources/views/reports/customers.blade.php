<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="h3 mb-0">Customer Report</h2>
                <p class="text-muted mb-0">Customer analysis and purchase history</p>
            </div>
            <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Reports
            </a>
        </div>
    </x-slot>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Business Profile</label>
                    <select name="business_profile_id" class="form-select">
                        <option value="">All Profiles</option>
                        @foreach($businessProfiles as $profile)
                            <option value="{{ $profile->id }}" {{ request('business_profile_id') == $profile->id ? 'selected' : '' }}>
                                {{ $profile->business_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Customer Type</label>
                    <select name="customer_type" class="form-select">
                        <option value="">All Types</option>
                        <option value="registered" {{ request('customer_type') == 'registered' ? 'selected' : '' }}>Registered</option>
                        <option value="unregistered" {{ request('customer_type') == 'unregistered' ? 'selected' : '' }}>Unregistered</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Customer Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Type</th>
                            <th>Business Profile</th>
                            <th>Total Invoices</th>
                            <th>Total Revenue</th>
                            <th>Avg. Invoice Value</th>
                            <th>Last Invoice</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $customer)
                            <tr>
                                <td>
                                    <div>
                                        <a href="{{ route('customers.show', $customer) }}" class="text-decoration-none fw-bold">
                                            {{ $customer->name }}
                                        </a>
                                        @if($customer->contact_email)
                                            <br><small class="text-muted">{{ $customer->contact_email }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="badge {{ $customer->customer_type == 'registered' ? 'bg-success' : 'bg-info' }}">
                                        {{ ucfirst($customer->customer_type) }}
                                    </span>
                                </td>
                                <td>{{ $customer->businessProfile->business_name }}</td>
                                <td>
                                    <span class="badge bg-primary">{{ $customer->invoices_count }}</span>
                                </td>
                                <td>₨{{ number_format($customer->invoices_sum_total_amount ?? 0, 2) }}</td>
                                <td>
                                    @if($customer->invoices_count > 0)
                                        ₨{{ number_format(($customer->invoices_sum_total_amount ?? 0) / $customer->invoices_count, 2) }}
                                    @else
                                        ₨0.00
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $lastInvoice = $customer->invoices()->latest()->first();
                                    @endphp
                                    @if($lastInvoice)
                                        {{ $lastInvoice->invoice_date->format('M d, Y') }}
                                    @else
                                        <span class="text-muted">Never</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="bi bi-people display-1 text-muted mb-3"></i>
                                    <h5>No customer data found</h5>
                                    <p class="text-muted">Try adjusting your filters</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($customers->hasPages())
                <div class="d-flex justify-content-center mt-3">
                    {{ $customers->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>