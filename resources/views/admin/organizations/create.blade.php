@extends('layouts.admin')
@section('title', __('New Organization'))
@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.organizations.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label">{{ __('Name') }}</label>
                <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">{{ __('Domain') }}</label>
                <input type="text" name="domain" value="{{ old('domain') }}" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">{{ __('Email') }}</label>
                <input type="email" name="email" value="{{ old('email') }}" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">{{ __('Phone') }}</label>
                <input type="text" name="phone" value="{{ old('phone') }}" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">{{ __('Notes') }}</label>
                <textarea name="notes" rows="3" class="form-control">{{ old('notes') }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
            <a href="{{ route('admin.organizations.index') }}" class="btn">{{ __('Cancel') }}</a>
        </form>
    </div>
</div>
@endsection
