@extends('layouts.admin')
@section('title', 'Edit SLA Policy')
@section('content')

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <a href="{{ route('admin.sla-policies.index') }}" class="btn btn-link px-0">&larr; Back to SLA Policies</a>
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.sla-policies.update', $policy) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label for="name" class="form-label">Policy Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $policy->name) }}" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" id="description" rows="2" class="form-control">{{ old('description', $policy->description) }}</textarea>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="department_id" class="form-label">Department</label>
                                <select name="department_id" id="department_id" class="form-select" required>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}" {{ old('department_id', $policy->department_id) == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="priority" class="form-label">Priority</label>
                                <select name="priority" id="priority" class="form-select" required>
                                    @foreach(['low', 'medium', 'high', 'urgent'] as $p)
                                        <option value="{{ $p }}" {{ old('priority', $policy->priority) === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="first_response_time" class="form-label">First Response (minutes)</label>
                                <input type="number" min="1" name="first_response_time" id="first_response_time" value="{{ old('first_response_time', $policy->first_response_time) }}" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="resolution_time" class="form-label">Resolution Time (minutes)</label>
                                <input type="number" min="1" name="resolution_time" id="resolution_time" value="{{ old('resolution_time', $policy->resolution_time) }}" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-check form-switch">
                            <input type="checkbox" name="is_active" value="1" {{ $policy->is_active ? 'checked' : '' }} class="form-check-input">
                            <span class="form-check-label">Active</span>
                        </label>
                    </div>
                    <div class="form-footer d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.sla-policies.index') }}" class="btn">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Policy</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
