<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="h3 mb-0">Business Profiles</h2>
                <p class="text-muted mb-0">Manage your business profiles for FBR integration</p>
            </div>
            <a href="{{ route('business-profiles.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Add Business Profile
            </a>
        </div>
    </x-slot>

    <div class="row">
        @forelse ($profiles as $profile)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex align-items-center">
                                @if($profile->logo_path)
                                    <img src="{{ asset('storage/' . $profile->logo_path) }}" alt="Logo" class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                @else
                                    <div class="bg-primary rounded d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                        <i class="bi bi-building text-white"></i>
                                    </div>
                                @endif
                                <div>
                                    <h5 class="mb-0">{{ $profile->business_name }}</h5>
                                    <small class="text-muted">{{ $profile->strn_ntn ?? 'No STRN/NTN' }}</small>
                                </div>
                            </div>
                            <span class="badge {{ $profile->is_sandbox ? 'bg-warning' : 'bg-success' }}">
                                {{ $profile->is_sandbox ? 'Sandbox' : 'Production' }}
                            </span>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted">Address:</small>
                            <p class="mb-1">{{ Str::limit($profile->address, 50) }}</p>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted">Contact:</small>
                            <p class="mb-0">
                                @if($profile->contact_email)
                                    <i class="bi bi-envelope me-1"></i>{{ $profile->contact_email }}<br>
                                @endif
                                @if($profile->contact_phone)
                                    <i class="bi bi-phone me-1"></i>{{ $profile->contact_phone }}
                                @endif
                            </p>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge {{ $profile->is_active ? 'bg-success' : 'bg-secondary' }}">
                                {{ $profile->is_active ? 'Active' : 'Inactive' }}
                            </span>
                            
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('business-profiles.show', $profile) }}" class="btn btn-outline-primary" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('business-profiles.edit', $profile) }}" class="btn btn-outline-secondary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('business-profiles.destroy', $profile) }}" style="display: inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this business profile?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-light">
                        <small class="text-muted">
                            <i class="bi bi-calendar me-1"></i>Created {{ $profile->created_at->diffForHumans() }}
                        </small>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-building display-1 text-muted mb-3"></i>
                        <h4>No Business Profiles</h4>
                        <p class="text-muted mb-4">You haven't created any business profiles yet. Create your first profile to start generating FBR compliant invoices.</p>
                        <a href="{{ route('business-profiles.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Create Business Profile
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    @if($profiles->hasPages())
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-center">
                    {{ $profiles->links() }}
                </div>
            </div>
        </div>
    @endif
</x-app-layout>