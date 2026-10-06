@extends('layouts.admin')
@section('title', 'Users')
@section('page-actions')
    <button type="button" onclick="document.getElementById('create-modal').style.display='block'" class="btn btn-primary">
        Add User
    </button>
@endsection
@section('content')
<div class="card mb-3">
    <div class="card-body">
        <form action="{{ route('admin.users.index') }}" method="GET">
            <div class="row g-2">
                <div class="col-md-6">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search users..." class="form-control">
                </div>
                <div class="col-md-3">
                    <select name="role" class="form-select">
                        <option value="">All Roles</option>
                        <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="agent" {{ request('role') === 'agent' ? 'selected' : '' }}>Agent</option>
                        <option value="user" {{ request('role') === 'user' ? 'selected' : '' }}>User</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="btn-list">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-ghost-secondary">Reset</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Role</th>
                    <th>Email</th>
                    <th>Tickets</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users ?? [] as $user)
                <tr>
                    <td>
                        <span class="avatar avatar-sm me-2">{{ substr($user->name, 0, 1) }}</span>
                        <strong>{{ $user->name }}</strong>
                    </td>
                    <td class="text-muted">{{ ucfirst($user->role ?? 'user') }}</td>
                    <td class="text-muted">{{ $user->email }}</td>
                    <td class="text-muted">{{ $user->tickets_count ?? 0 }}</td>
                    <td>
                        <span class="badge bg-{{ ($user->is_active ?? true) ? 'green' : 'secondary' }}-lt">
                            {{ ($user->is_active ?? true) ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="text-muted">{{ wib($user->created_at, 'd F Y', false) }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm">View</a>
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm">Edit</a>
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="empty">
                            <p class="empty-title">No users found</p>
                            <p class="empty-subtitle text-muted">Try a different search or add a new user.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer d-flex align-items-center justify-content-center">
        {{ ($users ?? collect())->links() }}
    </div>
</div>

<div class="modal" id="create-modal" tabindex="-1" style="display: none;">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add User</h5>
                <button type="button" class="btn-close" onclick="document.getElementById('create-modal').style.display='none'"></button>
            </div>
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select">
                            <option value="user">User</option>
                            <option value="agent">Agent</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" onclick="document.getElementById('create-modal').style.display='none'" class="btn me-auto">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
