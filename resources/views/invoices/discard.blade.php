<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center">
            <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-outline-secondary me-3">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h2 class="h3 mb-0">Discard Invoice</h2>
                <p class="text-muted mb-0">{{ $invoice->invoice_number }} - {{ $invoice->customer->name }}</p>
            </div>
        </div>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>Discard Invoice
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Warning:</strong> Discarding this invoice will mark it as inactive. 
                        You can restore it later if needed, but it will not be submitted to FBR while discarded.
                    </div>

                    <form method="POST" action="{{ route('invoices.store-discard', $invoice) }}">
                        @csrf

                        <div class="mb-4">
                            <label for="discard_reason" class="form-label">Reason for Discarding <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('discard_reason') is-invalid @enderror" 
                                      id="discard_reason" name="discard_reason" rows="4" 
                                      placeholder="Please provide a reason for discarding this invoice..." required>{{ old('discard_reason') }}</textarea>
                            @error('discard_reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">This reason will be logged for audit purposes</div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-archive me-2"></i>Discard Invoice
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Invoice Summary -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Invoice Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <small class="text-muted">Invoice Number:</small>
                            <p class="mb-2">{{ $invoice->invoice_number }}</p>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Date:</small>
                            <p class="mb-2">{{ $invoice->invoice_date->format('M d, Y') }}</p>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Customer:</small>
                            <p class="mb-2">{{ $invoice->customer->name }}</p>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Total Amount:</small>
                            <p class="mb-2">₨{{ number_format($invoice->total_amount, 2) }}</p>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Items:</small>
                            <p class="mb-2">{{ $invoice->invoiceItems->count() }} items</p>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">FBR Status:</small>
                            <span class="badge bg-{{ $invoice->fbr_status === 'submitted' ? 'success' : 'warning' }}">
                                {{ ucfirst($invoice->fbr_status) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>