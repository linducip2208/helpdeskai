@extends('layouts.admin')
@section('title', __('Add Incident'))
@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <a href="{{ route('admin.incidents.index') }}" class="btn btn-link px-0">&larr; {{ __('Back to Incidents') }}</a>
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.incidents.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="title" class="form-label">{{ __('Title') }}</label>
                        <input type="text" name="title" id="title" value="{{ old('title') }}" class="form-control" required>
                        @error('title')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">{{ __('Description') }}</label>
                        <textarea name="description" id="description" rows="4" class="form-control" required>{{ old('description') }}</textarea>
                        @error('description')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="severity" class="form-label">{{ __('Severity') }}</label>
                                <select name="severity" id="severity" class="form-select" required>
                                    @foreach(['low','medium','high','critical'] as $s)
                                    <option value="{{ $s }}" {{ old('severity','medium') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
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
                                    <option value="{{ $user->id }}" {{ old('owner_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="started_at" class="form-label">{{ __('Started at') }}</label>
                        <input type="datetime-local" name="started_at" id="started_at" value="{{ old('started_at') }}" class="form-control">
                    </div>
                    <div class="form-footer d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.incidents.index') }}" class="btn">{{ __('Cancel') }}</a>
                        <button type="submit" class="btn btn-primary">{{ __('Save Incident') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
