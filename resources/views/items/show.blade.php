<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center">
            <a href="{{ route('items.index') }}" class="btn btn-outline-secondary me-3">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h2 class="h3 mb-0">{{ $item->name }}</h2>
                <p class="text-muted mb-0">Item Details</p>
            </div>
        </div>
    </x-slot>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Item Information</h5>
                    <a href="{{ route('items.edit', $item) }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-pencil me-1"></i>Edit
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted">Item Name</h6>
                            <p class="mb-3">{{ $item->name }}</p>

                            <h6 class="text-muted">Item Code</h6>
                            <p class="mb-3"><code>{{ $item->item_code }}</code></p>

                            <h6 class="text-muted">HS Code</h6>
                            <p class="mb-3">{{ $item->hs_code ?: 'Not specified' }}</p>

                            <h6 class="text-muted">Unit of Measure</h6>
                            <p class="mb-3">{{ $item->unit_of_measure }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Unit Price</h6>
                            <p class="mb-3">₨{{ number_format($item->price, 2) }}</p>

                            <h6 class="text-muted">Tax Rate</h6>
                            <p class="mb-3">{{ $item->tax_rate }}%</p>

                            <h6 class="text-muted">Business Profile</h6>
                            <p class="mb-3">{{ $item->businessProfile->business_name }}</p>

                            <h6 class="text-muted">Status</h6>
                            <span class="badge {{ $item->is_active ? 'bg-success' : 'bg-secondary' }}">
                                {{ $item->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>

                    @if($item->description)
                        <h6 class="text-muted">Description</h6>
                        <p class="mb-3">{{ $item->description }}</p>
                    @endif

                    @if($item->sro_references)
                        <h6 class="text-muted">SRO References</h6>
                        <p class="mb-3">
                            @foreach($item->sro_references as $sro)
                                <span class="badge bg-info me-1">{{ $sro }}</span>
                            @endforeach
                        </p>
                    @endif

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted">Created</h6>
                            <p class="mb-1">{{ $item->created_at->format('M d, Y H:i') }}</p>
                            <small class="text-muted">{{ $item->created_at->diffForHumans() }}</small>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Last Updated</h6>
                            <p class="mb-1">{{ $item->updated_at->format('M d, Y H:i') }}</p>
                            <small class="text-muted">{{ $item->updated_at->diffForHumans() }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Quick Stats</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Times Used in Invoices</span>
                        <span class="badge bg-primary">{{ $item->invoiceItems()->count() }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Total Quantity Sold</span>
                        <span class="badge bg-success">{{ $item->invoiceItems()->sum('quantity') }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Total Revenue</span>
                        <span class="badge bg-info">₨{{ number_format($item->invoiceItems()->sum('line_total'), 2) }}</span>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Pricing Calculator</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="calc_quantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="calc_quantity" value="1" min="1">
                    </div>
                    
                    <div class="border rounded p-3 bg-light">
                        <div class="d-flex justify-content-between">
                            <span>Subtotal:</span>
                            <span id="calc_subtotal">₨{{ number_format($item->price, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Tax ({{ $item->tax_rate }}%):</span>
                            <span id="calc_tax">₨{{ number_format(($item->price * $item->tax_rate) / 100, 2) }}</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold">
                            <span>Total:</span>
                            <span id="calc_total">₨{{ number_format($item->price + (($item->price * $item->tax_rate) / 100), 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.getElementById('calc_quantity').addEventListener('input', function() {
            const quantity = parseFloat(this.value) || 0;
            const unitPrice = {{ $item->price }};
            const taxRate = {{ $item->tax_rate }};
            
            const subtotal = quantity * unitPrice;
            const tax = (subtotal * taxRate) / 100;
            const total = subtotal + tax;
            
            document.getElementById('calc_subtotal').textContent = '₨' + subtotal.toLocaleString('en-PK', {minimumFractionDigits: 2});
            document.getElementById('calc_tax').textContent = '₨' + tax.toLocaleString('en-PK', {minimumFractionDigits: 2});
            document.getElementById('calc_total').textContent = '₨' + total.toLocaleString('en-PK', {minimumFractionDigits: 2});
        });
    </script>
    @endpush
</x-app-layout>