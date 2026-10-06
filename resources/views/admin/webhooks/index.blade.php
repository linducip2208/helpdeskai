@extends('layouts.admin')
@section('title', __('Webhooks'))
@section('page-actions')
    <a href="{{ route('admin.webhooks.deliveries') }}" class="btn">{{ __('Delivery Log') }}</a>
@endsection
@section('content')

<div class="row">
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('Endpoints') }}</h3></div>
            <div class="table-responsive"><table class="table table-vcenter card-table">
                <thead><tr><th>{{ __('Name') }}</th><th>{{ __('URL') }}</th><th>{{ __('Events') }}</th><th>{{ __('Status') }}</th><th class="text-end">{{ __('Actions') }}</th></tr></thead>
                <tbody>
                    @forelse($endpoints ?? [] as $endpoint)
                    <tr>
                        <td><strong>{{ $endpoint->name }}</strong></td>
                        <td class="text-muted font-monospace small">{{ \Illuminate\Support\Str::limit($endpoint->url, 48) }}</td>
                        <td>
                            @foreach($endpoint->eventList() as $event)
                            <span class="badge bg-blue-lt mb-1">{{ $event }}</span>
                            @endforeach
                        </td>
                        <td>
                            @if($endpoint->is_active)
                            <span class="badge bg-green-lt">{{ __('Active') }}</span>
                            @else
                            <span class="badge bg-secondary">{{ __('Inactive') }}</span>
                            @endif
                            @if(($endpoint->failed_deliveries ?? 0) > 0)
                            <span class="badge bg-red-lt">{{ $endpoint->failed_deliveries }} {{ __('failed') }}</span>
                            @endif
                        </td>
                        <td class="text-end text-nowrap">
                            <form action="{{ route('admin.webhooks.test', $endpoint) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm">{{ __('Send test') }}</button>
                            </form>
                            <a href="{{ route('admin.webhooks.edit', $endpoint) }}" class="btn btn-sm">{{ __('Edit') }}</a>
                            <form action="{{ route('admin.webhooks.destroy', $endpoint) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this endpoint?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">{{ __('Delete') }}</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5"><div class="empty"><p class="empty-title">{{ __('No webhook endpoints yet.') }}</p><p class="empty-subtitle text-muted">{{ __('Add an endpoint to receive signed ticket events.') }}</p></div></td></tr>
                    @endforelse
                </tbody>
            </table></div>
        </div>

        <div class="card mt-3">
            <div class="card-header"><h3 class="card-title">{{ __('Verify signatures') }}</h3></div>
            <div class="card-body">
                <p class="text-muted">{{ __('Every delivery carries these headers. Verify on your receiver:') }}</p>
                <ul class="text-muted small">
                    <li><code>X-Webhook-Event</code>, <code>X-Webhook-Timestamp</code> (unix seconds — reject if older than 5 minutes), <code>X-Idempotency-Key</code></li>
                    <li><code>X-Webhook-Signature: sha256=&lt;hex&gt;</code> where hex = <code>HMAC_SHA256(timestamp + "." + raw_body, endpoint_secret)</code></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('Add Endpoint') }}</h3></div>
            <div class="card-body">
                <form action="{{ route('admin.webhooks.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="name">{{ __('Name') }}</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="url">{{ __('URL') }}</label>
                        <input type="url" name="url" id="url" value="{{ old('url') }}" class="form-control" placeholder="https://example.com/hooks/helpdesk" required>
                    </div>
                    <div class="mb-3">
                        <span class="form-label">{{ __('Events') }}</span>
                        @foreach($events ?? [] as $event)
                        <label class="form-check">
                            <input type="checkbox" name="events[]" value="{{ $event }}" {{ in_array($event, old('events', [])) ? 'checked' : '' }} class="form-check-input">
                            <span class="form-check-label font-monospace">{{ $event }}</span>
                        </label>
                        @endforeach
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="timeout_seconds">{{ __('Timeout (seconds)') }}</label>
                        <input type="number" name="timeout_seconds" id="timeout_seconds" min="3" max="60" value="{{ old('timeout_seconds', 10) }}" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">{{ __('Add Endpoint') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
