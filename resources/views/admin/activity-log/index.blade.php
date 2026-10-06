@extends('layouts.admin')
@section('title', 'Activity Log')
@section('content')
<div class="card mb-3">
    <div class="card-body">
        <form method="GET">
            <div class="row g-2">
                <div class="col-md-6">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search activities..." class="form-control">
                </div>
                <div class="col-md-4">
                    <select name="user_id" class="form-select">
                        <option value="">All Users</option>
                        @foreach($users ?? [] as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="btn-list">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="{{ route('admin.activity-log.index') }}" class="btn btn-ghost-secondary">Reset</a>
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
                    <th>Action</th>
                    <th>Subject</th>
                    <th>IP Address</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs ?? [] as $activity)
                <tr>
                    <td>
                        <span class="avatar avatar-sm me-2">{{ substr($activity->user->name ?? 'S', 0, 1) }}</span>
                        <span>{{ $activity->user->name ?? 'System' }}</span>
                    </td>
                    <td>{{ $activity->action }}</td>
                    <td class="text-muted">{{ Str::limit($activity->target_label ?? '', 60) }}</td>
                    <td class="text-muted font-monospace">{{ $activity->ip_address ?? 'N/A' }}</td>
                    <td class="text-muted">{{ wib($activity->created_at) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5">
                        <div class="empty">
                            <p class="empty-title">No activity recorded yet</p>
                            <p class="empty-subtitle text-muted">Activity will appear here once actions are logged.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer d-flex align-items-center justify-content-center">
        @if(isset($logs) && method_exists($logs, 'links'))
            {{ $logs->links() }}
        @endif
    </div>
</div>
@endsection
