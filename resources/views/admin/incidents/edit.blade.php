@extends('layouts.admin')
@section('title', __('Edit Incident'))
@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <a href="{{ route('admin.incidents.show', $incident) }}" class="btn btn-link px-0">&larr; {{ __('Back to Incident') }}</a>
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.incidents.update', $incident) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label for="title" class="form-label">{{ __('Title') }}</label>
                        <input type="text" name="title" id="title" value="{{ old('title', $incident->title) }}" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">{{ __('Description') }}</label>
                        <textarea name="description" id="description" rows="4" class="form-control" required>{{ old('description', $incident->description) }}</textarea>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="severity" class="form-label">{{ __('Severity') }}</label>
                                <select name="severity" id="severity" class="form-select" required>
                                    @foreach(['low','medium','high','critical'] as $s)
                                    <option value="{{ $s }}" {{ old('severity', $incident->severity) === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="owner_id" class="form-label">{{ __('Owner') }}</label>
                                <select name="owner_id" id="owner_id" class="form-select">
                                    <option value="">— {{ __('Unassigned') }} —</option>
                                    @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('owner_id', $incident->owner_id) == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="root_cause" class="form-label">{{ __('Root cause') }}</label>
                        <textarea name="root_cause" id="root_cause" rows="2" class="form-control">{{ old('root_cause', $incident->root_cause) }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label for="resolution" class="form-label">{{ __('Resolution') }}</label>
                        <textarea name="resolution" id="resolution" rows="2" class="form-control">{{ old('resolution', $incident->resolution) }}</textarea>
                    </div>
                    <div class="form-footer d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.incidents.show', $incident) }}" class="btn">{{ __('Cancel') }}</a>
                        <button type="submit" class="btn btn-primary">{{ __('Update Incident') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
