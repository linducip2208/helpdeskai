@extends('layouts.admin')
@section('title', __('Edit Organization'))
@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.organizations.update', $organization) }}" method="POST">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label">{{ __('Name') }}</label>
                <input type="text" name="name" value="{{ old('name', $organization->name) }}" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">{{ __('Domain') }}</label>
                <input type="text" name="domain" value="{{ old('domain', $organization->domain) }}" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">{{ __('Email') }}</label>
                <input type="email" name="email" value="{{ old('email', $organization->email) }}" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">{{ __('Phone') }}</label>
                <input type="text" name="phone" value="{{ old('phone', $organization->phone) }}" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">{{ __('Notes') }}</label>
                <textarea name="notes" rows="3" class="form-control">{{ old('notes', $organization->notes) }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
            <a href="{{ route('admin.organizations.show', $organization) }}" class="btn">{{ __('Cancel') }}</a>
        </form>
    </div>
</div>
@endsection
