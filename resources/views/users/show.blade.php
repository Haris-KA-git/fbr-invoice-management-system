<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center">
            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary me-3">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h2 class="h3 mb-0">{{ $user->name }}</h2>
                <p class="text-muted mb-0">User Details & Business Profile Access</p>
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
                        @if($user->roles->isNotEmpty())
                            <span class="badge bg-primary">{{ $user->roles->first()->name }}</span>
                        @endif
                    </div>

                    <hr>

                    <div class="mb-3">
                        <h6 class="text-muted">Email</h6>
                        <p class="mb-1">{{ $user->email }}</p>
                    </div>

                    <div class="mb-3">
                        <h6 class="text-muted">Business Profile Limit</h6>
                        <div class="d-flex align-items-center">
                            <div class="progress me-3 flex-grow-1" style="height: 10px;">
                                @php
                                    $ownedCount = $user->businessProfiles()->count();
                                    $percentage = ($ownedCount / $user->business_profile_limit) * 100;
                                @endphp
                                <div class="progress-bar {{ $percentage >= 100 ? 'bg-danger' : ($percentage >= 80 ? 'bg-warning' : 'bg-success') }}" 
                                     style="width: {{ min(100, $percentage) }}%"></div>
                            </div>
                            <span class="badge bg-info">{{ $ownedCount }}/{{ $user->business_profile_limit }}</span>
                        </div>
                        <small class="text-muted">{{ $user->getRemainingBusinessProfiles() }} profiles remaining</small>
                    </div>

                    <div class="mb-3">
                        <h6 class="text-muted">Status</h6>
                        <span class="badge {{ $user->is_active ? 'bg-success' : 'bg-secondary' }}">
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>

                    <div class="mb-3">
                        <h6 class="text-muted">Member Since</h6>
                        <p class="mb-1">{{ $user->created_at->format('M d, Y') }}</p>
                        <small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <!-- Owned Business Profiles -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Owned Business Profiles ({{ $user->businessProfiles()->count() }})</h5>
                </div>
                <div class="card-body">
                    @forelse($user->businessProfiles as $profile)
                        <div class="d-flex align-items-center justify-content-between p-3 border rounded mb-2">
                            <div class="d-flex align-items-center">
                                <div class="bg-success rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    <i class="bi bi-building text-white"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $profile->business_name }}</h6>
                                    <small class="text-muted">
                                        {{ $profile->customers()->count() }} customers • 
                                        {{ $profile->items()->count() }} items • 
                                        {{ $profile->invoices()->count() }} invoices
                                    </small>
                                </div>
                            </div>
                            <div>
                                <span class="badge bg-success me-2">Owner</span>
                                <span class="badge {{ $profile->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $profile->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <i class="bi bi-building display-1 text-muted mb-3"></i>
                            <h5>No owned business profiles</h5>
                            <p class="text-muted">This user hasn't created any business profiles yet</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Shared Business Profiles -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Shared Business Profiles ({{ $user->accessibleBusinessProfiles()->count() }})</h5>
                </div>
                <div class="card-body">
                    @forelse($user->accessibleBusinessProfiles as $profile)
                        <div class="d-flex align-items-center justify-content-between p-3 border rounded mb-2">
                            <div class="d-flex align-items-center">
                                <div class="bg-info rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    <i class="bi bi-building text-white"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $profile->business_name }}</h6>
                                    <small class="text-muted">
                                        Owner: {{ $profile->user->name }} • 
                                        Added: {{ $profile->pivot->created_at->format('M d, Y') }}
                                    </small>
                                </div>
                            </div>
                            <div>
                                <span class="badge bg-{{ $profile->pivot->role === 'manager' ? 'primary' : ($profile->pivot->role === 'editor' ? 'info' : 'secondary') }}">
                                    {{ ucfirst($profile->pivot->role) }}
                                </span>
                                <span class="badge {{ $profile->pivot->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $profile->pivot->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <i class="bi bi-share display-1 text-muted mb-3"></i>
                            <h5>No shared business profiles</h5>
                            <p class="text-muted">This user doesn't have access to any shared business profiles</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>