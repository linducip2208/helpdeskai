@extends('layouts.admin')
@section('title', 'New Conversation')
@section('content')

<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <a href="{{ route('admin.conversations.index') }}" class="text-sm text-indigo-600 hover:text-indigo-700">&larr; Back to Conversations</a>
        <h2 class="text-2xl font-bold text-slate-900 mt-1">New Conversation</h2>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form action="{{ route('admin.conversations.store') }}" method="POST">
            @csrf
            <div class="space-y-5">
                <div>
                    <label for="user_id" class="block text-sm font-medium text-slate-700 mb-1">Customer</label>
                    <select name="user_id" id="user_id" class="w-full rounded-lg border-gray-200 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                        <option value="">— Select Customer —</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ old('user_id') == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="subject" class="block text-sm font-medium text-slate-700 mb-1">Subject</label>
                    <input type="text" name="subject" id="subject" value="{{ old('subject') }}" class="w-full rounded-lg border-gray-200 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
                <div>
                    <label for="body" class="block text-sm font-medium text-slate-700 mb-1">Initial Message</label>
                    <textarea name="body" id="body" rows="5" class="w-full rounded-lg border-gray-200 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>{{ old('body') }}</textarea>
                </div>
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                    <a href="{{ route('admin.conversations.index') }}" class="px-6 py-2.5 border border-gray-200 text-slate-700 text-sm font-medium rounded-lg hover:bg-gray-50">Cancel</a>
                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg">Start Conversation</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection
