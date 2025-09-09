<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center">
            <a href="{{ route('business-profiles.show', $businessProfile) }}" class="btn btn-outline-secondary me-3">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h2 class="h3 mb-0">Manage Users</h2>
                <p class="text-muted mb-0">{{ $businessProfile->business_name }} - User Access Control</p>
            </div>
        </div>
    </x-slot>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Current Users</h5>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="bi bi-plus-circle me-1"></i>Add User
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Role</th>
                                    <th>Permissions</th>
                                    <th>Status</th>
                                    <th>Added</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($businessProfile->users as $user)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                                    <i class="bi bi-person text-white"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ $user->name }}</h6>
                                                    <small class="text-muted">{{ $user->email }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $user->pivot->role === 'owner' ? 'success' : ($user->pivot->role === 'manager' ? 'primary' : 'info') }}">
                                                {{ ucfirst($user->pivot->role) }}
                                            </span>
                                        </td>
                                        <td>
                                            @php
                                                $permissions = $user->pivot->permissions ? json_decode($user->pivot->permissions, true) : [];
                                            @endphp
                                            @if($user->pivot->role === 'owner')
                                                <span class="badge bg-success">All Permissions</span>
                                            @elseif(count($permissions) > 0)
                                                <span class="badge bg-info">{{ count($permissions) }} permissions</span>
                                            @else
                                                <span class="text-muted">No permissions</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $user->pivot->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $user->pivot->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td>{{ $user->pivot->created_at->format('M d, Y') }}</td>
                                        <td>
                                            @if($user->pivot->role !== 'owner' || $user->id !== $businessProfile->user_id)
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-outline-primary" 
                                                            data-bs-toggle="modal" data-bs-target="#editUserModal{{ $user->id }}">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <form method="POST" action="{{ route('business-profiles.remove-user', [$businessProfile, $user]) }}" style="display: inline-block;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger" 
                                                                onclick="return confirm('Remove this user from the business profile?')">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            @else
                                                <span class="badge bg-warning">Owner</span>
                                            @endif
                                        </td>
                                    </tr>

                                    <!-- Edit User Modal -->
                                    <div class="modal fade" id="editUserModal{{ $user->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit User Access</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST" action="{{ route('business-profiles.update-user', [$businessProfile, $user]) }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label">Role</label>
                                                            <select name="role" class="form-select" required>
                                                                <option value="viewer" {{ $user->pivot->role === 'viewer' ? 'selected' : '' }}>Viewer</option>
                                                                <option value="editor" {{ $user->pivot->role === 'editor' ? 'selected' : '' }}>Editor</option>
                                                                <option value="manager" {{ $user->pivot->role === 'manager' ? 'selected' : '' }}>Manager</option>
                                                                @if($user->id === $businessProfile->user_id)
                                                                    <option value="owner" {{ $user->pivot->role === 'owner' ? 'selected' : '' }}>Owner</option>
                                                                @endif
                                                            </select>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label">Permissions</label>
                                                            @php
                                                                $userPermissions = $user->pivot->permissions ? json_decode($user->pivot->permissions, true) : [];
                                                                $availablePermissions = [
                                                                    'view_invoices' => 'View Invoices',
                                                                    'create_invoices' => 'Create Invoices',
                                                                    'edit_invoices' => 'Edit Invoices',
                                                                    'delete_invoices' => 'Delete Invoices',
                                                                    'view_customers' => 'View Customers',
                                                                    'create_customers' => 'Create Customers',
                                                                    'edit_customers' => 'Edit Customers',
                                                                    'view_items' => 'View Items',
                                                                    'create_items' => 'Create Items',
                                                                    'edit_items' => 'Edit Items',
                                                                    'view_reports' => 'View Reports',
                                                                ];
                                                            @endphp
                                                            
                                                            <div class="row">
                                                                @foreach($availablePermissions as $key => $label)
                                                                    <div class="col-6 mb-2">
                                                                        <div class="form-check">
                                                                            <input class="form-check-input" type="checkbox" 
                                                                                   name="permissions[]" value="{{ $key }}" 
                                                                                   id="perm_{{ $user->id }}_{{ $key }}"
                                                                                   {{ in_array($key, $userPermissions) ? 'checked' : '' }}>
                                                                            <label class="form-check-label small" for="perm_{{ $user->id }}_{{ $key }}">
                                                                                {{ $label }}
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>

                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="is_active" value="1" 
                                                                   id="active_{{ $user->id }}" {{ $user->pivot->is_active ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="active_{{ $user->id }}">
                                                                Active Access
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary">Update Access</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <i class="bi bi-people display-1 text-muted mb-3"></i>
                                            <h5>No additional users</h5>
                                            <p class="text-muted">Add users to collaborate on this business profile</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Role Descriptions</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-success">Owner</h6>
                        <small class="text-muted">Full control over the business profile including user management and all operations.</small>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-primary">Manager</h6>
                        <small class="text-muted">Can manage invoices, customers, and items. Cannot manage users or delete the profile.</small>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-info">Editor</h6>
                        <small class="text-muted">Can create and edit invoices, customers, and items based on assigned permissions.</small>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-secondary">Viewer</h6>
                        <small class="text-muted">Read-only access to view invoices, customers, and reports based on permissions.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add User to Business Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('business-profiles.add-user', $businessProfile) }}">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">User</label>
                            <select name="user_id" class="form-select" required>
                                <option value="">Select User</option>
                                @foreach($availableUsers as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-select" required>
                                <option value="viewer">Viewer</option>
                                <option value="editor">Editor</option>
                                <option value="manager">Manager</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Permissions</label>
                            @php
                                $availablePermissions = [
                                    'view_invoices' => 'View Invoices',
                                    'create_invoices' => 'Create Invoices',
                                    'edit_invoices' => 'Edit Invoices',
                                    'delete_invoices' => 'Delete Invoices',
                                    'view_customers' => 'View Customers',
                                    'create_customers' => 'Create Customers',
                                    'edit_customers' => 'Edit Customers',
                                    'view_items' => 'View Items',
                                    'create_items' => 'Create Items',
                                    'edit_items' => 'Edit Items',
                                    'view_reports' => 'View Reports',
                                ];
                            @endphp
                            
                            <div class="row">
                                @foreach($availablePermissions as $key => $label)
                                    <div class="col-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" 
                                                   name="permissions[]" value="{{ $key }}" 
                                                   id="new_perm_{{ $key }}">
                                            <label class="form-check-label small" for="new_perm_{{ $key }}">
                                                {{ $label }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>