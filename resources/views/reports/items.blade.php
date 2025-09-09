<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="h3 mb-0">Item Report</h2>
                <p class="text-muted mb-0">Product and service performance analysis</p>
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
                <div class="col-md-6">
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
                <div class="col-md-6">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Items Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Code</th>
                            <th>Business Profile</th>
                            <th>Times Sold</th>
                            <th>Total Quantity</th>
                            <th>Total Revenue</th>
                            <th>Avg. Sale Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            <tr>
                                <td>
                                    <div>
                                        <a href="{{ route('items.show', $item) }}" class="text-decoration-none fw-bold">
                                            {{ $item->name }}
                                        </a>
                                        @if($item->description)
                                            <br><small class="text-muted">{{ Str::limit($item->description, 40) }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td><code>{{ $item->item_code }}</code></td>
                                <td>{{ $item->businessProfile->business_name }}</td>
                                <td>
                                    <span class="badge bg-primary">{{ $item->invoice_items_count }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $item->invoice_items_sum_quantity ?? 0 }} {{ $item->unit_of_measure }}</span>
                                </td>
                                <td>₨{{ number_format($item->invoice_items_sum_line_total ?? 0, 2) }}</td>
                                <td>
                                    @if($item->invoice_items_sum_quantity > 0)
                                        ₨{{ number_format(($item->invoice_items_sum_line_total ?? 0) / $item->invoice_items_sum_quantity, 2) }}
                                    @else
                                        ₨{{ number_format($item->price, 2) }}
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="bi bi-box display-1 text-muted mb-3"></i>
                                    <h5>No item data found</h5>
                                    <p class="text-muted">Try adjusting your filters</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($items->hasPages())
                <div class="d-flex justify-content-center mt-3">
                    {{ $items->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>