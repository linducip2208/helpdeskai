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

    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title">AI &amp; Privacy</h3>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-check form-switch">
                    <input type="checkbox" name="ai.enabled" value="1" {{ ($settings->{'ai.enabled'} ?? true) ? 'checked' : '' }} class="form-check-input">
                    <span class="form-check-label">Enable AI features</span>
                </label>
            </div>
            <div class="mb-3">
                <label class="form-check form-switch">
                    <input type="checkbox" name="ai.process_ticket_content" value="1" {{ ($settings->{'ai.process_ticket_content'} ?? true) ? 'checked' : '' }} class="form-check-input">
                    <span class="form-check-label">Allow AI to process ticket content (classification, sentiment, suggestions)</span>
                </label>
                <div class="form-hint">When off, AI calls that would include customer data are blocked by policy. Only the minimum required context is ever sent to providers.</div>
            </div>
            <div class="mb-3">
                <label for="ai_data_retention_days" class="form-label">AI usage log retention (days, 0 = keep forever)</label>
                <input type="number" name="ai_data_retention_days" id="ai_data_retention_days" min="0" value="{{ old('ai_data_retention_days', $settings->ai_data_retention_days ?? 365) }}" class="form-control">
            </div>
            <div class="row g-2">
                <div class="col-md-6">
                    <label for="webhook_retention_days" class="form-label">Webhook delivery retention (days, 0 = keep)</label>
                    <input type="number" name="webhook_retention_days" id="webhook_retention_days" min="0" value="{{ old('webhook_retention_days', $settings->webhook_retention_days ?? 90) }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label for="notification_retention_days" class="form-label">Notification retention (days, 0 = keep)</label>
                    <input type="number" name="notification_retention_days" id="notification_retention_days" min="0" value="{{ old('notification_retention_days', $settings->notification_retention_days ?? 180) }}" class="form-control">
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title">AI Policy (human-in-the-loop)</h3>
        </div>
        <div class="card-body">
            <div class="row g-2">
                <div class="col-md-6">
                    <label class="form-label" for="ai_classification_mode">Classification apply mode</label>
                    <select name="ai.classification_mode" id="ai_classification_mode" class="form-select">
                        <option value="automatic" {{ ($settings->{'ai.classification_mode'} ?? 'automatic') === 'automatic' ? 'selected' : '' }}>Automatic (apply department/category)</option>
                        <option value="suggest" {{ ($settings->{'ai.classification_mode'} ?? '') === 'suggest' ? 'selected' : '' }}>Suggestion only (record, do not apply)</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="ai_priority_mode">Priority apply mode</label>
                    <select name="ai.priority_mode" id="ai_priority_mode" class="form-select">
                        <option value="automatic" {{ ($settings->{'ai.priority_mode'} ?? 'automatic') === 'automatic' ? 'selected' : '' }}>Automatic</option>
                        <option value="suggest" {{ ($settings->{'ai.priority_mode'} ?? '') === 'suggest' ? 'selected' : '' }}>Suggestion only</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="ai_low_confidence_threshold">Low-confidence threshold (0–1)</label>
                    <input type="number" step="0.05" min="0" max="1" name="ai.low_confidence_threshold" id="ai_low_confidence_threshold" value="{{ old('ai.low_confidence_threshold', $settings->{'ai.low_confidence_threshold'} ?? 0.5) }}" class="form-control">
                    <div class="form-hint">KB answers below this confidence create an internal review note instead of being trusted.</div>
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <label class="form-check">
                        <input type="checkbox" name="ai.qa_enabled" value="1" {{ ($settings->{'ai.qa_enabled'} ?? false) ? 'checked' : '' }} class="form-check-input">
                        <span class="form-check-label">Enable AI reply quality scoring</span>
                    </label>
                </div>
            </div>
            <p class="text-muted small mt-2 mb-0">AI never auto-sends customer replies and never auto-closes tickets. Suggestions are always drafts for agent review.</p>
        </div>
    </div>

    <div class="d-flex justify-content-end">
        <button type="submit" class="btn btn-primary">Save Settings</button>
    </div>
</form>

@endsection
