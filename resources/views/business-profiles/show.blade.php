<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center">
            <a href="{{ route('business-profiles.index') }}" class="btn btn-outline-secondary me-3">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h2 class="h3 mb-0">{{ $businessProfile->business_name }}</h2>
                <p class="text-muted mb-0">Business Profile Details</p>
            </div>
        </div>
    </x-slot>

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Business Information</h5>
                    <div class="btn-group">
                        <a href="{{ route('business-profiles.edit', $businessProfile) }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-pencil me-1"></i>Edit
                        </a>
                        <a href="{{ route('business-profiles.users', $businessProfile) }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-people me-1"></i>Manage Users
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted">Business Name</h6>
                            <p class="mb-3">{{ $businessProfile->business_name }}</p>

                            <h6 class="text-muted">STRN/NTN</h6>
                            <p class="mb-3">{{ $businessProfile->strn_ntn ?: 'Not provided' }}</p>

                            <h6 class="text-muted">CNIC</h6>
                            <p class="mb-3">{{ $businessProfile->cnic ?: 'Not provided' }}</p>

                            <h6 class="text-muted">Province</h6>
                            <p class="mb-3">
                                @php
                                    $provinces = [
                                        '01' => 'Punjab',
                                        '02' => 'Sindh', 
                                        '03' => 'KPK',
                                        '04' => 'Balochistan',
                                        '05' => 'Islamabad'
                                    ];
                                @endphp
                                {{ $provinces[$businessProfile->province_code] ?? $businessProfile->province_code }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Contact Phone</h6>
                            <p class="mb-3">{{ $businessProfile->contact_phone ?: 'Not provided' }}</p>

                            <h6 class="text-muted">Contact Email</h6>
                            <p class="mb-3">{{ $businessProfile->contact_email ?: 'Not provided' }}</p>

                            <h6 class="text-muted">Branch Name</h6>
                            <p class="mb-3">{{ $businessProfile->branch_name ?: 'Not provided' }}</p>

                            <h6 class="text-muted">Branch Code</h6>
                            <p class="mb-3">{{ $businessProfile->branch_code ?: 'Not provided' }}</p>
                        </div>
                    </div>

                    <h6 class="text-muted">Address</h6>
                    <p class="mb-3">{{ $businessProfile->address }}</p>

                    @if($businessProfile->whitelisted_ips)
                        <h6 class="text-muted">Whitelisted IPs</h6>
                        <p class="mb-3">
                            @foreach($businessProfile->whitelisted_ips as $ip)
                                <span class="badge bg-info me-1">{{ $ip }}</span>
                            @endforeach
                        </p>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">FBR Configuration</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted">Environment</h6>
                            <span class="badge {{ $businessProfile->is_sandbox ? 'bg-warning' : 'bg-success' }} mb-3">
                                {{ $businessProfile->is_sandbox ? 'Sandbox' : 'Production' }}
                            </span>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Status</h6>
                            <span class="badge {{ $businessProfile->is_active ? 'bg-success' : 'bg-secondary' }} mb-3">
                                {{ $businessProfile->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>

                    <h6 class="text-muted">API Token</h6>
                    <div class="input-group">
                        <input type="password" class="form-control" value="{{ $businessProfile->fbr_api_token ?: 'Not configured' }}" readonly id="apiToken">
                        <button class="btn btn-outline-secondary" type="button" onclick="toggleToken()">
                            <i class="bi bi-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Business Logo</h5>
                </div>
                <div class="card-body text-center">
                    @if($businessProfile->logo_path)
                        <img src="{{ asset('storage/' . $businessProfile->logo_path) }}" 
                             alt="Business Logo" class="img-fluid rounded mb-3" style="max-height: 200px;">
                    @else
                        <div class="bg-light rounded p-5 mb-3">
                            <i class="bi bi-image display-1 text-muted"></i>
                            <p class="text-muted mt-3">No logo uploaded</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Customers</span>
                        <span class="badge bg-primary">{{ $businessProfile->customers()->count() }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Items</span>
                        <span class="badge bg-info">{{ $businessProfile->items()->count() }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Invoices</span>
                        <span class="badge bg-success">{{ $businessProfile->invoices()->count() }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function toggleToken() {
            const tokenField = document.getElementById('apiToken');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (tokenField.type === 'password') {
                tokenField.type = 'text';
                toggleIcon.className = 'bi bi-eye-slash';
            } else {
                tokenField.type = 'password';
                toggleIcon.className = 'bi bi-eye';
            }
        }
    </script>
    @endpush
</x-app-layout>