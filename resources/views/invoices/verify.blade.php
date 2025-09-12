<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice Verification - {{ $invoice->invoice_number }}</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
        }
        
        .verification-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        
        .verification-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border-radius: 20px 20px 0 0;
            padding: 2rem;
            text-align: center;
        }
        
        .verification-icon {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="verification-card">
                    <div class="verification-header">
                        <div class="verification-icon">
                            <i class="bi bi-shield-check display-4"></i>
                        </div>
                        <h2 class="mb-2">Invoice Verified</h2>
                        <p class="mb-0">This invoice is FBR compliant and verified</p>
                    </div>
                    
                    <div class="card-body p-4">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="text-muted">Invoice Number</h6>
                                <p class="h5 text-primary">{{ $invoice->invoice_number }}</p>
                                
                                @if($invoice->usin)
                                    <h6 class="text-muted">FBR USIN</h6>
                                    <p class="mb-3"><code>{{ $invoice->usin }}</code></p>
                                @endif
                                
                                <h6 class="text-muted">Invoice Date</h6>
                                <p class="mb-3">{{ $invoice->invoice_date->format('M d, Y') }}</p>
                            </div>
                            <div class="col-md-6 text-end">
                                @if($invoice->qr_code_path)
                                    <img src="{{ asset('storage/' . $invoice->qr_code_path) }}" 
                                         alt="QR Code" class="img-fluid" style="max-width: 150px;">
                                @endif
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-header">
                                        <h6 class="mb-0">Business Information</h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="mb-1"><strong>{{ $invoice->businessProfile->business_name }}</strong></p>
                                        @if($invoice->businessProfile->strn_ntn)
                                            <p class="mb-1">STRN/NTN: {{ $invoice->businessProfile->strn_ntn }}</p>
                                        @endif
                                        <p class="mb-0 small text-muted">{{ $invoice->businessProfile->address }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-header">
                                        <h6 class="mb-0">Customer Information</h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="mb-1"><strong>{{ $invoice->customer->name }}</strong></p>
                                        @if($invoice->customer->ntn_cnic)
                                            <p class="mb-1">
                                                {{ $invoice->customer->customer_type === 'registered' ? 'NTN' : 'CNIC' }}: 
                                                {{ $invoice->customer->ntn_cnic }}
                                            </p>
                                        @endif
                                        @if($invoice->customer->address)
                                            <p class="mb-0 small text-muted">{{ $invoice->customer->address }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Invoice Summary</h6>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-md-3">
                                        <h5 class="text-primary">{{ $invoice->invoiceItems->count() }}</h5>
                                        <small class="text-muted">Items</small>
                                    </div>
                                    <div class="col-md-3">
                                        <h5 class="text-info">₨{{ number_format($invoice->subtotal, 2) }}</h5>
                                        <small class="text-muted">Subtotal</small>
                                    </div>
                                    <div class="col-md-3">
                                        <h5 class="text-warning">₨{{ number_format($invoice->sales_tax, 2) }}</h5>
                                        <small class="text-muted">Sales Tax</small>
                                    </div>
                                    <div class="col-md-3">
                                        <h5 class="text-success">₨{{ number_format($invoice->total_amount, 2) }}</h5>
                                        <small class="text-muted">Total Amount</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center mt-4">
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle me-2"></i>
                                <strong>Verified:</strong> This invoice is authentic and FBR compliant
                            </div>
                            
                            <p class="text-muted small">
                                Verification performed on {{ now()->format('M d, Y H:i') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>