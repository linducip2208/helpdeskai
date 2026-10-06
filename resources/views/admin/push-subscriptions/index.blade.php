@extends('layouts.admin')
@section('title', 'Push Subscriptions')
@section('content')

<p class="text-muted mb-3">User yang sudah aktivasi web push notification.</p>

<div class="row row-cards mb-3">
    <div class="col-sm-6 col-lg-4">
        <div class="card">
            <div class="card-body">
                <div class="text-muted">Active Subscriptions</div>
                <div class="h2 mb-0">{{ number_format($totals['all']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-4">
        <div class="card">
            <div class="card-body">
                <div class="text-muted">Unique Subscribers</div>
                <div class="h2 mb-0 text-blue">{{ number_format($totals['unique_users']) }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title">Send Broadcast</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.push-subscriptions.broadcast') }}">
            @csrf
            <div class="row g-2">
                <div class="col-md-6">
                    <div class="mb-3">
                        <input type="text" name="title" placeholder="Notification title" maxlength="120" required class="form-control">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <input type="url" name="url" placeholder="Action URL (optional)" class="form-control">
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <textarea name="body" rows="2" placeholder="Notification body" maxlength="300" required class="form-control"></textarea>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-3">
                <label class="form-check">
                    <input type="radio" name="target" value="all" checked class="form-check-input">
                    <span class="form-check-label">Send to all subscribers</span>
                </label>
                <label class="form-check">
                    <input type="radio" name="target" value="user" class="form-check-input">
                    <span class="form-check-label">Specific user ID:</span>
                    <input type="number" name="user_id" class="form-control d-inline-block ms-1" style="width:6rem;">
                </label>
                <button class="btn btn-primary ms-auto">Queue Broadcast</button>
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
                    <th>Endpoint</th>
                    <th>Browser / Device</th>
                    <th>Subscribed</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subscriptions as $sub)
                    <tr>
                        <td>
                            <div>{{ $sub->user->name ?? 'Unknown' }}</div>
                            <div class="text-muted">{{ $sub->user->email ?? '—' }}</div>
                        </td>
                        <td class="text-muted"><code>{{ \Illuminate\Support\Str::limit($sub->endpoint, 60) }}</code></td>
                        <td class="text-muted" title="{{ $sub->user_agent }}">{{ \Illuminate\Support\Str::limit($sub->user_agent, 40) }}</td>
                        <td class="text-muted">{{ $sub->created_at?->diffForHumans() }}</td>
                        <td class="text-end">
                            <form method="POST" action="{{ route('admin.push-subscriptions.destroy', $sub) }}" onsubmit="return confirm('Remove this subscription?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger">Remove</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5"><div class="empty"><p class="empty-title">No active subscriptions.</p><p class="empty-subtitle text-muted">User perlu enable push di profile dulu.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($subscriptions->hasPages())
        <div class="card-footer d-flex align-items-center justify-content-center">{{ $subscriptions->links() }}</div>
    @endif
</div>

@endsection
