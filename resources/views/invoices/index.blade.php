<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="h3 mb-0">Invoices</h2>
                <p class="text-muted mb-0">Manage your FBR-compliant invoices</p>
            </div>
            <div>
                <a href="{{ route('invoices.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Create Invoice
                </a>
            </div>
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
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="discarded" {{ request('status') == 'discarded' ? 'selected' : '' }}>Discarded</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="fbr_status" class="form-select">
                        <option value="">All FBR Status</option>
                        <option value="pending" {{ request('fbr_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="validated" {{ request('fbr_status') == 'validated' ? 'selected' : '' }}>Validated</option>
                        <option value="submitted" {{ request('fbr_status') == 'submitted' ? 'selected' : '' }}>Submitted</option>
                        <option value="failed" {{ request('fbr_status') == 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Search invoices..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Invoices Table -->
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
                            <th>Items</th>
                            <th>Amount</th>
                            <th>QR Code</th>
                            <th>Status</th>
                            <th>FBR Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($invoices as $invoice)
                            <tr class="{{ $invoice->status === 'discarded' ? 'table-secondary' : '' }}">
                                <td>
                                    <a href="{{ route('invoices.show', $invoice) }}" class="text-decoration-none">
                                        {{ $invoice->invoice_number }}
                                    </a>
                                    @if($invoice->fbr_invoice_number)
                                        <br><small class="text-muted">FBR: {{ $invoice->fbr_invoice_number }}</small>
                                    @endif
                                </td>
                                <td>{{ $invoice->invoice_date->format('M d, Y') }}</td>
                                <td>{{ $invoice->customer->name }}</td>
                                <td>{{ $invoice->businessProfile->business_name }}</td>
                                <td>
                                    <span class="badge bg-info">
                                        {{ ucfirst(str_replace('_', ' ', $invoice->invoice_type)) }}
                                    </span>
                                </td>
                                <td>{{ $invoice->invoiceItems->count() }}</td>
                                <td>₨{{ number_format($invoice->total_amount, 2) }}</td>
                                <td class="text-center">
                                    @if($invoice->qr_code_path && $invoice->fbr_status === 'submitted')
                                        <i class="bi bi-qr-code text-success" title="QR Code Available"></i>
                                    @elseif($invoice->fbr_status === 'submitted')
                                        <i class="bi bi-qr-code text-warning" title="QR Code Generating"></i>
                                    @else
                                        <i class="bi bi-qr-code text-muted" title="No QR Code"></i>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $invoice->status === 'draft' ? 'secondary' : ($invoice->status === 'discarded' ? 'danger' : 'primary') }}">
                                        {{ ucfirst($invoice->status) }}
                                    </span>
                                </td>
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
                                        @if($invoice->status === 'draft' || ($invoice->status === 'active' && in_array($invoice->fbr_status, ['pending', 'failed'])))
                                            <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-outline-secondary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endif
                                        <a href="{{ route('invoices.download-pdf', $invoice) }}" class="btn btn-outline-info" title="Download PDF">
                                            <i class="bi bi-download"></i>
                                        </a>
                                        @if($invoice->status === 'draft')
                                            <form method="POST" action="{{ route('invoices.destroy', $invoice) }}" style="display: inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" title="Delete" onclick="return confirm('Are you sure?')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center py-4">
                                    <i class="bi bi-file-earmark-text display-1 text-muted mb-3"></i>
                                    <h4>No invoices found</h4>
                                    <p class="text-muted">Start by creating your first invoice</p>
                                    <a href="{{ route('invoices.create') }}" class="btn btn-primary">
                                        <i class="bi bi-plus-circle me-2"></i>Create Invoice
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($invoices->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $invoices->links() }}
        </div>
    @endif
</x-app-layout>