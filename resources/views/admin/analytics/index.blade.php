@extends('layouts.admin')
@section('title', 'Analytics')
@section('page-actions')
    <a href="{{ route('admin.export.tickets', request()->only(['from','to','status','priority'])) }}" class="btn">
        Export Tickets CSV
    </a>
    <a href="{{ route('admin.export.agents') }}" class="btn">
        Export Agents CSV
    </a>
@endsection
@section('content')
<div class="card mb-3">
    <div class="card-body">
        <form method="GET">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">From</label>
                    <input type="date" name="from" value="{{ request('from') }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">To</label>
                    <input type="date" name="to" value="{{ request('to') }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary">Apply</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row row-cards mb-3">
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <p class="text-muted mb-1">Total Tickets</p>
                <p class="h1 mb-0">{{ $analytics->total_tickets ?? 0 }}</p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <p class="text-muted mb-1">Resolved</p>
                <p class="h1 mb-0 text-green">{{ $analytics->resolved ?? 0 }}</p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <p class="text-muted mb-1">Avg First Response</p>
                <p class="h1 mb-0">{{ $analytics->avg_first_response ?? 'N/A' }}</p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <p class="text-muted mb-1">CSAT Score</p>
                <p class="h1 mb-0 text-yellow">{{ $analytics->csat_score ?? 'N/A' }}</p>
            </div>
        </div>
    </div>
</div>

<div class="row row-cards mb-3">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tickets by Status</h3>
            </div>
            <div class="card-body">
                @php $statuses = ['open' => 35, 'pending' => 20, 'resolved' => 30, 'closed' => 15]; @endphp
                @foreach($statuses as $status => $percent)
                <div class="mb-3">
                    <div class="row align-items-center mb-1">
                        <div class="col">{{ ucfirst($status) }}</div>
                        <div class="col-auto text-muted">{{ $percent }}%</div>
                    </div>
                    <div class="progress">
                        <div class="progress-bar {{ $status === 'open' ? 'bg-green' : ($status === 'pending' ? 'bg-yellow' : ($status === 'resolved' ? 'bg-blue' : 'bg-secondary')) }}" style="width: {{ $percent }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tickets by Priority</h3>
            </div>
            <div class="card-body">
                @php $priorities = ['urgent' => 10, 'high' => 25, 'medium' => 45, 'low' => 20]; @endphp
                @foreach($priorities as $priority => $percent)
                <div class="mb-3">
                    <div class="row align-items-center mb-1">
                        <div class="col">{{ ucfirst($priority) }}</div>
                        <div class="col-auto text-muted">{{ $percent }}%</div>
                    </div>
                    <div class="progress">
                        <div class="progress-bar {{ $priority === 'urgent' ? 'bg-red' : ($priority === 'high' ? 'bg-orange' : ($priority === 'medium' ? 'bg-yellow' : 'bg-green')) }}" style="width: {{ $percent }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Agent Performance</h3>
    </div>
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead>
                <tr>
                    <th>Agent</th>
                    <th>Resolved</th>
                    <th>Avg Response</th>
                    <th>CSAT</th>
                </tr>
            </thead>
            <tbody>
                @forelse($agentPerformance ?? [] as $perf)
                <tr>
                    <td><strong>{{ $perf->name }}</strong></td>
                    <td class="text-muted">{{ $perf->resolved_count }}</td>
                    <td class="text-muted">{{ $perf->avg_response }}</td>
                    <td class="text-muted">{{ $perf->csat }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4">
                        <div class="empty">
                            <p class="empty-title">No data available</p>
                            <p class="empty-subtitle text-muted">No agent performance data for this period.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
