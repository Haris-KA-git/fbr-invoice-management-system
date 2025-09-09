<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="h3 mb-0">Invoices</h2>
                <p class="text-muted mb-0">Manage and track your FBR invoices</p>
            </div>
            <a href="{{ route('invoices.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Create Invoice
            </a>
        </div>
    </x-slot>

    <!-- Status Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-file-earmark text-info display-4"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="small text-muted">Draft</div>
                            <div class="h4">{{ $invoices->where('status', 'draft')->count() }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-clock text-warning display-4"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="small text-muted">Pending</div>
                            <div class="h4">{{ $invoices->where('fbr_status', 'pending')->count() }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-check-circle text-info display-4"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="small text-muted">Validated</div>
                            <div class="h4">{{ $invoices->where('fbr_status', 'validated')->count() }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-check2-circle text-success display-4"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="small text-muted">Submitted</div>
                            <div class="h4">{{ $invoices->where('fbr_status', 'submitted')->count() }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-archive text-secondary display-4"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="small text-muted">Discarded</div>
                            <div class="h4">{{ $invoices->where('status', 'discarded')->count() }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Invoices Table -->
    <div class="card">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0">All Invoices</h5>
                </div>
                <div class="col-auto">
                    <div class="row g-2">
                        <div class="col-auto">
                            <select class="form-select form-select-sm" id="invoiceStatusFilter">
                                <option value="">All Status</option>
                                <option value="draft">Draft</option>
                                <option value="active">Active</option>
                                <option value="discarded">Discarded</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <select class="form-select form-select-sm" id="statusFilter">
                                <option value="">All FBR Status</option>
                                <option value="pending">Pending</option>
                                <option value="validated">Validated</option>
                                <option value="submitted">Submitted</option>
                                <option value="failed">Failed</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <input type="text" class="form-control form-control-sm" placeholder="Search invoices..." id="searchInput">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Items</th>
                            <th>Amount</th>
                            <th>FBR Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($invoices as $invoice)
                            <tr>
                                <td>
                                    <div>
                                        <strong>{{ $invoice->invoice_number }}</strong>
                                        @if($invoice->fbr_invoice_number)
                                            <br><small class="text-muted">FBR: {{ $invoice->fbr_invoice_number }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        {{ $invoice->customer->name }}
                                        <br><small class="text-muted">{{ $invoice->businessProfile->business_name }}</small>
                                    </div>
                                </td>
                                <td>{{ $invoice->invoice_date->format('M d, Y') }}</td>
                                <td>
                                    <span class="badge bg-info">
                                        {{ ucfirst(str_replace('_', ' ', $invoice->invoice_type)) }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'draft' => 'secondary',
                                            'active' => 'primary',
                                            'discarded' => 'dark'
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $statusColors[$invoice->status] ?? 'secondary' }}">
                                        {{ ucfirst($invoice->status) }}
                                    </span>
                                    @if($invoice->status === 'discarded')
                                        <i class="bi bi-info-circle text-muted ms-1" data-bs-toggle="tooltip" 
                                           title="Discarded: {{ $invoice->discard_reason }}"></i>
                                    @endif
                                </td>
                                <td>{{ $invoice->invoiceItems->count() }} items</td>
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
                                    @if($invoice->fbr_status === 'failed' && $invoice->fbr_error_message)
                                        <i class="bi bi-info-circle text-danger ms-1" data-bs-toggle="tooltip" 
                                           title="{{ $invoice->fbr_error_message }}"></i>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-outline-primary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @if(in_array($invoice->fbr_status, ['pending', 'failed']) && $invoice->status !== 'discarded')
                                            <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-outline-secondary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endif
                                        <a href="{{ route('invoices.download-pdf', $invoice) }}" class="btn btn-outline-secondary" title="Download PDF">
                                            <i class="bi bi-download"></i>
                                        </a>
                                        @if($invoice->status === 'discarded')
                                            <form method="POST" action="{{ route('invoices.restore', $invoice) }}" style="display: inline-block;">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-success" title="Restore Invoice">
                                                    <i class="bi bi-arrow-clockwise"></i>
                                                </button>
                                            </form>
                                        @elseif($invoice->fbr_status === 'pending' || $invoice->fbr_status === 'failed')
                                            <form method="POST" action="{{ route('invoices.submit-to-fbr', $invoice) }}" style="display: inline-block;">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-success" title="Submit to FBR">
                                                    <i class="bi bi-upload"></i>
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('invoices.discard', $invoice) }}" style="display: inline-block;">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-warning" title="Discard Invoice">
                                                    <i class="bi bi-archive"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
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

    @push('scripts')
    <script>
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Filter and search functionality
        document.getElementById('statusFilter').addEventListener('change', filterTable);
        document.getElementById('invoiceStatusFilter').addEventListener('change', filterTable);
        document.getElementById('searchInput').addEventListener('keyup', filterTable);

        function filterTable() {
            const statusFilter = document.getElementById('statusFilter').value.toLowerCase();
            const invoiceStatusFilter = document.getElementById('invoiceStatusFilter').value.toLowerCase();
            const searchFilter = document.getElementById('searchInput').value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');

            rows.forEach(row => {
                const fbrStatus = row.cells[7].textContent.toLowerCase();
                const invoiceStatus = row.cells[4].textContent.toLowerCase();
                const invoice = row.cells[0].textContent.toLowerCase();
                const customer = row.cells[1].textContent.toLowerCase();

                const fbrStatusMatch = !statusFilter || fbrStatus.includes(statusFilter);
                const invoiceStatusMatch = !invoiceStatusFilter || invoiceStatus.includes(invoiceStatusFilter);
                const searchMatch = !searchFilter || invoice.includes(searchFilter) || customer.includes(searchFilter);

                row.style.display = fbrStatusMatch && invoiceStatusMatch && searchMatch ? '' : 'none';
            });
        }
    </script>
    @endpush
</x-app-layout>