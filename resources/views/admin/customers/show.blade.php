@extends('layouts.admin')
@section('title', __('Customer 360'))
@section('content')
<div class="row g-3">
    <div class="col-12 col-lg-4">
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">{{ __('Profile') }}</h3>
                @if($customer->vip)
                    <div class="card-actions"><span class="badge bg-yellow-lt">{{ __('VIP') }}</span></div>
                @endif
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-4">{{ __('Name') }}</dt>
                    <dd class="col-8">{{ $customer->name }}</dd>
                    <dt class="col-4">{{ __('Email') }}</dt>
                    <dd class="col-8">{{ $customer->email }}</dd>
                    <dt class="col-4">{{ __('Phone') }}</dt>
                    <dd class="col-8">{{ $customer->phone ?? '—' }}</dd>
                    <dt class="col-4">{{ __('Organization') }}</dt>
                    <dd class="col-8">{{ $organization->name ?? '—' }}</dd>
                </dl>
                @if($tags->isNotEmpty())
                    <div class="mt-2">
                        @foreach($tags as $tag)
                            <span class="badge bg-blue-lt me-1">{{ $tag }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><h3 class="card-title">{{ __('Stats') }}</h3></div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-6">{{ __('Total tickets') }}</dt><dd class="col-6">{{ $stats['total'] }}</dd>
                    <dt class="col-6">{{ __('Open') }}</dt><dd class="col-6">{{ $stats['open'] }}</dd>
                    <dt class="col-6">{{ __('Resolved') }}</dt><dd class="col-6">{{ $stats['resolved'] }}</dd>
                    <dt class="col-6">{{ __('Avg resolution') }}</dt><dd class="col-6">{{ $stats['avg_resolution_hours'] !== null ? $stats['avg_resolution_hours'].' h' : '—' }}</dd>
                    <dt class="col-6">{{ __('Avg first response') }}</dt><dd class="col-6">{{ $stats['avg_first_response_hours'] !== null ? $stats['avg_first_response_hours'].' h' : '—' }}</dd>
                    <dt class="col-6">{{ __('SLA compliance') }}</dt><dd class="col-6">{{ $stats['sla_compliance'] !== null ? $stats['sla_compliance'].'%' : '—' }}</dd>
                    <dt class="col-6">{{ __('CSAT avg') }}</dt><dd class="col-6">{{ $stats['csat_avg'] ?? '—' }}</dd>
                    <dt class="col-6">{{ __('Reopen rate') }}</dt><dd class="col-6">{{ $stats['reopen_rate'] }}% ({{ $stats['reopen_count'] }})</dd>
                </dl>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><h3 class="card-title">{{ __('Related tickets') }}</h3></div>
            <div class="list-group list-group-flush">
                @forelse($relatedTickets as $ticket)
                    <a href="{{ route('admin.tickets.show', $ticket) }}" class="list-group-item list-group-item-action">
                        <div>{{ $ticket->uid }} — {{ Str::limit($ticket->subject, 45) }}</div>
                        <div class="text-muted small">{{ ucfirst(str_replace('_',' ',(string)($ticket->status?->value ?? $ticket->status))) }} · {{ $ticket->created_at?->format('d M Y') }}</div>
                    </a>
                @empty
                    <div class="list-group-item text-muted">{{ __('No tickets yet.') }}</div>
                @endforelse
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><h3 class="card-title">{{ __('Attachments') }}</h3></div>
            <div class="list-group list-group-flush">
                @forelse($attachments as $attachment)
                    <div class="list-group-item">
                        <div>{{ $attachment->original_name }}</div>
                        <div class="text-muted small">{{ number_format($attachment->size / 1024, 1) }} KB · {{ $attachment->created_at?->format('d M Y') }}</div>
                    </div>
                @empty
                    <div class="list-group-item text-muted">{{ __('No attachments.') }}</div>
                @endforelse
            </div>
        </div>

        @can('manage_users')
        <div class="card mb-3">
            <div class="card-header"><h3 class="card-title">{{ __('Internal notes') }}</h3></div>
            <div class="card-body">
                <p class="text-muted small">{{ __('Only visible to staff. Never shown to the customer.') }}</p>
                <form action="{{ route('admin.customers.update', ['customer' => $customer->id]) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">{{ __('Notes') }}</label>
                        <textarea name="internal_notes" rows="3" class="form-control">{{ old('internal_notes', $customer->internal_notes) }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-check">
                            <input type="checkbox" name="vip" value="1" class="form-check-input" {{ old('vip', $customer->vip) ? 'checked' : '' }}>
                            <span class="form-check-label">{{ __('VIP customer') }}</span>
                        </label>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">{{ __('Save notes') }}</button>
                </form>
            </div>
        </div>
        @endcan
    </div>

    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('Timeline') }}</h3></div>
            <div class="card-body">
                @forelse($timeline as $item)
                    <div class="d-flex gap-3 mb-3">
                        <div class="text-muted small" style="min-width:9rem;">{{ $item['at']?->format('d M Y H:i') }}</div>
                        <div>
                            <span class="badge bg-blue-lt">{{ $item['label'] }}</span>
                            @if($item['by'])<span class="text-muted small"> · {{ $item['by'] }}</span>@endif
                            <div class="mt-1">{{ $item['detail'] }}</div>
                        </div>
                    </div>
                @empty
                    <div class="empty">
                        <p class="empty-title">{{ __('No activity yet') }}</p>
                    </div>
                @endforelse
                <div class="mt-3">{{ $timeline->links() }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
