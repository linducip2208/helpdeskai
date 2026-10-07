@extends('layouts.admin')
@section('title', __('New Team'))
@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.teams.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label">{{ __('Name') }}</label>
                <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">{{ __('Description') }}</label>
                <textarea name="description" rows="3" class="form-control">{{ old('description') }}</textarea>
            </div>
            <div class="mb-3">
                <label class="form-check">
                    <input type="checkbox" name="is_active" value="1" class="form-check-input" checked>
                    <span class="form-check-label">{{ __('Active') }}</span>
                </label>
            </div>
            <div class="mb-3">
                <label class="form-label">{{ __('Members') }}</label>
                <select name="members[]" multiple class="form-select" size="8">
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
            <a href="{{ route('admin.teams.index') }}" class="btn">{{ __('Cancel') }}</a>
        </form>
    </div>
</div>
@endsection
