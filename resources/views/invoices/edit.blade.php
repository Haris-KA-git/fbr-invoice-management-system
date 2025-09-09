<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center">
            <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-outline-secondary me-3">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h2 class="h3 mb-0">Edit Invoice</h2>
                <p class="text-muted mb-0">{{ $invoice->invoice_number }} - {{ $invoice->customer->name }}</p>
            </div>
        </div>
    </x-slot>

    @if($invoice->status === 'discarded')
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Warning:</strong> This invoice is currently discarded. You can edit it, but you'll need to restore it before submitting to FBR.
        </div>
    @endif

    @if($invoice->fbr_status === 'submitted')
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Note:</strong> This invoice has been submitted to FBR. Changes may require re-submission.
        </div>
    @endif

    <form method="POST" action="{{ route('invoices.update', $invoice) }}" id="invoiceForm">
        @csrf
        @method('PUT')
        
        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Invoice Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="business_profile_id" class="form-label">Business Profile <span class="text-danger">*</span></label>
                                <select class="form-select @error('business_profile_id') is-invalid @enderror" 
                                        id="business_profile_id" name="business_profile_id" required>
                                    <option value="">Select Business Profile</option>
                                    @foreach($businessProfiles as $profile)
                                        <option value="{{ $profile->id }}" {{ old('business_profile_id', $invoice->business_profile_id) == $profile->id ? 'selected' : '' }}>
                                            {{ $profile->business_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('business_profile_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="customer_id" class="form-label">Customer <span class="text-danger">*</span></label>
                                <select class="form-select @error('customer_id') is-invalid @enderror" 
                                        id="customer_id" name="customer_id" required>
                                    <option value="">Select Customer</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" 
                                                data-business-profile="{{ $customer->business_profile_id }}"
                                                {{ old('customer_id', $invoice->customer_id) == $customer->id ? 'selected' : '' }}>
                                            {{ $customer->name }} ({{ $customer->businessProfile->business_name }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('customer_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="invoice_date" class="form-label">Invoice Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('invoice_date') is-invalid @enderror" 
                                       id="invoice_date" name="invoice_date" 
                                       value="{{ old('invoice_date', $invoice->invoice_date->format('Y-m-d')) }}" required>
                                @error('invoice_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="invoice_type" class="form-label">Invoice Type <span class="text-danger">*</span></label>
                                <select class="form-select @error('invoice_type') is-invalid @enderror" 
                                        id="invoice_type" name="invoice_type" required>
                                    <option value="">Select Type</option>
                                    <option value="sales" {{ old('invoice_type', $invoice->invoice_type) == 'sales' ? 'selected' : '' }}>Sales Invoice</option>
                                    <option value="purchase" {{ old('invoice_type', $invoice->invoice_type) == 'purchase' ? 'selected' : '' }}>Purchase Invoice</option>
                                    <option value="debit_note" {{ old('invoice_type', $invoice->invoice_type) == 'debit_note' ? 'selected' : '' }}>Debit Note</option>
                                    <option value="credit_note" {{ old('invoice_type', $invoice->invoice_type) == 'credit_note' ? 'selected' : '' }}>Credit Note</option>
                                </select>
                                @error('invoice_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Invoice Items -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Invoice Items</h5>
                        <button type="button" class="btn btn-primary btn-sm" onclick="addItem()">
                            <i class="bi bi-plus-circle me-1"></i>Add Item
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table" id="itemsTable">
                                <thead>
                                    <tr>
                                        <th style="width: 25%">Item</th>
                                        <th style="width: 8%">Qty</th>
                                        <th style="width: 12%">Unit Price</th>
                                        <th style="width: 10%">Discount %</th>
                                        <th style="width: 10%">Tax Rate %</th>
                                        <th style="width: 12%">Tax Amount</th>
                                        <th style="width: 15%">Line Total</th>
                                        <th style="width: 8%">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsBody">
                                    <!-- Existing items will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                        
                        @error('items')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror

                        <div class="alert alert-info mt-3" id="noItemsAlert" style="display: none;">
                            <i class="bi bi-info-circle me-2"></i>Click "Add Item" to start adding products or services to this invoice.
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card mb-4" style="position: sticky; top: 20px;">
                    <div class="card-header">
                        <h5 class="mb-0">Invoice Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span id="summarySubtotal">₨0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Discount:</span>
                            <span id="summaryDiscount" class="text-danger">₨0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Tax:</span>
                            <span id="summaryTax" class="text-success">₨0.00</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between h5">
                            <span><strong>Total Amount:</strong></span>
                            <span id="summaryTotal" class="text-primary"><strong>₨0.00</strong></span>
                        </div>
                        
                        <hr>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="bi bi-check2 me-2"></i>Update Invoice
                            </button>
                            <button type="submit" name="save_as_draft" value="1" class="btn btn-outline-secondary" id="draftBtn">
                                <i class="bi bi-file-earmark me-2"></i>Save as Draft
                            </button>
                            <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-secondary">
                                Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
    <script>
        let itemIndex = 0;
        const items = @json($items->groupBy('business_profile_id'));
        const existingItems = @json($invoice->invoiceItems->map(function($item) {
            return [
                'item_id' => $item->item_id,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'discount_rate' => $item->discount_rate,
                'tax_rate' => $item->tax_rate,
                'tax_amount' => $item->tax_amount,
                'line_total' => $item->line_total,
                'item_name' => $item->item->name,
                'item_code' => $item->item->item_code
            ];
        }));

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            filterCustomers();
            loadExistingItems();
            updateSummary();
        });

        // Filter customers based on business profile
        document.getElementById('business_profile_id').addEventListener('change', function() {
            filterCustomers();
            updateAvailableItems();
        });

        function filterCustomers() {
            const businessProfileId = document.getElementById('business_profile_id').value;
            const customerSelect = document.getElementById('customer_id');
            const customers = customerSelect.querySelectorAll('option');
            
            customers.forEach(option => {
                if (option.value === '') return;
                
                const customerBusinessProfile = option.dataset.businessProfile;
                if (!businessProfileId || customerBusinessProfile === businessProfileId) {
                    option.style.display = 'block';
                } else {
                    option.style.display = 'none';
                }
            });
            
            // Reset customer selection if current selection is not valid
            const currentCustomer = customerSelect.value;
            if (currentCustomer) {
                const currentOption = customerSelect.querySelector(`option[value="${currentCustomer}"]`);
                if (currentOption && currentOption.style.display === 'none') {
                    customerSelect.value = '';
                }
            }
        }

        function loadExistingItems() {
            existingItems.forEach(item => {
                addExistingItem(item);
            });
            updateNoItemsAlert();
        }

        function addExistingItem(itemData) {
            const businessProfileId = document.getElementById('business_profile_id').value;
            const tbody = document.getElementById('itemsBody');
            const row = document.createElement('tr');
            
            row.innerHTML = `
                <td>
                    <select class="form-select form-select-sm" name="items[${itemIndex}][item_id]" required onchange="updateItemDetails(this, ${itemIndex})">
                        <option value="">Select Item</option>
                    </select>
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm" name="items[${itemIndex}][quantity]" 
                           value="${itemData.quantity}" min="1" step="1" required onchange="calculateRowTotal(${itemIndex})">
                </td>
                <td>
                    <input type="number" step="0.01" class="form-control form-control-sm" name="items[${itemIndex}][unit_price]" 
                           value="${parseFloat(itemData.unit_price).toFixed(2)}" min="0" required onchange="calculateRowTotal(${itemIndex})">
                </td>
                <td>
                    <input type="number" step="0.01" class="form-control form-control-sm" name="items[${itemIndex}][discount_rate]" 
                           value="${parseFloat(itemData.discount_rate).toFixed(2)}" min="0" max="100" onchange="calculateRowTotal(${itemIndex})">
                </td>
                <td>
                    <input type="number" step="0.01" class="form-control form-control-sm" name="items[${itemIndex}][tax_rate]" 
                           value="${parseFloat(itemData.tax_rate).toFixed(2)}" min="0" max="100" required onchange="calculateRowTotal(${itemIndex})">
                </td>
                <td>
                    <input type="number" step="0.01" class="form-control form-control-sm" name="items[${itemIndex}][tax_amount]" 
                           value="${parseFloat(itemData.tax_amount).toFixed(2)}" min="0" readonly style="background-color: #f8f9fa;">
                </td>
                <td>
                    <input type="number" step="0.01" class="form-control form-control-sm" name="items[${itemIndex}][line_total]" 
                           value="${parseFloat(itemData.line_total).toFixed(2)}" min="0" readonly style="background-color: #f8f9fa;">
                </td>
                <td>
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeItem(this)">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            `;
            
            tbody.appendChild(row);
            
            // Update item options and select the current item
            const itemSelect = row.querySelector('select');
            updateItemOptions(itemSelect, businessProfileId);
            itemSelect.value = itemData.item_id;
            
            itemIndex++;
        }

        function updateAvailableItems() {
            const businessProfileId = document.getElementById('business_profile_id').value;
            document.querySelectorAll('select[name*="[item_id]"]').forEach(select => {
                updateItemOptions(select, businessProfileId);
            });
        }

        function updateItemOptions(select, businessProfileId) {
            const currentValue = select.value;
            select.innerHTML = '<option value="">Select Item</option>';
            
            if (businessProfileId && items[businessProfileId]) {
                items[businessProfileId].forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = `${item.name} (${item.item_code})`;
                    option.dataset.price = item.price;
                    option.dataset.taxRate = item.tax_rate;
                    option.dataset.name = item.name;
                    option.dataset.code = item.item_code;
                    option.dataset.uom = item.unit_of_measure;
                    select.appendChild(option);
                });
                
                if (currentValue) {
                    select.value = currentValue;
                }
            }
        }

        function addItem() {
            const businessProfileId = document.getElementById('business_profile_id').value;
            if (!businessProfileId) {
                alert('Please select a business profile first');
                return;
            }

            const tbody = document.getElementById('itemsBody');
            const row = document.createElement('tr');
            
            row.innerHTML = `
                <td>
                    <select class="form-select form-select-sm" name="items[${itemIndex}][item_id]" required onchange="updateItemDetails(this, ${itemIndex})">
                        <option value="">Select Item</option>
                    </select>
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm" name="items[${itemIndex}][quantity]" 
                           value="1" min="1" step="1" required onchange="calculateRowTotal(${itemIndex})">
                </td>
                <td>
                    <input type="number" step="0.01" class="form-control form-control-sm" name="items[${itemIndex}][unit_price]" 
                           value="0.00" min="0" required onchange="calculateRowTotal(${itemIndex})">
                </td>
                <td>
                    <input type="number" step="0.01" class="form-control form-control-sm" name="items[${itemIndex}][discount_rate]" 
                           value="0.00" min="0" max="100" onchange="calculateRowTotal(${itemIndex})">
                </td>
                <td>
                    <input type="number" step="0.01" class="form-control form-control-sm" name="items[${itemIndex}][tax_rate]" 
                           value="0.00" min="0" max="100" required onchange="calculateRowTotal(${itemIndex})">
                </td>
                <td>
                    <input type="number" step="0.01" class="form-control form-control-sm" name="items[${itemIndex}][tax_amount]" 
                           value="0.00" min="0" readonly style="background-color: #f8f9fa;">
                </td>
                <td>
                    <input type="number" step="0.01" class="form-control form-control-sm" name="items[${itemIndex}][line_total]" 
                           value="0.00" min="0" readonly style="background-color: #f8f9fa;">
                </td>
                <td>
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeItem(this)">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            `;
            
            tbody.appendChild(row);
            
            // Update item options for the new row
            const itemSelect = row.querySelector('select');
            updateItemOptions(itemSelect, businessProfileId);
            
            itemIndex++;
            updateNoItemsAlert();
        }

        function removeItem(button) {
            button.closest('tr').remove();
            updateNoItemsAlert();
            updateSummary();
        }

        function updateItemDetails(select, rowIndex) {
            const option = select.options[select.selectedIndex];
            const row = select.closest('tr');
            
            if (!option.value) return;

            const priceInput = row.querySelector('input[name*="[unit_price]"]');
            const taxRateInput = row.querySelector('input[name*="[tax_rate]"]');

            // Only update if fields are empty or user confirms
            if (priceInput.value == 0 || confirm('Update price and tax rate from item defaults?')) {
                priceInput.value = parseFloat(option.dataset.price || 0).toFixed(2);
                taxRateInput.value = parseFloat(option.dataset.taxRate || 0).toFixed(2);
            }

            calculateRowTotal(rowIndex);
        }

        function calculateRowTotal(rowIndex) {
            const rows = document.querySelectorAll('#itemsBody tr');
            if (rowIndex >= rows.length) return;
            
            const row = rows[rowIndex];
            const quantity = parseFloat(row.querySelector('input[name*="[quantity]"]').value) || 0;
            const unitPrice = parseFloat(row.querySelector('input[name*="[unit_price]"]').value) || 0;
            const discountRate = parseFloat(row.querySelector('input[name*="[discount_rate]"]').value) || 0;
            const taxRate = parseFloat(row.querySelector('input[name*="[tax_rate]"]').value) || 0;

            // Calculate line amounts
            const lineSubtotal = quantity * unitPrice;
            const discountAmount = (lineSubtotal * discountRate) / 100;
            const afterDiscount = lineSubtotal - discountAmount;
            const taxAmount = (afterDiscount * taxRate) / 100;
            const lineTotal = afterDiscount + taxAmount;

            // Update the display fields
            row.querySelector('input[name*="[tax_amount]"]').value = taxAmount.toFixed(2);
            row.querySelector('input[name*="[line_total]"]').value = lineTotal.toFixed(2);

            updateSummary();
        }

        function updateSummary() {
            let subtotal = 0;
            let totalDiscount = 0;
            let totalTax = 0;

            document.querySelectorAll('#itemsBody tr').forEach((row, index) => {
                const itemSelect = row.querySelector('select[name*="[item_id]"]');
                if (!itemSelect.value) return;

                const quantity = parseFloat(row.querySelector('input[name*="[quantity]"]').value) || 0;
                const unitPrice = parseFloat(row.querySelector('input[name*="[unit_price]"]').value) || 0;
                const discountRate = parseFloat(row.querySelector('input[name*="[discount_rate]"]').value) || 0;
                const taxRate = parseFloat(row.querySelector('input[name*="[tax_rate]"]').value) || 0;

                const lineSubtotal = quantity * unitPrice;
                const discountAmount = (lineSubtotal * discountRate) / 100;
                const afterDiscount = lineSubtotal - discountAmount;
                const taxAmount = (afterDiscount * taxRate) / 100;

                subtotal += afterDiscount;
                totalDiscount += discountAmount;
                totalTax += taxAmount;
            });

            const total = subtotal + totalTax;

            document.getElementById('summarySubtotal').textContent = `₨${subtotal.toLocaleString('en-PK', {minimumFractionDigits: 2})}`;
            document.getElementById('summaryDiscount').textContent = `₨${totalDiscount.toLocaleString('en-PK', {minimumFractionDigits: 2})}`;
            document.getElementById('summaryTax').textContent = `₨${totalTax.toLocaleString('en-PK', {minimumFractionDigits: 2})}`;
            document.getElementById('summaryTotal').textContent = `₨${total.toLocaleString('en-PK', {minimumFractionDigits: 2})}`;
        }

        function updateNoItemsAlert() {
            const hasItems = document.querySelectorAll('#itemsBody tr').length > 0;
            document.getElementById('noItemsAlert').style.display = hasItems ? 'none' : 'block';
        }

        // Form submission validation
        document.getElementById('invoiceForm').addEventListener('submit', function(e) {
            const hasItems = document.querySelectorAll('#itemsBody tr').length > 0;
            if (!hasItems) {
                e.preventDefault();
                alert('Please add at least one item to the invoice.');
                return false;
            }

            // Validate all items have required fields
            let isValid = true;
            document.querySelectorAll('#itemsBody tr').forEach((row, index) => {
                const itemSelect = row.querySelector('select[name*="[item_id]"]');
                const quantity = row.querySelector('input[name*="[quantity]"]');
                const unitPrice = row.querySelector('input[name*="[unit_price]"]');
                const taxRate = row.querySelector('input[name*="[tax_rate]"]');

                if (!itemSelect.value || !quantity.value || !unitPrice.value || taxRate.value === '') {
                    isValid = false;
                    row.style.backgroundColor = '#ffe6e6';
                    setTimeout(() => {
                        row.style.backgroundColor = '';
                    }, 3000);
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields for each item (highlighted in red).');
                return false;
            }
        });
    </script>
    @endpush
</x-app-layout>