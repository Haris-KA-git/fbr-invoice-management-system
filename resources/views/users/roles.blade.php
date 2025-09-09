<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="h3 mb-0">Role Management</h2>
                <p class="text-muted mb-0">Manage system roles and permissions</p>
            </div>
            <div class="btn-group">
                <a href="{{ route('users.create-role') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Create Role
                </a>
                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Back to Users
                </a>
            </div>
        </div>
    </x-slot>

    <div class="row">
        @foreach($roles as $role)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ $role->name }}</h5>
                        <span class="badge bg-primary">{{ $role->users()->count() }} users</span>
                    </div>
                    <div class="card-body">
                        <h6 class="text-muted mb-3">Permissions ({{ $role->permissions->count() }})</h6>
                        
                        @php
                            $groupedPermissions = $role->permissions->groupBy(function($permission) {
                                return explode(' ', $permission->name)[1] ?? 'general';
                            });
                        @endphp

                        @foreach($groupedPermissions as $group => $perms)
                            <div class="mb-2">
                                <small class="text-muted fw-bold">{{ ucfirst($group) }}:</small>
                                <div>
                                    @foreach($perms->take(3) as $permission)
                                        <span class="badge bg-light text-dark me-1 mb-1" style="font-size: 0.7rem;">
                                            {{ str_replace($group, '', $permission->name) }}
                                        </span>
                                    @endforeach
                                    @if($perms->count() > 3)
                                        <span class="badge bg-secondary" style="font-size: 0.7rem;">
                                            +{{ $perms->count() - 3 }} more
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach

                        @if($role->permissions->isEmpty())
                            <p class="text-muted small">No permissions assigned</p>
                        @endif
                    </div>
                    <div class="card-footer">
                        <div class="btn-group w-100">
                            <a href="{{ route('users.edit-role', $role) }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-pencil me-1"></i>Edit
                            </a>
                            @if($role->users()->count() == 0)
                                <form method="POST" action="{{ route('users.destroy-role', $role) }}" style="display: inline-block;" class="flex-fill">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm w-100" 
                                            onclick="return confirm('Are you sure you want to delete this role?')">
                                        <i class="bi bi-trash me-1"></i>Delete
                                    </button>
                                </form>
                            @else
                                <button class="btn btn-outline-secondary btn-sm flex-fill" disabled title="Cannot delete role with assigned users">
                                    <i class="bi bi-lock me-1"></i>Protected
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if($roles->isEmpty())
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-shield-check display-1 text-muted mb-3"></i>
                <h4>No Roles Found</h4>
                <p class="text-muted mb-4">Create your first role to manage user permissions.</p>
                <a href="{{ route('users.create-role') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Create Role
                </a>
            </div>
        </div>
    @endif

    <!-- Permissions Overview -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">Available Permissions</h5>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($permissions as $group => $perms)
                    <div class="col-md-6 col-lg-4 mb-3">
                        <h6 class="text-primary">{{ ucfirst($group) }}</h6>
                        @foreach($perms as $permission)
                            <div class="small text-muted">
                                <i class="bi bi-check2 me-1"></i>{{ $permission->name }}
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>