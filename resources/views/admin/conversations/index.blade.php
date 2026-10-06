@extends('layouts.admin')
@section('title', 'Conversations')
@section('content')

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-slate-900">Conversations</h2>
        <a href="{{ route('admin.conversations.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Conversation
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Subject</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Channel</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Messages</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Last Message</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($conversations ?? [] as $conversation)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-emerald-100 rounded-full flex items-center justify-center text-emerald-600 font-semibold text-sm mr-3">
                                    {{ substr($conversation->user->name ?? 'U', 0, 1) }}
                                </div>
                                <span class="text-sm font-medium text-slate-900">{{ $conversation->user->name ?? 'Guest' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-500">{{ Str::limit($conversation->subject ?? 'Chat', 40) }}</td>
                        <td class="px-6 py-4 text-sm text-slate-500">{{ ucfirst($conversation->channel ?? 'web') }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ ($conversation->status ?? '') === 'active' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                {{ ($conversation->status ?? '') === 'waiting' ? 'bg-amber-100 text-amber-700' : '' }}
                                {{ ($conversation->status ?? '') === 'closed' ? 'bg-slate-100 text-slate-700' : '' }}">
                                {{ ucfirst($conversation->status ?? 'Active') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-500">{{ $conversation->messages_count ?? 0 }}</td>
                        <td class="px-6 py-4 text-sm text-slate-500">{{ $conversation->updated_at->diffForHumans() }}</td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <a href="{{ route('admin.conversations.show', $conversation) }}" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">View</a>
                            <form action="{{ route('admin.conversations.destroy', $conversation) }}" method="POST" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-600 text-sm font-medium" onclick="return confirm('Delete?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-6 py-12 text-center text-slate-400">No conversations found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-3 bg-gray-50 border-t border-gray-100">
            {{ ($conversations ?? collect())->links() }}
        </div>
    </div>
</div>

@endsection
