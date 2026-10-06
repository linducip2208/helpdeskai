@extends('layouts.admin')
@section('title', 'User Detail')
@section('page-actions')
    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">Edit</a>
    <form action="{{ route('admin.users.impersonate', $user) }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-warning">Impersonate</button>
    </form>
@endsection
@section('content')
<div class="mb-3">
    <a href="{{ route('admin.users.index') }}">&larr; Back to Users</a>
    <h2 class="page-title mt-1">{{ $user->name }}</h2>
</div>

<div class="row row-cards mb-3">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Profile</h3>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-6 text-muted">Email</dt>
                    <dd class="col-6">{{ $user->email }}</dd>
                    <dt class="col-6 text-muted">Email Verified</dt>
                    <dd class="col-6">{{ $user->email_verified_at ? wib($user->email_verified_at, 'd F Y', false) : '—' }}</dd>
                    <dt class="col-6 text-muted">Status</dt>
                    <dd class="col-6"><span class="badge bg-{{ $user->is_active ? 'green' : 'secondary' }}-lt">{{ $user->is_active ? 'Active' : 'Inactive' }}</span></dd>
                    <dt class="col-6 text-muted">Joined</dt>
                    <dd class="col-6">{{ wib($user->created_at, 'd F Y', false) }}</dd>
                </dl>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Roles</h3>
            </div>
            <div class="card-body">
                @forelse($user->roles as $role)
                    <span class="badge bg-primary-lt">{{ ucfirst($role->name) }}</span>
                @empty
                    <div class="empty">
                        <p class="empty-title">No roles assigned</p>
                        <p class="empty-subtitle text-muted">Assign a role from the edit page.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Recent Tickets</h3>
    </div>
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>Status</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                @forelse($user->tickets as $ticket)
                    <tr>
                        <td><a href="{{ route('admin.tickets.show', $ticket) }}">{{ $ticket->subject }}</a></td>
                        <td class="text-muted">{{ ucfirst($ticket->status->value ?? $ticket->status) }}</td>
                        <td class="text-muted">{{ wib($ticket->created_at, 'd F Y', false) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">
                            <div class="empty">
                                <p class="empty-title">No tickets</p>
                                <p class="empty-subtitle text-muted">This user has no tickets yet.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
