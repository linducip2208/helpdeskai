@extends('layouts.admin')
@section('title', 'Settings')
@section('content')

<form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
    @csrf @method('PUT')

    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title">General</h3>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label for="site_name" class="form-label">Site Name</label>
                <input type="text" name="site_name" id="site_name" value="{{ old('site_name', config('app.name')) }}" class="form-control">
            </div>
            <div class="mb-3">
                <label for="site_description" class="form-label">Site Description</label>
                <textarea name="site_description" id="site_description" rows="2" class="form-control">{{ old('site_description', $settings->site_description ?? '') }}</textarea>
            </div>
            <div class="mb-3">
                <label for="support_email" class="form-label">Support Email</label>
                <input type="email" name="support_email" id="support_email" value="{{ old('support_email', $settings->support_email ?? '') }}" class="form-control">
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title">Ticket Settings</h3>
        </div>
        <div class="card-body">
            <div class="row g-2">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="default_priority" class="form-label">Default Priority</label>
                        <select name="default_priority" id="default_priority" class="form-select">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="ticket_prefix" class="form-label">Ticket ID Prefix</label>
                        <input type="text" name="ticket_prefix" id="ticket_prefix" value="{{ old('ticket_prefix', $settings->ticket_prefix ?? 'TKT-') }}" class="form-control">
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <label for="auto_close_days" class="form-label">Auto-close Resolved Tickets After (days)</label>
                <input type="number" name="auto_close_days" id="auto_close_days" value="{{ old('auto_close_days', $settings->auto_close_days ?? 7) }}" class="form-control">
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title">Notifications</h3>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-check form-switch">
                    <input type="checkbox" name="notify_new_ticket" value="1" {{ ($settings->notify_new_ticket ?? true) ? 'checked' : '' }} class="form-check-input">
                    <span class="form-check-label">Email notifications for new tickets</span>
                </label>
            </div>
            <div class="mb-3">
                <label class="form-check form-switch">
                    <input type="checkbox" name="notify_ticket_reply" value="1" {{ ($settings->notify_ticket_reply ?? true) ? 'checked' : '' }} class="form-check-input">
                    <span class="form-check-label">Email notifications for ticket replies</span>
                </label>
            </div>
            <div class="mb-3">
                <label class="form-check form-switch">
                    <input type="checkbox" name="notify_sla_breach" value="1" {{ ($settings->notify_sla_breach ?? true) ? 'checked' : '' }} class="form-check-input">
                    <span class="form-check-label">SLA breach alerts</span>
                </label>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end">
        <button type="submit" class="btn btn-primary">Save Settings</button>
    </div>
</form>

@endsection
