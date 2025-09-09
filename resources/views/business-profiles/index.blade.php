<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="h3 mb-0">Business Profiles</h2>
                <p class="text-muted mb-0">
                    @if(auth()->user()->hasRole('Admin'))
                        Manage all business entities in the system
                    @else
                        Manage your business entities and settings
                    @endif
                </p>
            </div>
            <div>
                @if(auth()->user()->hasRole('Admin') || auth()->user()->canCreateBusinessProfile())
                    <a href="{{ route('business-profiles.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>Add Business Profile
                    </a>
                @else
                    <button class="btn btn-secondary" disabled title="Business profile limit reached">
                        <i class="bi bi-plus-circle me-2"></i>Add Business Profile
                    </button>
                @endif
            </div>
        </div>
    </x-slot>

    <!-- Profile Limit Alert (Only for non-admins) -->
    @if(!auth()->user()->hasRole('Admin'))
        @if(!auth()->user()->canCreateBusinessProfile())
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Profile Limit Reached:</strong> You have used all {{ auth()->user()->business_profile_limit }} of your allowed business profiles. 
                Contact an administrator to increase your limit.
            </div>
        @elseif(auth()->user()->getRemainingBusinessProfiles() <= 1)
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Profile Limit Notice:</strong> You have {{ auth()->user()->getRemainingBusinessProfiles() }} business profile(s) remaining out of {{ auth()->user()->business_profile_limit }}.
            </div>
        @endif
    @endif

    <!-- Business Profiles -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                @if(auth()->user()->hasRole('Admin'))
                    All Business Profiles ({{ $ownedProfiles->count() }})
                @else
                    My Business Profiles ({{ $ownedProfiles->count() }})
                @endif
            </h5>
            @if(!auth()->user()->hasRole('Admin'))
                <span class="badge bg-primary">{{ $ownedProfiles->count() }}/{{ auth()->user()->business_profile_limit }} used</span>
            @endif
        </div>
        <div class="card-body">
            <div class="row">
                @forelse($ownedProfiles as $profile)
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    @if($profile->logo_path)
                                        <img src="{{ asset('storage/' . $profile->logo_path) }}" 
                                             alt="Logo" class="rounded me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                    @else
                                        <div class="bg-primary rounded d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                            <i class="bi bi-building text-white"></i>
                                        </div>
                                    @endif
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0">{{ $profile->business_name }}</h6>
                                        <small class="text-muted">
                                            @if(auth()->user()->hasRole('Admin'))
                                                Owner: {{ $profile->user->name }}
                                            @else
                                                Owner
                                            @endif
                                        </small>
                                    </div>
                                </div>

                                <div class="row text-center mb-3">
                                    <div class="col-4">
                                        <div class="text-primary fw-bold">{{ $profile->customers()->count() }}</div>
                                        <small class="text-muted">Customers</small>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-info fw-bold">{{ $profile->items()->count() }}</div>
                                        <small class="text-muted">Items</small>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-success fw-bold">{{ $profile->invoices()->count() }}</div>
                                        <small class="text-muted">Invoices</small>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge {{ $profile->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $profile->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                    <span class="badge {{ $profile->is_sandbox ? 'bg-warning' : 'bg-info' }}">
                                        {{ $profile->is_sandbox ? 'Sandbox' : 'Production' }}
                                    </span>
                                </div>

                                <div class="d-grid gap-2 mt-3">
                                    <a href="{{ route('business-profiles.show', $profile) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-eye me-1"></i>View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="text-center py-4">
                            <i class="bi bi-building display-1 text-muted mb-3"></i>
                            <h4>No business profiles found</h4>
                            <p class="text-muted">
                                @if(auth()->user()->hasRole('Admin'))
                                    No business profiles exist in the system yet
                                @else
                                    Create your first business profile to get started
                                @endif
                            </p>
                            @if(auth()->user()->hasRole('Admin') || auth()->user()->canCreateBusinessProfile())
                                <a href="{{ route('business-profiles.create') }}" class="btn btn-primary">
                                    <i class="bi bi-plus-circle me-2"></i>Create Business Profile
                                </a>
                            @endif
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Shared Business Profiles (Only for non-admins) -->
    @if(!auth()->user()->hasRole('Admin') && $sharedProfiles->isNotEmpty())
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Shared Business Profiles ({{ $sharedProfiles->count() }})</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($sharedProfiles as $profile)
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card h-100 border-0 shadow-sm border-start border-info border-3">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        @if($profile->logo_path)
                                            <img src="{{ asset('storage/' . $profile->logo_path) }}" 
                                                 alt="Logo" class="rounded me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                        @else
                                            <div class="bg-info rounded d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                                <i class="bi bi-building text-white"></i>
                                            </div>
                                        @endif
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0">{{ $profile->business_name }}</h6>
                                            <small class="text-muted">{{ ucfirst($profile->pivot->role) }}</small>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <small class="text-muted">Owner: {{ $profile->user->name }}</small><br>
                                        <small class="text-muted">Added: {{ $profile->pivot->created_at->format('M d, Y') }}</small>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="badge bg-{{ $profile->pivot->role === 'manager' ? 'primary' : ($profile->pivot->role === 'editor' ? 'info' : 'secondary') }}">
                                            {{ ucfirst($profile->pivot->role) }}
                                        </span>
                                        <span class="badge {{ $profile->pivot->is_active ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $profile->pivot->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>

                                    <div class="d-grid gap-2">
                                        <a href="{{ route('business-profiles.show', $profile) }}" class="btn btn-outline-info btn-sm">
                                            <i class="bi bi-eye me-1"></i>View Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</x-app-layout>