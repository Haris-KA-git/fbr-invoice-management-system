<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center">
            <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary me-3">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h2 class="h3 mb-0">{{ $customer->name }}</h2>
                <p class="text-muted mb-0">Customer Details & Invoice History</p>
            </div>
        </div>
    </x-slot>

    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Customer Information</h5>
                    <a href="{{ route('customers.edit', $customer) }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-pencil me-1"></i>Edit
                    </a>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                            <i class="bi bi-person text-white display-4"></i>
                        </div>
                        <h4>{{ $customer->name }}</h4>
                        <span class="badge {{ $customer->customer_type == 'registered' ? 'bg-success' : 'bg-info' }}">
                            {{ ucfirst($customer->customer_type) }} Customer
                        </span>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <h6 class="text-muted">Business Profile</h6>
                        <p class="mb-1">{{ $customer->businessProfile->business_name }}</p>
                    </div>

                    @if($customer->ntn_cnic)
                        <div class="mb-3">
                            <h6 class="text-muted">{{ $customer->customer_type == 'registered' ? 'NTN' : 'CNIC' }}</h6>
                            <p class="mb-1">{{ $customer->ntn_cnic }}</p>
                        </div>
                    @endif

                    @if($customer->address)
                        <div class="mb-3">
                            <h6 class="text-muted">Address</h6>
                            <p class="mb-1">{{ $customer->address }}</p>
                        </div>
                    @endif

                    @if($customer->contact_phone)
                        <div class="mb-3">
                            <h6 class="text-muted">Phone</h6>
                            <p class="mb-1">{{ $customer->contact_phone }}</p>
                        </div>
                    @endif

                    @if($customer->contact_email)
                        <div class="mb-3">
                            <h6 class="text-muted">Email</h6>
                            <p class="mb-1">{{ $customer->contact_email }}</p>
                        </div>
                    @endif

                    <div class="mb-3">
                        <h6 class="text-muted">Status</h6>
                        <span class="badge {{ $customer->is_active ? 'bg-success' : 'bg-secondary' }}">
                            {{ $customer->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>

                    <div class="mb-3">
                        <h6 class="text-muted">Created</h6>
                        <p class="mb-1">{{ $customer->created_at->format('M d, Y') }}</p>
                        <small class="text-muted">{{ $customer->created_at->diffForHumans() }}</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Invoice History</h5>
                    <a href="{{ route('invoices.create', ['customer_id' => $customer->id]) }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle me-1"></i>New Invoice
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Items</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($invoices as $invoice)
                                    <tr>
                                        <td>{{ $invoice->invoice_number }}</td>
                                        <td>{{ $invoice->invoice_date->format('M d, Y') }}</td>
                                        <td>
                                            <span class="badge bg-info">
                                                {{ ucfirst(str_replace('_', ' ', $invoice->invoice_type)) }}
                                            </span>
                                        </td>
                                        <td>{{ $invoice->invoiceItems->count() }}</td>
                                        <td>₨{{ number_format($invoice->total_amount, 2) }}</td>
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
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-outline-primary" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('invoices.download-pdf', $invoice) }}" class="btn btn-outline-secondary" title="Download PDF">
                                                    <i class="bi bi-download"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <i class="bi bi-file-earmark-text display-1 text-muted mb-3"></i>
                                            <h5>No invoices found</h5>
                                            <p class="text-muted">This customer hasn't been invoiced yet</p>
                                            <a href="{{ route('invoices.create', ['customer_id' => $customer->id]) }}" class="btn btn-primary">
                                                <i class="bi bi-plus-circle me-2"></i>Create First Invoice
                                            </a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($invoices->hasPages())
                        <div class="d-flex justify-content-center mt-3">
                            {{ $invoices->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>