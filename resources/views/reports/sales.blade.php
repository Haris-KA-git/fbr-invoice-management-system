<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="h3 mb-0">Sales Report</h2>
                <p class="text-muted mb-0">Detailed sales analysis and invoice tracking</p>
            </div>
            <div class="btn-group">
                <a href="{{ route('reports.export.sales', request()->query()) }}" class="btn btn-success">
                    <i class="bi bi-download me-2"></i>Export CSV
                </a>
                <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Back to Reports
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Total Invoices</h6>
                            <h3 class="mb-0">{{ $totalInvoices }}</h3>
                        </div>
                        <div>
                            <i class="bi bi-file-earmark-text display-4 opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Total Revenue</h6>
                            <h3 class="mb-0">₨{{ number_format($totalAmount, 0) }}</h3>
                        </div>
                        <div>
                            <i class="bi bi-currency-dollar display-4 opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Total Tax</h6>
                            <h3 class="mb-0">₨{{ number_format($totalTax, 0) }}</h3>
                        </div>
                        <div>
                            <i class="bi bi-receipt display-4 opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
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
                <div class="col-md-2">
                    <label class="form-label">From Date</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">To Date</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">FBR Status</label>
                    <select name="fbr_status" class="form-select">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('fbr_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="validated" {{ request('fbr_status') == 'validated' ? 'selected' : '' }}>Validated</option>
                        <option value="submitted" {{ request('fbr_status') == 'submitted' ? 'selected' : '' }}>Submitted</option>
                        <option value="failed" {{ request('fbr_status') == 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Sales Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Business Profile</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Tax</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $invoice)
                            <tr>
                                <td>
                                    <a href="{{ route('invoices.show', $invoice) }}" class="text-decoration-none">
                                        {{ $invoice->invoice_number }}
                                    </a>
                                </td>
                                <td>{{ $invoice->invoice_date->format('M d, Y') }}</td>
                                <td>{{ $invoice->customer->name }}</td>
                                <td>{{ $invoice->businessProfile->business_name }}</td>
                                <td>
                                    <span class="badge bg-info">
                                        {{ ucfirst(str_replace('_', ' ', $invoice->invoice_type)) }}
                                    </span>
                                </td>
                                <td>₨{{ number_format($invoice->total_amount, 2) }}</td>
                                <td>₨{{ number_format($invoice->sales_tax, 2) }}</td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'validated' => 'info',
                                            'submitted' => 'success',
                                            'failed' => 'danger'
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $statusColors[$invoice->fbr_status] ?? 'secondary' }}">
                                        {{ ucfirst($invoice->fbr_status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="bi bi-file-earmark-text display-1 text-muted mb-3"></i>
                                    <h5>No sales data found</h5>
                                    <p class="text-muted">Try adjusting your filters or date range</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($invoices->hasPages())
                <div class="d-flex justify-content-center mt-3">
                    {{ $invoices->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>