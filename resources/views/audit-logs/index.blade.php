<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="h3 mb-0">Audit Logs</h2>
            <p class="text-muted mb-0">System activity and change tracking</p>
        </div>
    </x-slot>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">User</label>
                    <select name="user_id" class="form-select">
                        <option value="">All Users</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Action</label>
                    <input type="text" name="action" class="form-control" placeholder="Search action..." value="{{ request('action') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Model Type</label>
                    <select name="model_type" class="form-select">
                        <option value="">All Types</option>
                        @foreach($modelTypes as $type)
                            <option value="{{ $type }}" {{ request('model_type') == $type ? 'selected' : '' }}>
                                {{ class_basename($type) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">From Date</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">To Date</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Audit Logs Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date/Time</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Model</th>
                            <th>Changes</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($auditLogs as $log)
                            <tr>
                                <td>
                                    <div>{{ $log->created_at->format('M d, Y') }}</div>
                                    <small class="text-muted">{{ $log->created_at->format('H:i:s') }}</small>
                                </td>
                                <td>
                                    @if($log->user)
                                        <div>{{ $log->user->name }}</div>
                                        <small class="text-muted">{{ $log->user->email }}</small>
                                    @else
                                        <span class="text-muted">System</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ $log->action }}</span>
                                </td>
                                <td>
                                    <div>{{ class_basename($log->model_type) }}</div>
                                    <small class="text-muted">ID: {{ $log->model_id }}</small>
                                </td>
                                <td>
                                    @if($log->old_values || $log->new_values)
                                        <button class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#changesModal{{ $log->id }}">
                                            <i class="bi bi-eye"></i> View Changes
                                        </button>
                                    @else
                                        <span class="text-muted">No changes</span>
                                    @endif
                                </td>
                                <td>{{ $log->ip_address }}</td>
                            </tr>

                            <!-- Changes Modal -->
                            @if($log->old_values || $log->new_values)
                                <div class="modal fade" id="changesModal{{ $log->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Changes Details</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    @if($log->old_values)
                                                        <div class="col-md-6">
                                                            <h6 class="text-danger">Old Values</h6>
                                                            <pre class="bg-light p-3 rounded"><code>{{ json_encode($log->old_values, JSON_PRETTY_PRINT) }}</code></pre>
                                                        </div>
                                                    @endif
                                                    @if($log->new_values)
                                                        <div class="col-md-6">
                                                            <h6 class="text-success">New Values</h6>
                                                            <pre class="bg-light p-3 rounded"><code>{{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}</code></pre>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="bi bi-file-text display-1 text-muted mb-3"></i>
                                    <h5>No audit logs found</h5>
                                    <p class="text-muted">Try adjusting your filters</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($auditLogs->hasPages())
                <div class="d-flex justify-content-center mt-3">
                    {{ $auditLogs->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>