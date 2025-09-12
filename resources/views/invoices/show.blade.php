<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center">
            <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary me-3">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h2 class="h3 mb-0">{{ $invoice->invoice_number }}</h2>
                <p class="text-muted mb-0">Invoice Details</p>
            </div>
        </div>
    </x-slot>

    <div class="row">
        <div class="col-lg-8">
            <!-- Invoice Header -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Invoice Information</h5>
                    <div class="d-flex gap-2">
                        <span class="badge bg-{{ $invoice->status === 'draft' ? 'secondary' : ($invoice->status === 'discarded' ? 'danger' : 'primary') }}">
                            {{ ucfirst($invoice->status) }}
                        </span>
                        @php
                            $statusColors = [
                                'pending' => 'warning',
                                'validated' => 'info',
                                'submitted' => 'success',
                                'failed' => 'danger'
                            ];
                        @endphp
                        <span class="badge bg-{{ $statusColors[$invoice->fbr_status] ?? 'secondary' }}">
                            FBR: {{ ucfirst($invoice->fbr_status) }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted">Business Profile</h6>
                            <p class="mb-3">{{ $invoice->businessProfile->business_name }}</p>

                            <h6 class="text-muted">Customer</h6>
                            <p class="mb-3">{{ $invoice->customer->name }}</p>

                            <h6 class="text-muted">Invoice Date</h6>
                            <p class="mb-3">{{ $invoice->invoice_date->format('M d, Y') }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Invoice Type</h6>
                            <p class="mb-3">{{ ucfirst(str_replace('_', ' ', $invoice->invoice_type)) }}</p>

                            @if($invoice->fbr_invoice_number)
                                <h6 class="text-muted">FBR Invoice Number</h6>
                                <p class="mb-3">{{ $invoice->fbr_invoice_number }}</p>
                            @endif

                            <h6 class="text-muted">Created By</h6>
                            <p class="mb-3">{{ $invoice->user->name }}</p>
                        </div>
                    </div>

                    @if($invoice->status === 'discarded')
                        <div class="alert alert-warning">
                            <h6 class="alert-heading">Invoice Discarded</h6>
                            <p class="mb-2"><strong>Reason:</strong> {{ $invoice->discard_reason }}</p>
                            <p class="mb-2"><strong>Discarded by:</strong> {{ $invoice->discardedBy->name }}</p>
                            <p class="mb-0"><strong>Discarded on:</strong> {{ $invoice->discarded_at->format('M d, Y H:i') }}</p>
                        </div>
                    @endif

                    @if($invoice->fbr_error_message)
                        <div class="alert alert-danger">
                            <h6 class="alert-heading">FBR Error</h6>
                            <p class="mb-0">{{ $invoice->fbr_error_message }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Invoice Items -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Invoice Items</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Qty</th>
                                    <th>Unit Price</th>
                                    <th>Discount</th>
                                    <th>Tax Rate</th>
                                    <th>Tax Amount</th>
                                    <th>Line Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoice->invoiceItems as $item)
                                    <tr>
                                        <td>
                                            <strong>{{ $item->item->name }}</strong><br>
                                            <small class="text-muted">Code: {{ $item->item->item_code }}</small>
                                        </td>
                                        <td>{{ $item->quantity }} {{ $item->item->unit_of_measure }}</td>
                                        <td>₨{{ number_format($item->unit_price, 2) }}</td>
                                        <td>
                                            @if($item->discount_rate > 0)
                                                {{ $item->discount_rate }}%<br>
                                                <small class="text-muted">(₨{{ number_format($item->discount_amount, 2) }})</small>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $item->tax_rate }}%</td>
                                        <td>₨{{ number_format($item->tax_amount, 2) }}</td>
                                        <td>₨{{ number_format($item->line_total, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($invoice->status === 'draft')
                            <form method="POST" action="{{ route('invoices.activate', $invoice) }}">
                                @csrf
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="bi bi-play-circle me-2"></i>Activate & Submit to FBR
                                </button>
                            </form>
                        @endif

                        @if($invoice->status === 'draft' || ($invoice->status === 'active' && in_array($invoice->fbr_status, ['pending', 'failed'])))
                            <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-primary">
                                <i class="bi bi-pencil me-2"></i>Edit Invoice
                            </a>
                        @endif

                        <a href="{{ route('invoices.download-pdf', $invoice) }}" class="btn btn-outline-primary">
                            <i class="bi bi-download me-2"></i>Download PDF
                        </a>

                        @if($invoice->fbr_status === 'pending' || $invoice->fbr_status === 'failed')
                            <form method="POST" action="{{ route('invoices.submit-fbr', $invoice) }}">
                                @csrf
                                <button type="submit" class="btn btn-outline-success">
                                    <i class="bi bi-cloud-upload me-2"></i>Retry FBR Submission
                                </button>
                            </form>
                        @endif

                        @if($invoice->status !== 'discarded')
                            <a href="{{ route('invoices.discard', $invoice) }}" class="btn btn-outline-warning">
                                <i class="bi bi-archive me-2"></i>Discard Invoice
                            </a>
                        @else
                            <form method="POST" action="{{ route('invoices.restore', $invoice) }}">
                                @csrf
                                <button type="submit" class="btn btn-outline-info">
                                    <i class="bi bi-arrow-clockwise me-2"></i>Restore Invoice
                                </button>
                            </form>
                        @endif

                        @if($invoice->status === 'draft')
                            <form method="POST" action="{{ route('invoices.destroy', $invoice) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to delete this draft invoice?')">
                                    <i class="bi bi-trash me-2"></i>Delete Draft
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Invoice Summary -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Invoice Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span>₨{{ number_format($invoice->subtotal, 2) }}</span>
                    </div>
                    @if($invoice->discount_amount > 0)
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Discount:</span>
                            <span class="text-danger">-₨{{ number_format($invoice->discount_amount, 2) }}</span>
                        </div>
                    @endif
                    <div class="d-flex justify-content-between mb-2">
                        <span>Sales Tax:</span>
                        <span class="text-success">₨{{ number_format($invoice->sales_tax, 2) }}</span>
                    </div>
                    @if($invoice->fed_amount > 0)
                        <div class="d-flex justify-content-between mb-2">
                            <span>FED:</span>
                            <span>₨{{ number_format($invoice->fed_amount, 2) }}</span>
                        </div>
                    @endif
                    @if($invoice->further_tax > 0)
                        <div class="d-flex justify-content-between mb-2">
                            <span>Further Tax:</span>
                            <span>₨{{ number_format($invoice->further_tax, 2) }}</span>
                        </div>
                    @endif
                    @if($invoice->withheld_tax > 0)
                        <div class="d-flex justify-content-between mb-2">
                            <span>Withheld Tax:</span>
                            <span class="text-danger">-₨{{ number_format($invoice->withheld_tax, 2) }}</span>
                        </div>
                    @endif
                    <hr>
                    <div class="d-flex justify-content-between h5">
                        <span><strong>Total Amount:</strong></span>
                        <span class="text-primary"><strong>₨{{ number_format($invoice->total_amount, 2) }}</strong></span>
                    </div>

                    @if($invoice->qr_code_path)
                        <div class="text-center mt-3">
                            <img src="{{ asset('storage/' . $invoice->qr_code_path) }}" alt="QR Code" class="img-fluid" style="max-width: 120px;">
                            <br><small class="text-muted">Scan for verification</small>
                            @if($invoice->fbr_verification_url)
                                <br><a href="{{ $invoice->fbr_verification_url }}" target="_blank" class="btn btn-outline-primary btn-sm mt-2">
                                    <i class="bi bi-link-45deg me-1"></i>Verify Online
                                </a>
                            @endif
                        </div>
                    @endif
                    
                    @if($invoice->usin)
                        <hr>
                        <div class="text-center">
                            <small class="text-muted">FBR USIN:</small>
                            <br><code>{{ $invoice->usin }}</code>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>