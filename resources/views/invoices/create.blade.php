<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center">
            <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary me-3">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h2 class="h3 mb-0">Create Invoice</h2>
                <p class="text-muted mb-0">Generate a new FBR compliant invoice</p>
            </div>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('invoices.store') }}">
        @csrf
        
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
                                        <option value="{{ $profile->id }}" {{ old('business_profile_id') == $profile->id ? 'selected' : '' }}>
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
                                                {{ old('customer_id', request('customer_id')) == $customer->id ? 'selected' : '' }}>
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
                                       id="invoice_date" name="invoice_date" value="{{ old('invoice_date', date('Y-m-d')) }}" required>
                                @error('invoice_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="invoice_type" class="form-label">Invoice Type <span class="text-danger">*</span></label>
                                <select class="form-select @error('invoice_type') is-invalid @enderror" 
                                        id="invoice_type" name="invoice_type" required>
                                    <option value="">Select Type</option>
                                    <option value="sales" {{ old('invoice_type', 'sales') == 'sales' ? 'selected' : '' }}>Sales Invoice</option>
                                    <option value="purchase" {{ old('invoice_type') == 'purchase' ? 'selected' : '' }}>Purchase Invoice</option>
                                    <option value="debit_note" {{ old('invoice_type') == 'debit_note' ? 'selected' : '' }}>Debit Note</option>
                                    <option value="credit_note" {{ old('invoice_type') == 'credit_note' ? 'selected' : '' }}>Credit Note</option>
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
                                        <th style="width: 30%">Item</th>
                                        <th style="width: 10%">Qty</th>
                                        <th style="width: 15%">Unit Price</th>
                                        <th style="width: 10%">Discount %</th>
                                        <th style="width: 15%">Tax</th>
                                        <th style="width: 15%">Total</th>
                                        <th style="width: 5%"></th>
                                    </tr>
                                </thead>
                                <tbody id="itemsBody">
                                    <!-- Items will be added here dynamically -->
                                </tbody>
                            </table>
                        </div>
                        
                        @error('items')
                            <div class="text-danger">{{ $message }}</div>
                        @endif

                        <div class="alert alert-info mt-3" id="noItemsAlert">
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
                        <div class="d-flex justify-content-between">
                            <span>Subtotal:</span>
                            <span id="summarySubtotal">₨0.00</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Discount:</span>
                            <span id="summaryDiscount">₨0.00</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Tax:</span>
                            <span id="summaryTax">₨0.00</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between h5">
                            <span>Total:</span>
                            <span id="summaryTotal">₨0.00</span>
                        </div>
                        
                        <hr>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check2 me-2"></i>Create Invoice
                            </button>
                            <a href="{{ route('invoices.index') }}" class="btn btn-secondary">
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
            const currentCustomer = customerSelect.querySelector('option:checked');
            if (currentCustomer && currentCustomer.dataset.businessProfile && 
                currentCustomer.dataset.businessProfile !== businessProfileId) {
                customerSelect.value = '';
            }
        }

        function updateAvailableItems() {
            const businessProfileId = document.getElementById('business_profile_id').value;
            // Update existing item dropdowns
            document.querySelectorAll('select[name*="[item_id]"]').forEach(select => {
                updateItemOptions(select, businessProfileId);
            });
        }

        function updateItemOptions(select, businessProfileId) {
            select.innerHTML = '<option value="">Select Item</option>';
            
            if (businessProfileId && items[businessProfileId]) {
                items[businessProfileId].forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = `${item.name} (${item.item_code}) - ₨${item.price}`;
                    option.dataset.price = item.price;
                    option.dataset.taxRate = item.tax_rate;
                    select.appendChild(option);
                });
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
                    <select class="form-select" name="items[${itemIndex}][item_id]" required onchange="updateItemDetails(this, ${itemIndex})">
                        <option value="">Select Item</option>
                    </select>
                </td>
                <td>
                    <input type="number" class="form-control" name="items[${itemIndex}][quantity]" 
                           value="1" min="1" required onchange="calculateRowTotal(${itemIndex})">
                </td>
                <td>
                    <input type="number" step="0.01" class="form-control" name="items[${itemIndex}][unit_price]" 
                           value="0" min="0" required onchange="calculateRowTotal(${itemIndex})" readonly>
                </td>
                <td>
                    <input type="number" step="0.01" class="form-control" name="items[${itemIndex}][discount_rate]" 
                           value="0" min="0" max="100" onchange="calculateRowTotal(${itemIndex})">
                </td>
                <td>
                    <span class="tax-info">0% = ₨0.00</span>
                </td>
                <td>
                    <strong class="row-total">₨0.00</strong>
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
            if (!option.value) return;

            const row = select.closest('tr');
            const priceInput = row.querySelector('input[name*="[unit_price]"]');
            const taxInfo = row.querySelector('.tax-info');

            priceInput.value = option.dataset.price || 0;
            taxInfo.textContent = `${option.dataset.taxRate || 0}% = ₨0.00`;

            calculateRowTotal(rowIndex);
        }

        function calculateRowTotal(rowIndex) {
            const row = document.querySelector(`tr:nth-child(${rowIndex + 1})`);
            if (!row) return;

            const itemSelect = row.querySelector('select[name*="[item_id]"]');
            const quantity = parseFloat(row.querySelector('input[name*="[quantity]"]').value) || 0;
            const unitPrice = parseFloat(row.querySelector('input[name*="[unit_price]"]').value) || 0;
            const discountRate = parseFloat(row.querySelector('input[name*="[discount_rate]"]').value) || 0;

            const option = itemSelect.options[itemSelect.selectedIndex];
            const taxRate = parseFloat(option.dataset.taxRate) || 0;

            const lineTotal = quantity * unitPrice;
            const discountAmount = (lineTotal * discountRate) / 100;
            const afterDiscount = lineTotal - discountAmount;
            const taxAmount = (afterDiscount * taxRate) / 100;
            const rowTotal = afterDiscount + taxAmount;

            // Update tax info
            const taxInfo = row.querySelector('.tax-info');
            taxInfo.textContent = `${taxRate}% = ₨${taxAmount.toFixed(2)}`;

            // Update row total
            const rowTotalSpan = row.querySelector('.row-total');
            rowTotalSpan.textContent = `₨${rowTotal.toFixed(2)}`;

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

                const option = itemSelect.options[itemSelect.selectedIndex];
                const taxRate = parseFloat(option.dataset.taxRate) || 0;

                const lineTotal = quantity * unitPrice;
                const discountAmount = (lineTotal * discountRate) / 100;
                const afterDiscount = lineTotal - discountAmount;
                const taxAmount = (afterDiscount * taxRate) / 100;

                subtotal += afterDiscount;
                totalDiscount += discountAmount;
                totalTax += taxAmount;
            });

            const total = subtotal + totalTax;

            document.getElementById('summarySubtotal').textContent = `₨${subtotal.toFixed(2)}`;
            document.getElementById('summaryDiscount').textContent = `₨${totalDiscount.toFixed(2)}`;
            document.getElementById('summaryTax').textContent = `₨${totalTax.toFixed(2)}`;
            document.getElementById('summaryTotal').textContent = `₨${total.toFixed(2)}`;
        }

        function updateNoItemsAlert() {
            const hasItems = document.querySelectorAll('#itemsBody tr').length > 0;
            document.getElementById('noItemsAlert').style.display = hasItems ? 'none' : 'block';
        }

        // Initialize
        filterCustomers();
        updateNoItemsAlert();
    </script>
    @endpush
</x-app-layout>