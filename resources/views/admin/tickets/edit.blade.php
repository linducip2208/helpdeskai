@extends('layouts.admin')
@section('title', 'Edit Ticket #' . ($ticket->id ?? '0'))
@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-10">
        <div class="mb-3">
            <a href="{{ route('admin.tickets.index') }}">&larr; Back to Tickets</a>
            <h2 class="page-title mt-1">Edit Ticket #{{ $ticket->id ?? '0' }}</h2>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.tickets.update', $ticket ?? 0) }}" method="POST" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject</label>
                        <input type="text" name="subject" id="subject" value="{{ old('subject', $ticket->subject ?? '') }}" class="form-control" required>
                        @error('subject') <p class="text-danger small mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="body" class="form-label">Description</label>
                        <textarea name="body" id="body" rows="6" class="form-control" required>{{ old('body', $ticket->body ?? '') }}</textarea>
                        @error('body') <p class="text-danger small mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" id="status" class="form-select">
                                    <option value="open" {{ old('status', $ticket->status ?? '') === 'open' ? 'selected' : '' }}>Open</option>
                                    <option value="pending" {{ old('status', $ticket->status ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="resolved" {{ old('status', $ticket->status ?? '') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                                    <option value="closed" {{ old('status', $ticket->status ?? '') === 'closed' ? 'selected' : '' }}>Closed</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="mb-3">
                                <label for="priority" class="form-label">Priority</label>
                                <select name="priority" id="priority" class="form-select">
                                    <option value="low" {{ old('priority', $ticket->priority ?? '') === 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="medium" {{ old('priority', $ticket->priority ?? '') === 'medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="high" {{ old('priority', $ticket->priority ?? '') === 'high' ? 'selected' : '' }}>High</option>
                                    <option value="urgent" {{ old('priority', $ticket->priority ?? '') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="mb-3">
                                <label for="department_id" class="form-label">Department</label>
                                <select name="department_id" id="department_id" class="form-select">
                                    <option value="">Select Department</option>
                                    @foreach($departments ?? [] as $department)
                                    <option value="{{ $department->id }}" {{ old('department_id', $ticket->department_id ?? '') == $department->id ? 'selected' : '' }}>{{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="mb-3">
                                <label for="category_id" class="form-label">Category</label>
                                <select name="category_id" id="category_id" class="form-select">
                                    <option value="">Select Category</option>
                                    @foreach($categories ?? [] as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $ticket->category_id ?? '') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer d-flex justify-content-end">
                        <a href="{{ route('admin.tickets.index') }}" class="btn me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Ticket</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
