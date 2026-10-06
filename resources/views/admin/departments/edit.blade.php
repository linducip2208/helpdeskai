@extends('layouts.admin')
@section('title', 'Edit Department')
@section('content')

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <a href="{{ route('admin.departments.index') }}" class="btn btn-link px-0">&larr; Back to Departments</a>
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.departments.update', $department) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $department->name) }}" class="form-control" required>
                        @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" id="description" rows="3" class="form-control">{{ old('description', $department->description) }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-check form-switch">
                            <input type="checkbox" name="is_active" value="1" {{ $department->is_active ? 'checked' : '' }} class="form-check-input">
                            <span class="form-check-label">Active</span>
                        </label>
                    </div>
                    <div class="form-footer d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.departments.index') }}" class="btn">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Department</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
