@extends('layouts.admin')
@section('title', 'Create Ticket')
@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-10">
        <div class="mb-3">
            <a href="{{ route('admin.tickets.index') }}">&larr; Back to Tickets</a>
            <h2 class="page-title mt-1">Create New Ticket</h2>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.tickets.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject</label>
                        <input type="text" name="subject" id="subject" value="{{ old('subject') }}" class="form-control" placeholder="Brief summary of the issue" required>
                        @error('subject') <p class="text-danger small mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="body" class="form-label">Description</label>
                        <textarea name="body" id="body" rows="6" class="form-control" placeholder="Detailed description of the issue..." required>{{ old('body') }}</textarea>
                        @error('body') <p class="text-danger small mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="mb-3">
                                <label for="department_id" class="form-label">Department</label>
                                <select name="department_id" id="department_id" class="form-select" required>
                                    <option value="">Select Department</option>
                                    @foreach($departments ?? [] as $department)
                                    <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>{{ $department->name }}</option>
                                    @endforeach
                                </select>
                                @error('department_id') <p class="text-danger small mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="mb-3">
                                <label for="category_id" class="form-label">Category</label>
                                <select name="category_id" id="category_id" class="form-select" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories ?? [] as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id') <p class="text-danger small mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="mb-3">
                                <label for="priority" class="form-label">Priority</label>
                                <select name="priority" id="priority" class="form-select">
                                    <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="medium" {{ old('priority') === 'medium' ? 'selected' : '' }} selected>Medium</option>
                                    <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High</option>
                                    <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="mb-3">
                                <label for="assigned_to" class="form-label">Assign To</label>
                                <select name="assigned_to" id="assigned_to" class="form-select">
                                    <option value="">Unassigned</option>
                                    @foreach($agents ?? [] as $agent)
                                    <option value="{{ $agent->id }}" {{ old('assigned_to') == $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    @include('tickets._custom_fields', ['customFields' => $customFields ?? collect(), 'fieldsUrl' => $fieldsUrl ?? null])

                    <div class="card-footer d-flex justify-content-end">
                        <a href="{{ route('admin.tickets.index') }}" class="btn me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">Create Ticket</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
