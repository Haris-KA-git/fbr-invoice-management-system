<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center">
            <a href="{{ route('users.roles') }}" class="btn btn-outline-secondary me-3">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h2 class="h3 mb-0">Edit Role</h2>
                <p class="text-muted mb-0">Update {{ $role->name }} permissions</p>
            </div>
        </div>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('users.update-role', $role) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label for="name" class="form-label">Role Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $role->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Enter a descriptive name for this role (e.g., Manager, Supervisor)</div>
                        </div>

                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <label class="form-label mb-0">Permissions <span class="text-danger">*</span></label>
                                <div class="text-muted small">
                                    <span id="selected-count">{{ count($rolePermissions) }}</span> of {{ $permissions->flatten()->count() }} selected
                                </div>
                            </div>
                            @error('permissions')
                                <div class="text-danger small mb-2">{{ $message }}</div>
                            @enderror
                            
                            <div class="row">
                                @foreach($permissions as $group => $perms)
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card">
                                            <div class="card-header py-2">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h6 class="mb-0">{{ ucfirst($group) }}</h6>
                                                    <div class="form-check">
                                                        <input class="form-check-input group-toggle" type="checkbox" 
                                                               id="toggle_{{ $group }}" data-group="{{ $group }}">
                                                        <label class="form-check-label small" for="toggle_{{ $group }}">
                                                            All
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-body py-2">
                                                @foreach($perms as $permission)
                                                    <div class="form-check mb-1">
                                                        <input class="form-check-input permission-check @error('permissions') is-invalid @enderror" 
                                                               type="checkbox" value="{{ $permission->name }}" 
                                                               id="perm_{{ $permission->id }}" name="permissions[]"
                                                               data-group="{{ $group }}"
                                                               {{ in_array($permission->name, old('permissions', $rolePermissions)) ? 'checked' : '' }}>
                                                        <label class="form-check-label small" for="perm_{{ $permission->id }}">
                                                            {{ str_replace($group . ' ', '', $permission->name) }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Note:</strong> This role is currently assigned to {{ $role->users()->count() }} user(s). 
                            Changes will affect all users with this role immediately.
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('users.roles') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check2 me-2"></i>Update Role
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Group toggle functionality
        document.querySelectorAll('.group-toggle').forEach(toggle => {
            toggle.addEventListener('change', function() {
                const group = this.dataset.group;
                const checkboxes = document.querySelectorAll(`input[data-group="${group}"].permission-check`);
                
                checkboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                
                updateSelectedCount();
            });
        });

        // Update group toggle when individual permissions change
        document.querySelectorAll('.permission-check').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const group = this.dataset.group;
                const groupCheckboxes = document.querySelectorAll(`input[data-group="${group}"].permission-check`);
                const groupToggle = document.querySelector(`#toggle_${group}`);
                
                const checkedCount = Array.from(groupCheckboxes).filter(cb => cb.checked).length;
                const totalCount = groupCheckboxes.length;
                
                if (checkedCount === 0) {
                    groupToggle.checked = false;
                    groupToggle.indeterminate = false;
                } else if (checkedCount === totalCount) {
                    groupToggle.checked = true;
                    groupToggle.indeterminate = false;
                } else {
                    groupToggle.checked = false;
                    groupToggle.indeterminate = true;
                }
                
                updateSelectedCount();
            });
        });

        // Update selected count
        function updateSelectedCount() {
            const checkedPermissions = document.querySelectorAll('.permission-check:checked').length;
            document.getElementById('selected-count').textContent = checkedPermissions;
        }

        // Initialize group toggles on page load
        document.querySelectorAll('.group-toggle').forEach(toggle => {
            const group = toggle.dataset.group;
            const groupCheckboxes = document.querySelectorAll(`input[data-group="${group}"].permission-check`);
            const checkedCount = Array.from(groupCheckboxes).filter(cb => cb.checked).length;
            const totalCount = groupCheckboxes.length;
            
            if (checkedCount === 0) {
                toggle.checked = false;
                toggle.indeterminate = false;
            } else if (checkedCount === totalCount) {
                toggle.checked = true;
                toggle.indeterminate = false;
            } else {
                toggle.checked = false;
                toggle.indeterminate = true;
            }
        });
    </script>
    @endpush
</x-app-layout>