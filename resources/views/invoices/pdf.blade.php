<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        
        .header {
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .logo {
            float: left;
            width: 60%;
        }
        
        .logo img {
            max-height: 60px;
        }
        
        .invoice-info {
            float: right;
            width: 35%;
            text-align: right;
        }
        
        .invoice-info h2 {
            color: #007bff;
            margin: 0 0 10px 0;
            font-size: 24px;
        }
        
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
        
        .business-info {
            margin-bottom: 20px;
        }
        
        .business-info h3 {
            margin: 0 0 5px 0;
            color: #007bff;
        }
        
        .customer-section {
            margin-bottom: 30px;
        }
        
        .customer-info {
            float: left;
            width: 60%;
        }
        
        .qr-section {
            float: right;
            width: 35%;
            text-align: right;
        }
        
        .qr-section img {
            width: 100px;
            height: 100px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .items-table th,
        .items-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        .items-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .totals-table {
            float: right;
            width: 300px;
        }
        
        .totals-table td {
            padding: 5px 10px;
            border: none;
        }
        
        .total-row {
            background-color: #007bff;
            color: white;
            font-weight: bold;
        }
        
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 20px;
            font-size: 10px;
            text-align: center;
            border-top: 1px solid #ddd;
            background-color: white;
        }
        
        .page-break {
            page-break-after: always;
        }
        
        .badge {
            display: inline-block;
            padding: 3px 8px;
            font-size: 10px;
            background-color: #e9ecef;
            color: #495057;
            border-radius: 3px;
        }
        
        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }
        
        .badge-info {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        .fbr-logo {
            position: absolute;
            top: 20px;
            right: 20px;
            opacity: 0.1;
            font-size: 48px;
            color: #007bff;
        }
    </style>
</head>
<body>
    <div class="fbr-logo">FBR</div>
    
    <!-- Header -->
    <div class="header clearfix">
        <div class="logo">
            @if($invoice->businessProfile->logo_path && file_exists(public_path('storage/' . $invoice->businessProfile->logo_path)))
                <img src="{{ public_path('storage/' . $invoice->businessProfile->logo_path) }}" alt="Logo">
            @endif
            <div class="business-info">
                <h3>{{ $invoice->businessProfile->business_name }}</h3>
                <div>{{ $invoice->businessProfile->address }}</div>
                @if($invoice->businessProfile->strn_ntn)
                    <div><strong>STRN/NTN:</strong> {{ $invoice->businessProfile->strn_ntn }}</div>
                @endif
                @if($invoice->businessProfile->contact_phone)
                    <div><strong>Phone:</strong> {{ $invoice->businessProfile->contact_phone }}</div>
                @endif
                @if($invoice->businessProfile->contact_email)
                    <div><strong>Email:</strong> {{ $invoice->businessProfile->contact_email }}</div>
                @endif
            </div>
        </div>
        
        <div class="invoice-info">
            <h2>INVOICE</h2>
            <div><strong>Invoice #:</strong> {{ $invoice->invoice_number }}</div>
            @if($invoice->fbr_invoice_number)
                <div><strong>FBR #:</strong> {{ $invoice->fbr_invoice_number }}</div>
            @endif
            <div><strong>Date:</strong> {{ $invoice->invoice_date->format('M d, Y') }}</div>
            <div><strong>Type:</strong> {{ ucfirst(str_replace('_', ' ', $invoice->invoice_type)) }}</div>
            @if($invoice->fbr_status === 'submitted')
                <div class="badge badge-success">FBR Submitted</div>
            @endif
        </div>
    </div>
    
    <!-- Customer Information -->
    <div class="customer-section clearfix">
        <div class="customer-info">
            <h3>Bill To:</h3>
            <div><strong>{{ $invoice->customer->name }}</strong></div>
            @if($invoice->customer->ntn_cnic)
                <div>
                    <strong>{{ $invoice->customer->customer_type == 'registered' ? 'NTN' : 'CNIC' }}:</strong> 
                    {{ $invoice->customer->ntn_cnic }}
                </div>
            @endif
            @if($invoice->customer->address)
                <div>{{ $invoice->customer->address }}</div>
            @endif
            @if($invoice->customer->contact_phone)
                <div><strong>Phone:</strong> {{ $invoice->customer->contact_phone }}</div>
            @endif
            @if($invoice->customer->contact_email)
                <div><strong>Email:</strong> {{ $invoice->customer->contact_email }}</div>
            @endif
        </div>
        
        @if($qrCodePath && file_exists($qrCodePath))
            <div class="qr-section">
                <img src="{{ $qrCodePath }}" alt="QR Code">
                <div style="font-size: 10px; margin-top: 5px;">Scan for verification</div>
            </div>
        @endif
    </div>
    
    <!-- Invoice Items -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 30%;">Item Description</th>
                <th style="width: 8%;" class="text-center">Qty</th>
                <th style="width: 12%;" class="text-right">Unit Price</th>
                <th style="width: 10%;" class="text-center">Discount</th>
                <th style="width: 10%;" class="text-center">Tax Rate</th>
                <th style="width: 12%;" class="text-right">Tax Amount</th>
                <th style="width: 15%;" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->invoiceItems as $item)
                <tr>
                    <td>
                        <strong>{{ $item->item->name }}</strong><br>
                        <small>Code: {{ $item->item->item_code }}</small>
                        @if($item->item->hs_code)
                            <br><small>HS Code: {{ $item->item->hs_code }}</small>
                        @endif
                    </td>
                    <td class="text-center">{{ $item->quantity }} {{ $item->item->unit_of_measure }}</td>
                    <td class="text-right">₨{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-center">
                        @if($item->discount_rate > 0)
                            {{ $item->discount_rate }}%<br>
                            <small>(₨{{ number_format($item->discount_amount, 2) }})</small>
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-center">{{ $item->tax_rate }}%</td>
                    <td class="text-right">₨{{ number_format($item->tax_amount, 2) }}</td>
                    <td class="text-right">₨{{ number_format($item->line_total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <!-- Totals -->
    <div class="clearfix">
        <table class="totals-table">
            <tr>
                <td class="text-right"><strong>Subtotal:</strong></td>
                <td class="text-right">₨{{ number_format($invoice->subtotal, 2) }}</td>
            </tr>
            @if($invoice->discount_amount > 0)
                <tr>
                    <td class="text-right">Total Discount:</td>
                    <td class="text-right">-₨{{ number_format($invoice->discount_amount, 2) }}</td>
                </tr>
            @endif
            <tr>
                <td class="text-right">Sales Tax:</td>
                <td class="text-right">₨{{ number_format($invoice->sales_tax, 2) }}</td>
            </tr>
            @if($invoice->fed_amount > 0)
                <tr>
                    <td class="text-right">FED:</td>
                    <td class="text-right">₨{{ number_format($invoice->fed_amount, 2) }}</td>
                </tr>
            @endif
            @if($invoice->further_tax > 0)
                <tr>
                    <td class="text-right">Further Tax:</td>
                    <td class="text-right">₨{{ number_format($invoice->further_tax, 2) }}</td>
                </tr>
            @endif
            @if($invoice->withheld_tax > 0)
                <tr>
                    <td class="text-right">Withheld Tax:</td>
                    <td class="text-right">-₨{{ number_format($invoice->withheld_tax, 2) }}</td>
                </tr>
            @endif
            <tr class="total-row">
                <td class="text-right"><strong>Total Amount:</strong></td>
                <td class="text-right"><strong>₨{{ number_format($invoice->total_amount, 2) }}</strong></td>
            </tr>
        </table>
    </div>
    
    <!-- Footer -->
    <div class="footer">
        <div style="text-align: left; float: left;">
            Generated on {{ now()->format('M d, Y H:i') }}
        </div>
        <div style="text-align: right; float: right;">
            This is a computer generated invoice
        </div>
        <div style="text-align: center;">
            {{ $invoice->businessProfile->business_name }} - FBR Compliant Digital Invoice
        </div>
    </div>
</body>
</html>