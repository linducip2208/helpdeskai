@extends('layouts.admin')
@section('title', 'Edit User')
@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="mb-3">
            <a href="{{ route('admin.users.index') }}">&larr; Back to Users</a>
            <h2 class="page-title mt-1">Edit User</h2>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.users.update', $user) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" class="form-control" required>
                        @error('name')<p class="text-danger small mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" class="form-control" required>
                        @error('email')<p class="text-danger small mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">New Password (leave blank to keep current)</label>
                        <input type="password" name="password" id="password" class="form-control">
                        @error('password')<p class="text-danger small mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Roles</label>
                        @php $userRoleIds = $user->roles->pluck('id')->all(); @endphp
                        @foreach($roles as $role)
                            <label class="form-check">
                                <input type="checkbox" name="roles[]" value="{{ $role->id }}" {{ in_array($role->id, $userRoleIds) ? 'checked' : '' }} class="form-check-input">
                                <span class="form-check-label">{{ ucfirst($role->name) }}</span>
                            </label>
                        @endforeach
                    </div>
                    <div class="mb-3">
                        <label class="form-check">
                            <input type="checkbox" name="is_active" value="1" {{ $user->is_active ? 'checked' : '' }} class="form-check-input">
                            <span class="form-check-label">Active</span>
                        </label>
                    </div>
                    <div class="card-footer d-flex justify-content-end">
                        <a href="{{ route('admin.users.index') }}" class="btn me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
