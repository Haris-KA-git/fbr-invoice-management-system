<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="h3 mb-0">Reports & Analytics</h2>
            <p class="text-muted mb-0">Comprehensive business reporting and insights</p>
        </div>
    </x-slot>

    <div class="row">
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="bi bi-graph-up text-primary" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="card-title">Sales Report</h5>
                    <p class="card-text text-muted">
                        Detailed sales analysis with filtering options and export capabilities.
                    </p>
                    <a href="{{ route('reports.sales') }}" class="btn btn-primary">
                        <i class="bi bi-bar-chart me-2"></i>View Report
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="bi bi-people text-success" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="card-title">Customer Report</h5>
                    <p class="card-text text-muted">
                        Customer analysis with purchase history and revenue contribution.
                    </p>
                    <a href="{{ route('reports.customers') }}" class="btn btn-success">
                        <i class="bi bi-person-lines-fill me-2"></i>View Report
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="bi bi-box text-info" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="card-title">Item Report</h5>
                    <p class="card-text text-muted">
                        Product and service performance analysis with sales metrics.
                    </p>
                    <a href="{{ route('reports.items') }}" class="btn btn-info">
                        <i class="bi bi-boxes me-2"></i>View Report
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="bi bi-receipt text-warning" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="card-title">Tax Report</h5>
                    <p class="card-text text-muted">
                        Tax collection summary and FBR compliance reporting.
                    </p>
                    <a href="{{ route('reports.tax') }}" class="btn btn-warning">
                        <i class="bi bi-calculator me-2"></i>View Report
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="border-end">
                                <h3 class="text-primary">{{ auth()->user()->businessProfiles()->count() }}</h3>
                                <p class="text-muted mb-0">Business Profiles</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-end">
                                @php
                                    $profileIds = auth()->user()->businessProfiles()->pluck('id');
                                    $totalCustomers = \App\Models\Customer::whereIn('business_profile_id', $profileIds)->count();
                                @endphp
                                <h3 class="text-success">{{ $totalCustomers }}</h3>
                                <p class="text-muted mb-0">Total Customers</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-end">
                                @php
                                    $totalInvoices = \App\Models\Invoice::whereIn('business_profile_id', $profileIds)->count();
                                @endphp
                                <h3 class="text-info">{{ $totalInvoices }}</h3>
                                <p class="text-muted mb-0">Total Invoices</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            @php
                                $totalRevenue = \App\Models\Invoice::whereIn('business_profile_id', $profileIds)
                                    ->where('fbr_status', 'submitted')
                                    ->sum('total_amount');
                            @endphp
                            <h3 class="text-warning">₨{{ number_format($totalRevenue, 0) }}</h3>
                            <p class="text-muted mb-0">Total Revenue</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recent Activity</h5>
                </div>
                <div class="card-body">
                    @php
                        $profileIds = auth()->user()->businessProfiles()->pluck('id');
                        $recentInvoices = \App\Models\Invoice::whereIn('business_profile_id', $profileIds)
                            ->with(['customer', 'businessProfile'])
                            ->latest()
                            ->limit(5)
                            ->get();
                    @endphp

                    @forelse($recentInvoices as $invoice)
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <i class="bi bi-file-earmark-text text-primary fs-4"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="fw-bold">{{ $invoice->invoice_number }}</div>
                                <small class="text-muted">
                                    {{ $invoice->customer->name }} • {{ $invoice->businessProfile->business_name }}
                                </small>
                            </div>
                            <div class="flex-shrink-0">
                                <span class="badge bg-{{ $invoice->fbr_status === 'submitted' ? 'success' : 'warning' }}">
                                    {{ ucfirst($invoice->fbr_status) }}
                                </span>
                                <div class="text-end">
                                    <small class="text-muted">₨{{ number_format($invoice->total_amount, 2) }}</small>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center">No recent activity</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>