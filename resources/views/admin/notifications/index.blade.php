@extends('layouts.admin')
@section('title', 'Notifications')
@section('content')

<div class="space-y-6">
    <div>
        <h2 class="text-2xl font-bold text-slate-900">Notifications</h2>
        <p class="text-sm text-slate-500 mt-1">Riwayat notifikasi untuk akun {{ auth()->user()->name }}.</p>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
        @forelse($notifications as $n)
            @php
                $data = is_array($n->data) ? $n->data : (array) json_decode($n->data ?? '[]', true);
            @endphp
            <div class="flex gap-3 px-4 py-3 border-b border-gray-100 last:border-0 {{ $n->read_at ? 'bg-white' : 'bg-indigo-50/30' }}">
                <div class="w-2 h-2 mt-2 rounded-full {{ $n->read_at ? 'bg-gray-300' : 'bg-indigo-500' }}"></div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-2">
                        <h4 class="text-sm font-semibold text-slate-900 truncate">{{ $data['title'] ?? class_basename($n->type) }}</h4>
                        <span class="text-xs text-slate-400 shrink-0">{{ $n->created_at?->diffForHumans() }}</span>
                    </div>
                    <p class="text-sm text-slate-600">{{ $data['body'] ?? $data['message'] ?? '' }}</p>
                    @if(! empty($data['url']))
                        <a href="{{ $data['url'] }}" class="text-xs text-indigo-600 hover:text-indigo-700 mt-1 inline-block">Open &rarr;</a>
                    @endif
                </div>
                <form method="POST" action="{{ route('admin.notifications.destroy', $n) }}" onsubmit="return confirm('Delete this notification?')">
                    @csrf @method('DELETE')
                    <button class="text-xs text-rose-600 hover:text-rose-700">Delete</button>
                </form>
            </div>
        @empty
            <div class="px-4 py-12 text-center text-slate-400">No notifications yet.</div>
        @endforelse
        @if($notifications->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $notifications->links() }}</div>
        @endif
    </div>
</div>

@endsection
