<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="h3 mb-0">Customers</h2>
                <p class="text-muted mb-0">Manage your customer database</p>
            </div>
            <div class="btn-group">
                <a href="{{ route('customers.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Add Customer
                </a>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="bi bi-upload me-2"></i>Import
                </button>
                <button type="button" class="btn btn-info" onclick="exportCustomers()">
                    <i class="bi bi-download me-2"></i>Export
                </button>
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
                <div class="col-md-3">
                    <select name="customer_type" class="form-select">
                        <option value="">All Types</option>
                        <option value="registered" {{ request('customer_type') == 'registered' ? 'selected' : '' }}>Registered</option>
                        <option value="unregistered" {{ request('customer_type') == 'unregistered' ? 'selected' : '' }}>Unregistered</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search customers..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Customers Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>NTN/CNIC</th>
                            <th>Contact</th>
                            <th>Type</th>
                            <th>Business Profile</th>
                            <th>Invoices</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($customers as $customer)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                            <i class="bi bi-person text-white"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $customer->name }}</h6>
                                            @if($customer->address)
                                                <small class="text-muted">{{ Str::limit($customer->address, 30) }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $customer->ntn_cnic ?: 'N/A' }}</td>
                                <td>
                                    @if($customer->contact_email)
                                        <small class="d-block">{{ $customer->contact_email }}</small>
                                    @endif
                                    @if($customer->contact_phone)
                                        <small class="d-block text-muted">{{ $customer->contact_phone }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $customer->customer_type == 'registered' ? 'bg-success' : 'bg-info' }}">
                                        {{ ucfirst($customer->customer_type) }}
                                    </span>
                                </td>
                                <td>{{ $customer->businessProfile->business_name }}</td>
                                <td>
                                    <span class="badge bg-primary">{{ $customer->invoices()->count() }}</span>
                                </td>
                                <td>
                                    <span class="badge {{ $customer->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $customer->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('customers.show', $customer) }}" class="btn btn-outline-primary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('customers.edit', $customer) }}" class="btn btn-outline-secondary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form method="POST" action="{{ route('customers.destroy', $customer) }}" style="display: inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete" onclick="return confirm('Are you sure?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="bi bi-people display-1 text-muted mb-3"></i>
                                    <h4>No customers found</h4>
                                    <p class="text-muted">Start by adding your first customer</p>
                                    <a href="{{ route('customers.create') }}" class="btn btn-primary">
                                        <i class="bi bi-plus-circle me-2"></i>Add Customer
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($customers->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $customers->links() }}
        </div>
    @endif

    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Import Customers</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="importForm" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="business_profile_id" class="form-label">Business Profile <span class="text-danger">*</span></label>
                            <select class="form-select" id="import_business_profile_id" name="business_profile_id" required>
                                <option value="">Select Business Profile</option>
                                @foreach($businessProfiles as $profile)
                                    <option value="{{ $profile->id }}">{{ $profile->business_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="file" class="form-label">CSV/Excel File <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" id="import_file" name="file" accept=".csv,.xlsx,.xls" required>
                            <div class="form-text">Maximum file size: 10MB</div>
                        </div>
                        
                        <div class="alert alert-info">
                            <h6>Required CSV Headers:</h6>
                            <ul class="mb-0">
                                <li><strong>Name</strong> - Customer name</li>
                                <li><strong>NTN_CNIC</strong> - NTN or CNIC number</li>
                                <li><strong>Address</strong> - Customer address (optional)</li>
                                <li><strong>Contact_Phone</strong> - Phone number (optional)</li>
                                <li><strong>Contact_Email</strong> - Email address (optional)</li>
                            </ul>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-upload me-2"></i>Import Customers
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Import form submission
        document.getElementById('importForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Importing...';
            
            fetch('/api/customers/import', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Authorization': 'Bearer ' + '{{ auth()->user()->createToken("import")->plainTextToken ?? "" }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(`Import completed!\nImported: ${data.results.imported}\nUpdated: ${data.results.updated}\nSkipped: ${data.results.skipped}`);
                    location.reload();
                } else {
                    alert('Import failed: ' + data.message);
                }
            })
            .catch(error => {
                alert('Import failed: ' + error.message);
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
        
        // Export function
        function exportCustomers() {
            const businessProfileId = document.querySelector('select[name="business_profile_id"]').value;
            let url = '/api/customers/export';
            
            if (businessProfileId) {
                url += '?business_profile_id=' + businessProfileId;
            }
            
            window.location.href = url;
        }
    </script>
    @endpush
</x-app-layout>