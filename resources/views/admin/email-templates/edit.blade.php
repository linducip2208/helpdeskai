@extends('layouts.admin')
@section('title', 'Edit Email Template')
@section('content')

<div class="row justify-content-center">
    <div class="col-12 col-lg-10">
        <a href="{{ route('admin.email-templates.index') }}" class="btn btn-link px-0">&larr; Back to Templates</a>
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.email-templates.update', $template ?? 0) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label for="name" class="form-label">Template Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $template->name ?? '') }}" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject</label>
                        <input type="text" name="subject" id="subject" value="{{ old('subject', $template->subject ?? '') }}" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="body" class="form-label">Body (HTML)</label>
                        <textarea name="body" id="body" rows="12" class="form-control" required>{{ old('body', $template->body ?? '') }}</textarea>
                        <div class="form-hint">Available variables: {{ '{{name}}' }}, {{ '{{email}}' }}, {{ '{{ticket_id}}' }}, {{ '{{ticket_subject}}' }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-check form-switch">
                            <input type="checkbox" name="is_active" value="1" {{ ($template->is_active ?? true) ? 'checked' : '' }} class="form-check-input">
                            <span class="form-check-label">Active</span>
                        </label>
                    </div>
                    <div class="form-footer d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.email-templates.index') }}" class="btn">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Template</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
