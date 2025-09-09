<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="h3 mb-0">Reports</h2>
            <p class="text-muted mb-0">Business analytics and insights</p>
        </div>
    </x-slot>

    <div class="row">
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px;">
                        <i class="bi bi-graph-up text-white display-6"></i>
                    </div>
                    <h5 class="card-title">Sales Report</h5>
                    <p class="card-text text-muted">Detailed sales analysis and invoice tracking</p>
                    <a href="{{ route('reports.sales') }}" class="btn btn-primary">
                        <i class="bi bi-eye me-2"></i>View Report
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="bg-success rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px;">
                        <i class="bi bi-people text-white display-6"></i>
                    </div>
                    <h5 class="card-title">Customer Report</h5>
                    <p class="card-text text-muted">Customer analysis and purchase history</p>
                    <a href="{{ route('reports.customers') }}" class="btn btn-success">
                        <i class="bi bi-eye me-2"></i>View Report
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="bg-info rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px;">
                        <i class="bi bi-box text-white display-6"></i>
                    </div>
                    <h5 class="card-title">Item Report</h5>
                    <p class="card-text text-muted">Product and service performance analysis</p>
                    <a href="{{ route('reports.items') }}" class="btn btn-info">
                        <i class="bi bi-eye me-2"></i>View Report
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="bg-warning rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px;">
                        <i class="bi bi-receipt text-white display-6"></i>
                    </div>
                    <h5 class="card-title">Tax Report</h5>
                    <p class="card-text text-muted">Tax collection summary and FBR compliance</p>
                    <a href="{{ route('reports.tax') }}" class="btn btn-warning">
                        <i class="bi bi-eye me-2"></i>View Report
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Overview</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="border-end">
                                <h3 class="text-primary mb-1">{{ $businessProfiles->count() }}</h3>
                                <p class="text-muted mb-0">Business Profiles</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-end">
                                @php
                                    $totalCustomers = \App\Models\Customer::whereIn('business_profile_id', $businessProfiles->pluck('id'))->count();
                                @endphp
                                <h3 class="text-success mb-1">{{ $totalCustomers }}</h3>
                                <p class="text-muted mb-0">Total Customers</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-end">
                                @php
                                    $totalItems = \App\Models\Item::whereIn('business_profile_id', $businessProfiles->pluck('id'))->count();
                                @endphp
                                <h3 class="text-info mb-1">{{ $totalItems }}</h3>
                                <p class="text-muted mb-0">Total Items</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            @php
                                $totalInvoices = \App\Models\Invoice::whereIn('business_profile_id', $businessProfiles->pluck('id'))
                                    ->where('status', '!=', 'discarded')->count();
                            @endphp
                            <h3 class="text-warning mb-1">{{ $totalInvoices }}</h3>
                            <p class="text-muted mb-0">Total Invoices</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>