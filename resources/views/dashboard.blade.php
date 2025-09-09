<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="h3 mb-0">Dashboard</h2>
            <p class="text-muted mb-0">Welcome back, {{ auth()->user()->name }}!</p>
        </div>
    </x-slot>

    <!-- Profile Limit Warning -->
    @if(!auth()->user()->canCreateBusinessProfile())
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Profile Limit Reached:</strong> You have used all {{ auth()->user()->business_profile_limit }} of your allowed business profiles. 
            Contact an administrator to increase your limit.
        </div>
    @endif

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card stats-card text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Customers</h6>
                            <h3 class="mb-0">{{ $stats['customers'] }}</h3>
                        </div>
                        <div>
                            <i class="bi bi-people stats-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card stats-card text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Items</h6>
                            <h3 class="mb-0">{{ $stats['items'] }}</h3>
                        </div>
                        <div>
                            <i class="bi bi-box stats-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card stats-card text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Invoices</h6>
                            <h3 class="mb-0">{{ $stats['invoices'] }}</h3>
                        </div>
                        <div>
                            <i class="bi bi-file-earmark-text stats-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card stats-card text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Total Revenue</h6>
                            <h3 class="mb-0">₨{{ number_format($stats['total_amount'], 0) }}</h3>
                        </div>
                        <div>
                            <i class="bi bi-currency-dollar stats-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Business Profile Usage -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Business Profile Usage</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Owned Profiles</span>
                        <span class="badge bg-primary">{{ auth()->user()->businessProfiles()->count() }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Shared Profiles</span>
                        <span class="badge bg-info">{{ auth()->user()->accessibleBusinessProfiles()->count() }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Profile Limit</span>
                        <span class="badge bg-secondary">{{ auth()->user()->business_profile_limit }}</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center">
                        <span><strong>Remaining</strong></span>
                        <span class="badge bg-success">{{ auth()->user()->getRemainingBusinessProfiles() }}</span>
                    </div>
                    
                    <div class="progress mt-3" style="height: 10px;">
                        @php
                            $percentage = (auth()->user()->businessProfiles()->count() / auth()->user()->business_profile_limit) * 100;
                        @endphp
                        <div class="progress-bar {{ $percentage >= 100 ? 'bg-danger' : ($percentage >= 80 ? 'bg-warning' : 'bg-success') }}" 
                             style="width: {{ min(100, $percentage) }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Invoice Status Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Draft Invoices</span>
                        <span class="badge bg-secondary">{{ $stats['draft_invoices'] ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Active Invoices</span>
                        <span class="badge bg-primary">{{ $stats['active_invoices'] ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Pending FBR</span>
                        <span class="badge bg-warning">{{ $stats['pending_invoices'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Submitted to FBR</span>
                        <span class="badge bg-success">{{ $stats['submitted_invoices'] ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recent Invoices</h5>
                </div>
                <div class="card-body">
                    @forelse($recentInvoices as $invoice)
                        <div class="d-flex align-items-center justify-content-between p-3 border rounded mb-2">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    <i class="bi bi-file-earmark-text text-white"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $invoice->invoice_number }}</h6>
                                    <small class="text-muted">{{ $invoice->customer->name }} • {{ $invoice->businessProfile->business_name }}</small>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="mb-1">
                                    <span class="badge bg-{{ $invoice->status === 'draft' ? 'secondary' : ($invoice->status === 'discarded' ? 'danger' : 'primary') }}">
                                        {{ ucfirst($invoice->status) }}
                                    </span>
                                    <span class="badge bg-{{ $invoice->fbr_status === 'submitted' ? 'success' : 'warning' }}">
                                        {{ ucfirst($invoice->fbr_status) }}
                                    </span>
                                </div>
                                <small class="text-muted">₨{{ number_format($invoice->total_amount, 2) }}</small>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <i class="bi bi-file-earmark-text display-1 text-muted mb-3"></i>
                            <h5>No recent invoices</h5>
                            <p class="text-muted">Start creating invoices to see them here</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @can('create invoices')
                            <a href="{{ route('invoices.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-2"></i>Create Invoice
                            </a>
                        @endcan
                        @can('create customers')
                            <a href="{{ route('customers.create') }}" class="btn btn-outline-primary">
                                <i class="bi bi-person-plus me-2"></i>Add Customer
                            </a>
                        @endcan
                        @can('create items')
                            <a href="{{ route('items.create') }}" class="btn btn-outline-primary">
                                <i class="bi bi-box me-2"></i>Add Item
                            </a>
                        @endcan
                        @if(auth()->user()->canCreateBusinessProfile())
                            <a href="{{ route('business-profiles.create') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-building me-2"></i>Add Business Profile
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Chart for monthly data if available
        @if($monthlyData->isNotEmpty())
            const ctx = document.getElementById('monthlyChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: {!! json_encode($monthlyData->pluck('month')->map(function($month) { return date('M', mktime(0, 0, 0, $month, 1)); })) !!},
                        datasets: [{
                            label: 'Invoice Count',
                            data: {!! json_encode($monthlyData->pluck('count')) !!},
                            borderColor: 'rgb(75, 192, 192)',
                            tension: 0.1
                        }, {
                            label: 'Total Amount',
                            data: {!! json_encode($monthlyData->pluck('total')) !!},
                            borderColor: 'rgb(255, 99, 132)',
                            tension: 0.1,
                            yAxisID: 'y1'
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                type: 'linear',
                                display: true,
                                position: 'left',
                            },
                            y1: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                grid: {
                                    drawOnChartArea: false,
                                },
                            }
                        }
                    }
                });
            }
        @endif
    </script>
    @endpush
</x-app-layout>