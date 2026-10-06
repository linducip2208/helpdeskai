@extends('layouts.admin')
@section('title', 'Add SLA Policy')
@section('content')

<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <a href="{{ route('admin.sla-policies.index') }}" class="text-sm text-indigo-600 hover:text-indigo-700">&larr; Back to SLA Policies</a>
        <h2 class="text-2xl font-bold text-slate-900 mt-1">Add SLA Policy</h2>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form action="{{ route('admin.sla-policies.store') }}" method="POST">
            @csrf
            <div class="space-y-5">
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Policy Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" class="w-full rounded-lg border-gray-200 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                    @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="description" class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                    <textarea name="description" id="description" rows="2" class="w-full rounded-lg border-gray-200 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('description') }}</textarea>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="department_id" class="block text-sm font-medium text-slate-700 mb-1">Department</label>
                        <select name="department_id" id="department_id" class="w-full rounded-lg border-gray-200 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                            <option value="">— Select Department —</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="priority" class="block text-sm font-medium text-slate-700 mb-1">Priority</label>
                        <select name="priority" id="priority" class="w-full rounded-lg border-gray-200 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="first_response_time" class="block text-sm font-medium text-slate-700 mb-1">First Response (minutes)</label>
                        <input type="number" min="1" name="first_response_time" id="first_response_time" value="{{ old('first_response_time', 60) }}" class="w-full rounded-lg border-gray-200 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                    </div>
                    <div>
                        <label for="resolution_time" class="block text-sm font-medium text-slate-700 mb-1">Resolution Time (minutes)</label>
                        <input type="number" min="1" name="resolution_time" id="resolution_time" value="{{ old('resolution_time', 1440) }}" class="w-full rounded-lg border-gray-200 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                    </div>
                </div>
                <div class="flex items-center">
                    <label class="inline-flex items-center text-sm text-slate-700">
                        <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 mr-2">
                        Active
                    </label>
                </div>
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                    <a href="{{ route('admin.sla-policies.index') }}" class="px-6 py-2.5 border border-gray-200 text-slate-700 text-sm font-medium rounded-lg hover:bg-gray-50">Cancel</a>
                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg">Save Policy</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection
