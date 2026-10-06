@extends('layouts.admin')
@section('title', __('Edit Webhook'))
@section('content')

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="mb-3"><a href="{{ route('admin.webhooks.index') }}">&larr; {{ __('Back to Webhooks') }}</a></div>
        <form action="{{ route('admin.webhooks.update', $webhook) }}" method="POST">
            @csrf @method('PUT')
            <div class="card">
                <div class="card-header"><h3 class="card-title">{{ __('Edit Webhook') }}</h3></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label" for="name">{{ __('Name') }}</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $webhook->name) }}" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="url">{{ __('URL') }}</label>
                        <input type="url" name="url" id="url" value="{{ old('url', $webhook->url) }}" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <span class="form-label">{{ __('Events') }}</span>
                        @foreach($events ?? [] as $event)
                        <label class="form-check">
                            <input type="checkbox" name="events[]" value="{{ $event }}" {{ in_array($event, old('events', $webhook->eventList())) ? 'checked' : '' }} class="form-check-input">
                            <span class="form-check-label font-monospace">{{ $event }}</span>
                        </label>
                        @endforeach
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label" for="timeout_seconds">{{ __('Timeout (seconds)') }}</label>
                            <input type="number" name="timeout_seconds" id="timeout_seconds" min="3" max="60" value="{{ old('timeout_seconds', $webhook->timeout_seconds) }}" class="form-control">
                        </div>
                        <div class="col-md-6 d-flex align-items-end gap-3">
                            <label class="form-check">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $webhook->is_active) ? 'checked' : '' }} class="form-check-input">
                                <span class="form-check-label">{{ __('Active') }}</span>
                            </label>
                            <label class="form-check">
                                <input type="checkbox" name="rotate_secret" value="1" class="form-check-input">
                                <span class="form-check-label">{{ __('Rotate secret') }}</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.webhooks.index') }}" class="btn">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-primary">{{ __('Update Endpoint') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
