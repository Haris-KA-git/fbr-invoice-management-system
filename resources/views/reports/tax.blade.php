<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="h3 mb-0">Tax Report</h2>
                <p class="text-muted mb-0">Tax collection summary and FBR compliance</p>
            </div>
            <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Reports
            </a>
        </div>
    </x-slot>

    <!-- Monthly Tax Summary -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Monthly Tax Summary</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Invoices</th>
                            <th>Total Sales</th>
                            <th>Tax Collected</th>
                            <th>Tax Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($monthlyTax as $month)
                            <tr>
                                <td>
                                    {{ DateTime::createFromFormat('!m', $month->month)->format('F') }} {{ $month->year }}
                                </td>
                                <td>{{ $month->invoice_count }}</td>
                                <td>₨{{ number_format($month->total_amount, 2) }}</td>
                                <td>₨{{ number_format($month->total_tax, 2) }}</td>
                                <td>
                                    @if($month->total_amount > 0)
                                        {{ number_format(($month->total_tax / $month->total_amount) * 100, 2) }}%
                                    @else
                                        0%
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <i class="bi bi-receipt display-1 text-muted mb-3"></i>
                                    <h5>No tax data found</h5>
                                    <p class="text-muted">No submitted invoices with tax data</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Business Profile Tax Summary -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Tax Summary by Business Profile</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Business Profile</th>
                            <th>Invoices</th>
                            <th>Total Sales</th>
                            <th>Tax Collected</th>
                            <th>Avg. Tax Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($profileTax as $profile)
                            <tr>
                                <td>{{ $profile->businessProfile->business_name }}</td>
                                <td>{{ $profile->invoice_count }}</td>
                                <td>₨{{ number_format($profile->total_amount, 2) }}</td>
                                <td>₨{{ number_format($profile->total_tax, 2) }}</td>
                                <td>
                                    @if($profile->total_amount > 0)
                                        {{ number_format(($profile->total_tax / $profile->total_amount) * 100, 2) }}%
                                    @else
                                        0%
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <i class="bi bi-building display-1 text-muted mb-3"></i>
                                    <h5>No business profile tax data found</h5>
                                    <p class="text-muted">No submitted invoices with tax data</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>