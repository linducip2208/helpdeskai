@extends('layouts.admin')
@section('title', 'Edit Email Template')
@section('content')

<div class="max-w-3xl mx-auto space-y-6">
    <div>
        <a href="{{ route('admin.email-templates.index') }}" class="text-sm text-indigo-600 hover:text-indigo-700">&larr; Back to Templates</a>
        <h2 class="text-2xl font-bold text-slate-900 mt-1">Edit Email Template</h2>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form action="{{ route('admin.email-templates.update', $template ?? 0) }}" method="POST">
            @csrf @method('PUT')
            <div class="space-y-5">
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Template Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $template->name ?? '') }}" class="w-full rounded-lg border-gray-200 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                </div>
                <div>
                    <label for="subject" class="block text-sm font-medium text-slate-700 mb-1">Subject</label>
                    <input type="text" name="subject" id="subject" value="{{ old('subject', $template->subject ?? '') }}" class="w-full rounded-lg border-gray-200 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                </div>
                <div>
                    <label for="body" class="block text-sm font-medium text-slate-700 mb-1">Body (HTML)</label>
                    <textarea name="body" id="body" rows="12" class="w-full rounded-lg border-gray-200 text-sm font-mono focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>{{ old('body', $template->body ?? '') }}</textarea>
                    <p class="text-xs text-slate-400 mt-1">Available variables: {{ '{{name}}' }}, {{ '{{email}}' }}, {{ '{{ticket_id}}' }}, {{ '{{ticket_subject}}' }}</p>
                </div>
                <div class="flex items-center">
                    <label class="inline-flex items-center text-sm text-slate-700">
                        <input type="checkbox" name="is_active" value="1" {{ ($template->is_active ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 mr-2">
                        Active
                    </label>
                </div>
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                    <a href="{{ route('admin.email-templates.index') }}" class="px-6 py-2.5 border border-gray-200 text-slate-700 text-sm font-medium rounded-lg hover:bg-gray-50">Cancel</a>
                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg">Update Template</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection
