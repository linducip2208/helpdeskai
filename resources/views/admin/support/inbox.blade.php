@extends('layouts.admin')
@section('title', __('Support inbox'))
@section('page-actions')
    <a href="{{ route('admin.tickets.create') }}" class="btn btn-primary">{{ __('New Ticket') }}</a>
@endsection
@section('content')
<div class="row g-3">
    <div class="col-12 col-lg-2">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ __('Queues') }}</h3>
            </div>
            <div class="list-group list-group-flush">
                @foreach($queues as $key => $queue)
                    <a href="{{ route('admin.support.inbox', array_merge(request()->only(['q','status','priority','department_id','sort']), ['queue' => $key])) }}"
                       class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ ($activeQueue ?? '') === $key ? 'active' : '' }}">
                        <span>{{ $queue['label'] }}</span>
                        <span class="badge bg-primary-lt">{{ $queue['count'] }}</span>
                    </a>
                @endforeach
            </div>
            @if(($savedViews ?? collect())->isNotEmpty())
                <div class="card-header border-top">
                    <h3 class="card-title">{{ __('Saved views') }}</h3>
                </div>
                <div class="list-group list-group-flush">
                    @foreach($savedViews as $view)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span>{{ $view->name }}</span>
                            @if($view->is_shared)
                                <span class="badge bg-green-lt">{{ __('Shared') }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="col-12 col-lg-6">
        <div class="card mb-3">
            <div class="card-body">
                <form action="{{ route('admin.support.inbox') }}" method="GET">
                    <input type="hidden" name="queue" value="{{ $activeQueue ?? 'open' }}">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="{{ __('Search') }}" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <select name="status" class="form-select">
                                <option value="">{{ __('All status') }}</option>
                                @foreach(['open','in_progress','waiting','answered','resolved','closed'] as $st)
                                    <option value="{{ $st }}" {{ ($filters['status'] ?? '') === $st ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$st)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="priority" class="form-select">
                                <option value="">{{ __('All priority') }}</option>
                                @foreach(['low','medium','high','urgent'] as $pr)
                                    <option value="{{ $pr }}" {{ ($filters['priority'] ?? '') === $pr ? 'selected' : '' }}>{{ ucfirst($pr) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="department_id" class="form-select">
                                <option value="">{{ __('All departments') }}</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ (string)($filters['department_id'] ?? '') === (string)$dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="sort" class="form-select">
                                @foreach(['latest' => __('Latest'),'oldest' => __('Oldest'),'priority' => __('Priority'),'sla' => __('SLA')] as $val => $label)
                                    <option value="{{ $val }}" {{ ($filters['sort'] ?? 'latest') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mt-2">
                        <button type="submit" class="btn btn-primary btn-sm">{{ __('Filter') }}</button>
                    </div>
                </form>
            </div>
        </div>

        <form id="bulk-form" action="{{ route('admin.tickets.bulk') }}" method="POST">
            @csrf
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="select-all" class="form-check-input"></th>
                                <th>{{ __('Ticket') }}</th>
                                <th>{{ __('Customer') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('SLA') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tickets as $ticket)
                                @php $st = (string) ($ticket->status?->value ?? $ticket->status); @endphp
                                <tr>
                                    <td><input type="checkbox" name="ids[]" value="{{ $ticket->id }}" class="form-check-input"></td>
                                    <td>
                                        <a href="{{ route('admin.support.inbox', array_merge(request()->only(['q','status','priority','department_id','sort','queue']), ['preview' => $ticket->id])) }}">{{ $ticket->uid }} — {{ Str::limit($ticket->subject, 50) }}</a>
                                        <div class="text-muted small">{{ $ticket->user->name ?? '' }} · {{ $ticket->department->name ?? '' }}</div>
                                    </td>
                                    <td class="text-muted">{{ $ticket->user->name ?? 'N/A' }}</td>
                                    <td><span class="badge bg-blue-lt">{{ ucfirst(str_replace('_',' ',$st)) }}</span></td>
                                    <td class="text-muted small">{{ $ticket->sla_remaining ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5"><div class="empty"><p class="empty-title">{{ __('No tickets found') }}</p></div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    <div class="row g-2 align-items-center">
                        <div class="col-auto">
                            <select name="action" class="form-select">
                                <option value="">{{ __('Bulk Actions') }}</option>
                                <option value="status_change">{{ __('Change status') }}</option>
                                <option value="assign">{{ __('Assign') }}</option>
                                <option value="delete">{{ __('Delete') }}</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <select name="status" class="form-select">
                                <option value="open">{{ __('Open') }}</option>
                                <option value="in_progress">{{ __('In progress') }}</option>
                                <option value="waiting">{{ __('Waiting') }}</option>
                                <option value="answered">{{ __('Answered') }}</option>
                                <option value="resolved">{{ __('Resolved') }}</option>
                                <option value="closed">{{ __('Closed') }}</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <select name="assigned_to" class="form-select">
                                <option value="">{{ __('Assignee') }}</option>
                                @foreach($agents as $agent)
                                    <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-sm">{{ __('Apply') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="col-12 col-lg-4">
        @if(!empty($previewTicket))
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ $previewTicket->uid }}</h3>
                    <div class="card-actions">
                        <a href="{{ route('admin.tickets.show', $previewTicket) }}" class="btn btn-sm btn-primary">{{ __('Open full ticket') }}</a>
                    </div>
                </div>
                <div class="card-body">
                    <h4>{{ $previewTicket->subject }}</h4>
                    <dl class="row">
                        <dt class="col-5">{{ __('Customer') }}</dt>
                        <dd class="col-7">{{ $previewTicket->user->name ?? '—' }}<div class="text-muted small">{{ $previewTicket->user->email ?? '' }}</div></dd>
                        <dt class="col-5">{{ __('Status') }}</dt>
                        <dd class="col-7"><span class="badge bg-blue-lt">{{ ucfirst(str_replace('_',' ',(string)($previewTicket->status?->value ?? $previewTicket->status))) }}</span></dd>
                        <dt class="col-5">{{ __('Priority') }}</dt>
                        <dd class="col-7"><span class="badge bg-yellow-lt">{{ ucfirst($previewTicket->priority ?? '') }}</span></dd>
                        <dt class="col-5">{{ __('SLA') }}</dt>
                        <dd class="col-7">{{ $previewTicket->sla_remaining ?? '—' }}</dd>
                        <dt class="col-5">{{ __('Assignee') }}</dt>
                        <dd class="col-7">{{ $previewTicket->assignedTo->name ?? __('Unassigned') }}</dd>
                        <dt class="col-5">{{ __('Department') }}</dt>
                        <dd class="col-7">{{ $previewTicket->department->name ?? '—' }}</dd>
                    </dl>
                    <a href="{{ route('admin.customers.show', ['customer' => $previewTicket->user_id]) }}">{{ __('View customer 360') }}</a>
                </div>
            </div>
        @else
            <div class="card">
                <div class="card-body">
                    <div class="empty">
                        <p class="empty-title">{{ __('No preview selected') }}</p>
                        <p class="empty-subtitle text-muted">{{ __('Select a ticket to see details here.') }}</p>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
    document.getElementById('select-all')?.addEventListener('change', function() {
        document.querySelectorAll('input[name="ids[]"]').forEach(cb => cb.checked = this.checked);
    });
</script>
@endsection
