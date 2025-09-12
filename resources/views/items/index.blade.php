<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="h3 mb-0">Items</h2>
                <p class="text-muted mb-0">Manage your product and service catalog</p>
            </div>
            <div class="btn-group">
                <a href="{{ route('items.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Add Item
                </a>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="bi bi-upload me-2"></i>Import
                </button>
                <button type="button" class="btn btn-info" onclick="exportItems()">
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
                <div class="col-md-5">
                    <input type="text" name="search" class="form-control" placeholder="Search by name or item code..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Items Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Code</th>
                            <th>HS Code</th>
                            <th>UOM</th>
                            <th>Price</th>
                            <th>Tax Rate</th>
                            <th>Business Profile</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                            <tr>
                                <td>
                                    <div>
                                        <h6 class="mb-0">{{ $item->name }}</h6>
                                        @if($item->description)
                                            <small class="text-muted">{{ Str::limit($item->description, 40) }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td><code>{{ $item->item_code }}</code></td>
                                <td>{{ $item->hs_code ?: 'N/A' }}</td>
                                <td>{{ $item->unit_of_measure }}</td>
                                <td>₨{{ number_format($item->price, 2) }}</td>
                                <td>{{ $item->tax_rate }}%</td>
                                <td>{{ $item->businessProfile->business_name }}</td>
                                <td>
                                    <span class="badge {{ $item->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $item->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('items.show', $item) }}" class="btn btn-outline-primary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('items.edit', $item) }}" class="btn btn-outline-secondary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form method="POST" action="{{ route('items.destroy', $item) }}" style="display: inline-block;">
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
                                <td colspan="9" class="text-center py-4">
                                    <i class="bi bi-box display-1 text-muted mb-3"></i>
                                    <h4>No items found</h4>
                                    <p class="text-muted">Start by adding your first product or service</p>
                                    <a href="{{ route('items.create') }}" class="btn btn-primary">
                                        <i class="bi bi-plus-circle me-2"></i>Add Item
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($items->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $items->links() }}
        </div>
    @endif

    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Import Items</h5>
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
                                <li><strong>Name</strong> - Item name</li>
                                <li><strong>HS_Code</strong> - Harmonized System Code</li>
                                <li><strong>Unit_of_Measure</strong> - UOM (PCS, KG, etc.)</li>
                                <li><strong>Price</strong> - Unit price</li>
                                <li><strong>GST_Rate</strong> - Tax rate percentage</li>
                                <li><strong>Description</strong> - Item description (optional)</li>
                                <li><strong>Item_Code</strong> - Item code (optional, auto-generated)</li>
                            </ul>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-upload me-2"></i>Import Items
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
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Importing...';
        });
        
        // Update form action
        document.getElementById('importForm').action = '{{ route("items.import") }}';
        
        // Export function
        function exportItems() {
            const businessProfileId = document.querySelector('select[name="business_profile_id"]').value;
            let url = '{{ route("items.export") }}';
            
            if (businessProfileId) {
                url += '?business_profile_id=' + businessProfileId;
            }
            
            window.location.href = url;
        }
        });
        
        // Export function
        function exportItems() {
            const businessProfileId = document.querySelector('select[name="business_profile_id"]').value;
            let url = '/api/items/export';
            
            if (businessProfileId) {
                url += '?business_profile_id=' + businessProfileId;
            }
            
            window.location.href = url;
        }
    </script>
    @endpush
</x-app-layout>