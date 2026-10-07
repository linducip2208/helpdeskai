@extends('layouts.admin')
@section('title', $incident->title)
@section('page-actions')
<a href="{{ route('admin.incidents.edit', $incident) }}" class="btn">{{ __('Edit') }}</a>
@endsection
@section('content')
<div class="row g-3">
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('Incident details') }}</h3></div>
            <div class="card-body">
                <div class="mb-2"><span class="badge bg-secondary">{{ ucfirst($incident->severity) }}</span> <span class="badge bg-blue-lt">{{ ucfirst($incident->status) }}</span></div>
                <p class="text-muted">{{ $incident->description }}</p>
                <dl class="row">
                    <dt class="col-4">{{ __('Owner') }}</dt><dd class="col-8">{{ $incident->owner?->name ?? '—' }}</dd>
                    <dt class="col-4">{{ __('Root cause') }}</dt><dd class="col-8">{{ $incident->root_cause ?? '—' }}</dd>
                    <dt class="col-4">{{ __('Resolution') }}</dt><dd class="col-8">{{ $incident->resolution ?? '—' }}</dd>
                    <dt class="col-4">{{ __('Started at') }}</dt><dd class="col-8">{{ $incident->started_at ?? '—' }}</dd>
                    <dt class="col-4">{{ __('Resolved at') }}</dt><dd class="col-8">{{ $incident->resolved_at ?? '—' }}</dd>
                </dl>
                <form action="{{ route('admin.incidents.transition', $incident) }}" method="POST" class="d-flex gap-2 align-items-end">
                    @csrf @method('PATCH')
                    <div>
                        <label for="status" class="form-label">{{ __('Change status') }}</label>
                        <select name="status" id="status" class="form-select">
                            @foreach(['identified','investigating','mitigated','resolved','closed'] as $s)
                            <option value="{{ $s }}" {{ $incident->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                            @endforeach
                            <option value="investigating_reopen">{{ __('Reopen as investigating') }}</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">{{ __('Update status') }}</button>
                </form>
            </div>
        </div>
        <div class="card mt-3">
            <div class="card-header"><h3 class="card-title">{{ __('Linked tickets') }}</h3></div>
            <div class="card-body">
                <form action="{{ route('admin.incidents.tickets.attach', $incident) }}" method="POST" class="d-flex gap-2 mb-3">
                    @csrf
                    <input type="number" name="ticket_id" class="form-control" placeholder="{{ __('Ticket ID') }}" required>
                    <button type="submit" class="btn btn-primary">{{ __('Link ticket') }}</button>
                </form>
                <ul class="list-group">
                    @forelse($incident->tickets as $ticket)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <a href="{{ route('admin.tickets.show', $ticket) }}">#{{ $ticket->id }} — {{ $ticket->subject }}</a>
                        <form action="{{ route('admin.incidents.tickets.detach', [$incident, $ticket]) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">{{ __('Unlink') }}</button>
                        </form>
                    </li>
                    @empty
                    <li class="list-group-item text-muted">{{ __('No tickets linked.') }}</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('Activity timeline') }}</h3></div>
            <div class="card-body">
                <ul class="timeline">
                    @forelse($logs as $log)
                    <li>
                        <div class="fw-bold">{{ $log->action }}</div>
                        <div class="text-muted small">{{ $log->user?->name ?? __('System') }} — {{ $log->created_at }}</div>
                    </li>
                    @empty
                    <li class="text-muted">{{ __('No activity yet.') }}</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
