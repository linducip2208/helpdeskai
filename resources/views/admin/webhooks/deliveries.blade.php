@extends('layouts.admin')
@section('title', __('Webhook Deliveries'))
@section('content')

<div class="card mb-3">
    <div class="card-body">
        <form method="GET">
            <div class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label">{{ __('Endpoint') }}</label>
                    <select name="endpoint_id" class="form-select">
                        <option value="">{{ __('All endpoints') }}</option>
                        @foreach($endpoints ?? [] as $endpoint)
                        <option value="{{ $endpoint->id }}" {{ ($filters['endpoint_id'] ?? '') == $endpoint->id ? 'selected' : '' }}>{{ $endpoint->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">{{ __('Status') }}</label>
                    <select name="status" class="form-select">
                        <option value="">{{ __('All statuses') }}</option>
                        @foreach(['pending', 'sent', 'failed', 'skipped'] as $status)
                        <option value="{{ $status }}" {{ ($filters['status'] ?? '') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">{{ __('Apply') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive"><table class="table table-vcenter card-table">
        <thead><tr><th>{{ __('Event') }}</th><th>{{ __('Endpoint') }}</th><th>{{ __('Status') }}</th><th>{{ __('Attempts') }}</th><th>{{ __('Response') }}</th><th>{{ __('Created') }}</th></tr></thead>
        <tbody>
            @forelse($deliveries ?? [] as $delivery)
            <tr>
                <td class="font-monospace small">{{ $delivery->event }}</td>
                <td class="text-muted">{{ $delivery->endpoint->name ?? '—' }}</td>
                <td>
                    @if($delivery->status === 'sent')
                    <span class="badge bg-green-lt">{{ __('Sent') }}</span>
                    @elseif($delivery->status === 'failed')
                    <span class="badge bg-red-lt">{{ __('Failed') }}</span>
                    @elseif($delivery->status === 'skipped')
                    <span class="badge bg-secondary">{{ __('Skipped') }}</span>
                    @else
                    <span class="badge bg-yellow-lt">{{ __('Pending') }}</span>
                    @endif
                </td>
                <td class="text-muted">{{ $delivery->attempts }}</td>
                <td class="text-muted small">{{ $delivery->response_status ?? '—' }}</td>
                <td class="text-muted">{{ wib($delivery->created_at) }}</td>
            </tr>
            @empty
            <tr><td colspan="6"><div class="empty"><p class="empty-title">{{ __('No deliveries yet.') }}</p></div></td></tr>
            @endforelse
        </tbody>
    </table></div>
    <div class="card-footer d-flex align-items-center justify-content-center">
        {{ ($deliveries ?? collect())->links() }}
    </div>
</div>
@endsection
