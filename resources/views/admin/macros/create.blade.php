@extends('layouts.admin')
@section('title', __('New Macro'))
@section('content')

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="mb-3"><a href="{{ route('admin.macros.index') }}">&larr; {{ __('Back to Macros') }}</a></div>
        <form action="{{ route('admin.macros.store') }}" method="POST">
            @csrf
            <div class="card">
                <div class="card-header"><h3 class="card-title">{{ __('New Macro') }}</h3></div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label" for="name">{{ __('Name') }}</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="visibility">{{ __('Visibility') }}</label>
                            <select name="visibility" id="visibility" class="form-select">
                                <option value="personal">{{ __('Personal (only me)') }}</option>
                                <option value="shared">{{ __('Shared (all staff)') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3 mt-3">
                        <label class="form-label" for="reply">{{ __('Reply (public)') }}</label>
                        <textarea name="actions[reply]" id="reply" rows="3" class="form-control">{{ old('actions.reply') }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="internal_note">{{ __('Internal note') }}</label>
                        <textarea name="actions[internal_note]" id="internal_note" rows="2" class="form-control">{{ old('actions.internal_note') }}</textarea>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label" for="status">{{ __('Set status') }}</label>
                            <select name="actions[status]" id="status" class="form-select">
                                <option value="">{{ __('No change') }}</option>
                                @foreach(['open', 'in_progress', 'waiting', 'answered', 'resolved', 'closed'] as $st)
                                <option value="{{ $st }}">{{ ucfirst(str_replace('_', ' ', $st)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="priority">{{ __('Set priority') }}</label>
                            <select name="actions[priority]" id="priority" class="form-select">
                                <option value="">{{ __('No change') }}</option>
                                @foreach(['low', 'medium', 'high', 'urgent'] as $p)
                                <option value="{{ $p }}">{{ ucfirst($p) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="assigned_to">{{ __('Assign to (user ID)') }}</label>
                            <input type="number" name="actions[assigned_to]" id="assigned_to" class="form-control" placeholder="{{ __('Optional') }}">
                        </div>
                    </div>
                    <div class="row g-2 mt-1">
                        <div class="col-md-6">
                            <label class="form-label" for="add_tags">{{ __('Add tags (comma separated)') }}</label>
                            <input type="text" name="actions[add_tags]" id="add_tags" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="remove_tags">{{ __('Remove tags (comma separated)') }}</label>
                            <input type="text" name="actions[remove_tags]" id="remove_tags" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.macros.index') }}" class="btn">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-primary">{{ __('Save Macro') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
