<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center">
            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary me-3">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h2 class="h3 mb-0">{{ $user->name }}</h2>
                <p class="text-muted mb-0">User Details & Activity</p>
            </div>
        </div>
    </x-slot>

    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">User Information</h5>
                    <a href="{{ route('users.edit', $user) }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-pencil me-1"></i>Edit
                    </a>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                            <i class="bi bi-person text-white display-4"></i>
                        </div>
                        <h4>{{ $user->name }}</h4>
                        <p class="text-muted">{{ $user->email }}</p>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <h6 class="text-muted">Roles</h6>
                        @foreach($user->roles as $role)
                            <span class="badge bg-info me-1">{{ $role->name }}</span>
                        @endforeach
                    </div>

                    <div class="mb-3">
                        <h6 class="text-muted">Status</h6>
                        <span class="badge {{ $user->is_active ? 'bg-success' : 'bg-secondary' }}">
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>

                    <div class="mb-3">
                        <h6 class="text-muted">Email Verified</h6>
                        @if($user->email_verified_at)
                            <span class="badge bg-success">Verified</span>
                            <br><small class="text-muted">{{ $user->email_verified_at->format('M d, Y') }}</small>
                        @else
                            <span class="badge bg-warning">Not Verified</span>
                        @endif
                    </div>

                    <div class="mb-3">
                        <h6 class="text-muted">Created</h6>
                        <p class="mb-1">{{ $user->created_at->format('M d, Y H:i') }}</p>
                        <small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                    </div>

                    <div class="mb-3">
                        <h6 class="text-muted">Last Updated</h6>
                        <p class="mb-1">{{ $user->updated_at->format('M d, Y H:i') }}</p>
                        <small class="text-muted">{{ $user->updated_at->diffForHumans() }}</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <!-- Statistics -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Business Profiles</h6>
                                    <h3 class="mb-0">{{ $user->businessProfiles->count() }}</h3>
                                </div>
                                <div>
                                    <i class="bi bi-building display-4 opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Invoices Created</h6>
                                    <h3 class="mb-0">{{ $user->invoices->count() }}</h3>
                                </div>
                                <div>
                                    <i class="bi bi-file-earmark-text display-4 opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Total Revenue</h6>
                                    <h3 class="mb-0">₨{{ number_format($user->invoices->sum('total_amount'), 0) }}</h3>
                                </div>
                                <div>
                                    <i class="bi bi-currency-dollar display-4 opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Business Profiles -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Business Profiles</h5>
                </div>
                <div class="card-body">
                    @forelse($user->businessProfiles as $profile)
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                @if($profile->logo_path)
                                    <img src="{{ asset('storage/' . $profile->logo_path) }}" alt="Logo" class="rounded" style="width: 40px; height: 40px; object-fit: cover;">
                                @else
                                    <div class="bg-primary rounded d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="bi bi-building text-white"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0">{{ $profile->business_name }}</h6>
                                <small class="text-muted">{{ $profile->strn_ntn ?? 'No STRN/NTN' }}</small>
                            </div>
                            <div class="flex-shrink-0">
                                <span class="badge {{ $profile->is_sandbox ? 'bg-warning' : 'bg-success' }}">
                                    {{ $profile->is_sandbox ? 'Sandbox' : 'Production' }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center">No business profiles created</p>
                    @endforelse
                </div>
            </div>

            <!-- Recent Invoices -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recent Invoices</h5>
                </div>
                <div class="card-body">
                    @forelse($user->invoices->take(5) as $invoice)
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <i class="bi bi-file-earmark-text text-primary fs-4"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0">{{ $invoice->invoice_number }}</h6>
                                <small class="text-muted">{{ $invoice->customer->name }} • {{ $invoice->invoice_date->format('M d, Y') }}</small>
                            </div>
                            <div class="flex-shrink-0">
                                <span class="badge bg-{{ $invoice->fbr_status === 'submitted' ? 'success' : 'warning' }}">
                                    {{ ucfirst($invoice->fbr_status) }}
                                </span>
                                <div class="text-end">
                                    <small class="text-muted">₨{{ number_format($invoice->total_amount, 2) }}</small>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center">No invoices created</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>