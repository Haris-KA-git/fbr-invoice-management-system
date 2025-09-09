<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center">
            <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary me-3">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div class="flex-grow-1">
                <h2 class="h3 mb-0">Invoice {{ $invoice->invoice_number }}</h2>
                <p class="text-muted mb-0">{{ $invoice->customer->name }} • {{ $invoice->invoice_date->format('M d, Y') }}</p>
            </div>
            <div class="btn-group">
                <a href="{{ route('invoices.download-pdf', $invoice) }}" class="btn btn-outline-primary">
                    <i class="bi bi-download me-1"></i>Download PDF
                </a>
                @if($invoice->fbr_status === 'pending' || $invoice->fbr_status === 'failed')
                    <form method="POST" action="{{ route('invoices.submit-to-fbr', $invoice) }}" style="display: inline-block;">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-upload me-1"></i>Submit to FBR
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-body">
                    <!-- Invoice Header -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                @if($invoice->businessProfile->logo_path)
                                    <img src="{{ asset('storage/' . $invoice->businessProfile->logo_path) }}" 
                                         alt="Logo" class="me-3" style="height: 60px;">
                                @endif
                                <div>
                                    <h4 class="mb-0">{{ $invoice->businessProfile->business_name }}</h4>
                                    <small class="text-muted">{{ $invoice->businessProfile->strn_ntn }}</small>
                                </div>
                            </div>
                            <p class="small text-muted mb-0">{{ $invoice->businessProfile->address }}</p>
                            @if($invoice->businessProfile->contact_phone)
                                <p class="small text-muted mb-0">Phone: {{ $invoice->businessProfile->contact_phone }}</p>
                            @endif
                            @if($invoice->businessProfile->contact_email)
                                <p class="small text-muted mb-0">Email: {{ $invoice->businessProfile->contact_email }}</p>
                            @endif
                        </div>
                        
                        <div class="col-md-6 text-md-end">
                            <h2 class="text-primary">INVOICE</h2>
                            <p class="mb-1"><strong>Invoice #:</strong> {{ $invoice->invoice_number }}</p>
                            @if($invoice->fbr_invoice_number)
                                <p class="mb-1"><strong>FBR #:</strong> {{ $invoice->fbr_invoice_number }}</p>
                            @endif
                            <p class="mb-1"><strong>Date:</strong> {{ $invoice->invoice_date->format('M d, Y') }}</p>
                            <p class="mb-1">
                                <strong>Type:</strong> 
                                <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $invoice->invoice_type)) }}</span>
                            </p>
                        </div>
                    </div>

                    <!-- Customer Info -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Bill To:</h5>
                            <p class="mb-1"><strong>{{ $invoice->customer->name }}</strong></p>
                            @if($invoice->customer->ntn_cnic)
                                <p class="mb-1">{{ $invoice->customer->customer_type == 'registered' ? 'NTN' : 'CNIC' }}: {{ $invoice->customer->ntn_cnic }}</p>
                            @endif
                            @if($invoice->customer->address)
                                <p class="mb-1">{{ $invoice->customer->address }}</p>
                            @endif
                            @if($invoice->customer->contact_phone)
                                <p class="mb-1">Phone: {{ $invoice->customer->contact_phone }}</p>
                            @endif
                            @if($invoice->customer->contact_email)
                                <p class="mb-0">Email: {{ $invoice->customer->contact_email }}</p>
                            @endif
                        </div>
                        
                        <div class="col-md-6 text-md-end">
                            @if($invoice->qr_code)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $invoice->qr_code) }}" alt="QR Code" style="width: 100px;">
                                    <div><small class="text-muted">Scan for verification</small></div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Invoice Items -->
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Item</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-center">Discount</th>
                                    <th class="text-center">Tax Rate</th>
                                    <th class="text-end">Tax Amount</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoice->invoiceItems as $item)
                                    <tr>
                                        <td>
                                            <div>
                                                <strong>{{ $item->item->name }}</strong>
                                                <br><small class="text-muted">Code: {{ $item->item->item_code }}</small>
                                                @if($item->item->description)
                                                    <br><small class="text-muted">{{ $item->item->description }}</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="text-center">{{ $item->quantity }} {{ $item->item->unit_of_measure }}</td>
                                        <td class="text-end">₨{{ number_format($item->unit_price, 2) }}</td>
                                        <td class="text-center">
                                            @if($item->discount_rate > 0)
                                                {{ $item->discount_rate }}%<br>
                                                <small>(₨{{ number_format($item->discount_amount, 2) }})</small>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $item->tax_rate }}%</td>
                                        <td class="text-end">₨{{ number_format($item->tax_amount, 2) }}</td>
                                        <td class="text-end">₨{{ number_format($item->line_total, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Invoice Totals -->
                    <div class="row">
                        <div class="col-md-6"></div>
                        <div class="col-md-6">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <tr>
                                        <td class="text-end"><strong>Subtotal:</strong></td>
                                        <td class="text-end">₨{{ number_format($invoice->subtotal, 2) }}</td>
                                    </tr>
                                    @if($invoice->discount_amount > 0)
                                        <tr>
                                            <td class="text-end">Discount:</td>
                                            <td class="text-end">-₨{{ number_format($invoice->discount_amount, 2) }}</td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <td class="text-end">Sales Tax:</td>
                                        <td class="text-end">₨{{ number_format($invoice->sales_tax, 2) }}</td>
                                    </tr>
                                    @if($invoice->fed_amount > 0)
                                        <tr>
                                            <td class="text-end">FED:</td>
                                            <td class="text-end">₨{{ number_format($invoice->fed_amount, 2) }}</td>
                                        </tr>
                                    @endif
                                    @if($invoice->further_tax > 0)
                                        <tr>
                                            <td class="text-end">Further Tax:</td>
                                            <td class="text-end">₨{{ number_format($invoice->further_tax, 2) }}</td>
                                        </tr>
                                    @endif
                                    @if($invoice->withheld_tax > 0)
                                        <tr>
                                            <td class="text-end">Withheld Tax:</td>
                                            <td class="text-end">-₨{{ number_format($invoice->withheld_tax, 2) }}</td>
                                        </tr>
                                    @endif
                                    <tr class="table-dark">
                                        <td class="text-end"><strong>Total Amount:</strong></td>
                                        <td class="text-end"><strong>₨{{ number_format($invoice->total_amount, 2) }}</strong></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">FBR Status</h5>
                </div>
                <div class="card-body">
                    @php
                        $statusColors = [
                            'pending' => 'warning',
                            'validated' => 'info',
                            'submitted' => 'success',
                            'failed' => 'danger'
                        ];
                        $statusIcons = [
                            'pending' => 'clock',
                            'validated' => 'check-circle',
                            'submitted' => 'check2-circle',
                            'failed' => 'exclamation-circle'
                        ];
                    @endphp
                    
                    <div class="text-center mb-3">
                        <i class="bi bi-{{ $statusIcons[$invoice->fbr_status] ?? 'question-circle' }} display-1 text-{{ $statusColors[$invoice->fbr_status] ?? 'secondary' }}"></i>
                        <h4 class="mt-2">{{ ucfirst($invoice->fbr_status) }}</h4>
                    </div>

                    @if($invoice->fbr_status === 'failed' && $invoice->fbr_error_message)
                        <div class="alert alert-danger">
                            <small><strong>Error:</strong> {{ $invoice->fbr_error_message }}</small>
                        </div>
                    @endif

                    @if($invoice->fbr_status === 'submitted' && $invoice->fbr_response)
                        <div class="alert alert-success">
                            <small><strong>Successfully submitted to FBR</strong></small>
                        </div>
                    @endif

                    <div class="mb-3">
                        <small class="text-muted">Created:</small><br>
                        <span>{{ $invoice->created_at->format('M d, Y H:i') }}</span>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted">Last Updated:</small><br>
                        <span>{{ $invoice->updated_at->format('M d, Y H:i') }}</span>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('invoices.download-pdf', $invoice) }}" class="btn btn-outline-primary">
                            <i class="bi bi-download me-2"></i>Download PDF
                        </a>
                        
                        @if($invoice->fbr_status === 'pending' || $invoice->fbr_status === 'failed')
                            <form method="POST" action="{{ route('invoices.submit-to-fbr', $invoice) }}">
                                @csrf
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="bi bi-upload me-2"></i>Submit to FBR
                                </button>
                            </form>
                        @endif

                        <a href="{{ route('customers.show', $invoice->customer) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-person me-2"></i>View Customer
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>