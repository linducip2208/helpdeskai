@extends('layouts.admin')
@section('title', 'Push Subscriptions')
@section('content')

<div class="space-y-6">
    <div>
        <h2 class="text-2xl font-bold text-slate-900">Push Subscriptions</h2>
        <p class="text-sm text-slate-500 mt-1">User yang sudah aktivasi web push notification.</p>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
        <div class="bg-white rounded-xl border border-gray-100 p-4">
            <p class="text-xs text-slate-500">Active Subscriptions</p>
            <p class="text-2xl font-bold text-slate-900">{{ number_format($totals['all']) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4">
            <p class="text-xs text-slate-500">Unique Subscribers</p>
            <p class="text-2xl font-bold text-indigo-600">{{ number_format($totals['unique_users']) }}</p>
        </div>
    </div>

    {{-- Broadcast form --}}
    <div class="bg-white rounded-xl border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-slate-900 mb-3">Send Broadcast</h3>
        <form method="POST" action="{{ route('admin.push-subscriptions.broadcast') }}" class="space-y-3">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <input type="text" name="title" placeholder="Notification title" maxlength="120" required class="rounded-lg border-gray-200 text-sm">
                <input type="url" name="url" placeholder="Action URL (optional)" class="rounded-lg border-gray-200 text-sm">
            </div>
            <textarea name="body" rows="2" placeholder="Notification body" maxlength="300" required class="w-full rounded-lg border-gray-200 text-sm"></textarea>
            <div class="flex flex-wrap items-center gap-3">
                <label class="text-sm text-slate-700"><input type="radio" name="target" value="all" checked> Send to all subscribers</label>
                <label class="text-sm text-slate-700"><input type="radio" name="target" value="user"> Specific user ID: <input type="number" name="user_id" class="w-24 ml-1 rounded border-gray-200 text-xs"></label>
                <button class="ml-auto px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm rounded-lg">Queue Broadcast</button>
            </div>
        </form>
    </div>

    {{-- Subscribers table --}}
    <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-3 text-left">User</th>
                        <th class="px-4 py-3 text-left">Endpoint</th>
                        <th class="px-4 py-3 text-left">Browser / Device</th>
                        <th class="px-4 py-3 text-left">Subscribed</th>
                        <th class="px-4 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($subscriptions as $sub)
                        <tr>
                            <td class="px-4 py-3">
                                <p class="font-medium text-slate-900">{{ $sub->user->name ?? 'Unknown' }}</p>
                                <p class="text-xs text-slate-500">{{ $sub->user->email ?? '—' }}</p>
                            </td>
                            <td class="px-4 py-3 font-mono text-xs text-slate-500 truncate max-w-xs">{{ \Illuminate\Support\Str::limit($sub->endpoint, 60) }}</td>
                            <td class="px-4 py-3 text-xs text-slate-600 max-w-xs truncate" title="{{ $sub->user_agent }}">{{ \Illuminate\Support\Str::limit($sub->user_agent, 40) }}</td>
                            <td class="px-4 py-3 text-xs text-slate-500">{{ $sub->created_at?->diffForHumans() }}</td>
                            <td class="px-4 py-3 text-right">
                                <form method="POST" action="{{ route('admin.push-subscriptions.destroy', $sub) }}" onsubmit="return confirm('Remove this subscription?')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs text-rose-600 hover:text-rose-700">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-12 text-center text-slate-400">No active subscriptions. User perlu enable push di profile dulu.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($subscriptions->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $subscriptions->links() }}</div>
        @endif
    </div>
</div>

@endsection
