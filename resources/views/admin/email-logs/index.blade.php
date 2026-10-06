@extends('layouts.admin')
@section('title', 'Email Logs')
@section('content')
<div class="mb-3">
    <h2 class="page-title">Email Logs</h2>
    <p class="text-muted">Inbound (email piping) &amp; outbound notification email. Klik baris untuk detail body + headers.</p>
</div>

<div class="row row-cards mb-3">
    <div class="col-4">
        <div class="card">
            <div class="card-body">
                <p class="text-muted mb-1 small">Inbound Received</p>
                <p class="h1 mb-0">{{ number_format($totals['received']) }}</p>
            </div>
        </div>
    </div>
    <div class="col-4">
        <div class="card">
            <div class="card-body">
                <p class="text-muted mb-1 small">Outbound Sent</p>
                <p class="h1 mb-0 text-primary">{{ number_format($totals['sent']) }}</p>
            </div>
        </div>
    </div>
    <div class="col-4">
        <div class="card">
            <div class="card-body">
                <p class="text-muted mb-1 small">Failed</p>
                <p class="h1 mb-0 text-danger">{{ number_format($totals['failed']) }}</p>
            </div>
        </div>
    </div>
</div>

<form method="GET" class="card mb-3">
    <div class="card-body">
        <div class="row g-2">
            <div class="col-md-3">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search subject/email..." class="form-control">
            </div>
            <div class="col-md-3">
                <select name="direction" class="form-select">
                    <option value="">All directions</option>
                    <option value="inbound" {{ request('direction') == 'inbound' ? 'selected' : '' }}>Inbound</option>
                    <option value="outbound" {{ request('direction') == 'outbound' ? 'selected' : '' }}>Outbound</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All status</option>
                    @foreach(['received', 'parsed', 'failed', 'sent', 'queued'] as $s)
                        <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <div class="btn-list">
                    <button class="btn btn-primary">Filter</button>
                    <a href="{{ route('admin.email-logs.index') }}" class="btn btn-ghost-secondary">Reset</a>
                </div>
            </div>
        </div>
    </div>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Direction</th>
                    <th>From → To</th>
                    <th>Subject</th>
                    <th>Ticket</th>
                    <th class="text-center">Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td class="text-muted small text-nowrap">{{ wib($log->created_at) }}</td>
                        <td><span class="badge bg-{{ $log->direction === 'inbound' ? 'blue' : 'green' }}-lt">{{ $log->direction }}</span></td>
                        <td class="small">
                            <div>{{ \Illuminate\Support\Str::limit($log->from_email, 30) }}</div>
                            <div class="text-muted">→ {{ \Illuminate\Support\Str::limit($log->to_email, 30) }}</div>
                        </td>
                        <td>{{ \Illuminate\Support\Str::limit($log->subject, 50) }}</td>
                        <td class="small">
                            @if($log->ticket)
                                <a href="{{ route('admin.tickets.show', $log->ticket) }}" class="font-monospace">{{ $log->ticket->uid }}</a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @php
                                $statusColor = ['received' => 'secondary', 'parsed' => 'green', 'failed' => 'red', 'sent' => 'green', 'queued' => 'yellow'][$log->status] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $statusColor }}-lt">{{ $log->status }}</span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.email-logs.show', $log) }}" class="btn btn-sm">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty">
                                <p class="empty-title">No email logs yet</p>
                                <p class="empty-subtitle text-muted">Email piping akan otomatis ngisi tabel ini saat ada email masuk.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
        <div class="card-footer d-flex align-items-center justify-content-center">{{ $logs->links() }}</div>
    @endif
</div>
@endsection
