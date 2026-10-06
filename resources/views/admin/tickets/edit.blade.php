@extends('layouts.admin')
@section('title', 'Edit Ticket #' . ($ticket->id ?? '0'))
@section('content')

<div class="max-w-3xl mx-auto space-y-6">
    <div>
        <a href="{{ route('admin.tickets.index') }}" class="text-sm text-indigo-600 hover:text-indigo-700">&larr; Back to Tickets</a>
        <h2 class="text-2xl font-bold text-slate-900 mt-1">Edit Ticket #{{ $ticket->id ?? '0' }}</h2>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form action="{{ route('admin.tickets.update', $ticket ?? 0) }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="space-y-5">
                <div>
                    <label for="subject" class="block text-sm font-medium text-slate-700 mb-1">Subject</label>
                    <input type="text" name="subject" id="subject" value="{{ old('subject', $ticket->subject ?? '') }}"
                           class="w-full rounded-lg border-gray-200 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                    @error('subject') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="body" class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                    <textarea name="body" id="body" rows="6"
                              class="w-full rounded-lg border-gray-200 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>{{ old('body', $ticket->body ?? '') }}</textarea>
                    @error('body') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="status" class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                        <select name="status" id="status"
                                class="w-full rounded-lg border-gray-200 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="open" {{ old('status', $ticket->status ?? '') === 'open' ? 'selected' : '' }}>Open</option>
                            <option value="pending" {{ old('status', $ticket->status ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="resolved" {{ old('status', $ticket->status ?? '') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                            <option value="closed" {{ old('status', $ticket->status ?? '') === 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                    </div>
                    <div>
                        <label for="priority" class="block text-sm font-medium text-slate-700 mb-1">Priority</label>
                        <select name="priority" id="priority"
                                class="w-full rounded-lg border-gray-200 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="low" {{ old('priority', $ticket->priority ?? '') === 'low' ? 'selected' : '' }}>Low</option>
                            <option value="medium" {{ old('priority', $ticket->priority ?? '') === 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="high" {{ old('priority', $ticket->priority ?? '') === 'high' ? 'selected' : '' }}>High</option>
                            <option value="urgent" {{ old('priority', $ticket->priority ?? '') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="department_id" class="block text-sm font-medium text-slate-700 mb-1">Department</label>
                        <select name="department_id" id="department_id"
                                class="w-full rounded-lg border-gray-200 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="">Select Department</option>
                            @foreach($departments ?? [] as $department)
                            <option value="{{ $department->id }}" {{ old('department_id', $ticket->department_id ?? '') == $department->id ? 'selected' : '' }}>{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="category_id" class="block text-sm font-medium text-slate-700 mb-1">Category</label>
                        <select name="category_id" id="category_id"
                                class="w-full rounded-lg border-gray-200 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="">Select Category</option>
                            @foreach($categories ?? [] as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $ticket->category_id ?? '') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100">
                    <a href="{{ route('admin.tickets.index') }}" class="px-6 py-2.5 border border-gray-200 text-slate-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">Cancel</a>
                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">Update Ticket</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection
