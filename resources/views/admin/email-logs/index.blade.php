@extends('layouts.admin')
@section('title', 'Email Logs')
@section('content')

<div class="space-y-6">
    <div>
        <h2 class="text-2xl font-bold text-slate-900">Email Logs</h2>
        <p class="text-sm text-slate-500 mt-1">Inbound (email piping) &amp; outbound notification email. Klik baris untuk detail body + headers.</p>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
        <div class="bg-white rounded-xl border border-gray-100 p-4">
            <p class="text-xs text-slate-500">Inbound Received</p>
            <p class="text-2xl font-bold text-slate-900">{{ number_format($totals['received']) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4">
            <p class="text-xs text-slate-500">Outbound Sent</p>
            <p class="text-2xl font-bold text-indigo-600">{{ number_format($totals['sent']) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4">
            <p class="text-xs text-slate-500">Failed</p>
            <p class="text-2xl font-bold text-rose-600">{{ number_format($totals['failed']) }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="bg-white rounded-xl border border-gray-100 p-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search subject/email..." class="rounded-lg border-gray-200 text-sm">
            <select name="direction" class="rounded-lg border-gray-200 text-sm">
                <option value="">All directions</option>
                <option value="inbound" {{ request('direction') == 'inbound' ? 'selected' : '' }}>Inbound</option>
                <option value="outbound" {{ request('direction') == 'outbound' ? 'selected' : '' }}>Outbound</option>
            </select>
            <select name="status" class="rounded-lg border-gray-200 text-sm">
                <option value="">All status</option>
                @foreach(['received', 'parsed', 'failed', 'sent', 'queued'] as $s)
                    <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <button class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm rounded-lg">Filter</button>
                <a href="{{ route('admin.email-logs.index') }}" class="px-4 py-2 border border-gray-200 text-slate-700 text-sm rounded-lg hover:bg-gray-50">Reset</a>
            </div>
        </div>
    </form>

    <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-3 text-left">Time</th>
                        <th class="px-4 py-3 text-left">Direction</th>
                        <th class="px-4 py-3 text-left">From → To</th>
                        <th class="px-4 py-3 text-left">Subject</th>
                        <th class="px-4 py-3 text-left">Ticket</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-xs text-slate-500 whitespace-nowrap">{{ $log->created_at?->format('Y-m-d H:i') }}</td>
                            <td class="px-4 py-3"><span class="text-xs px-2 py-0.5 rounded {{ $log->direction === 'inbound' ? 'bg-blue-50 text-blue-700' : 'bg-emerald-50 text-emerald-700' }}">{{ $log->direction }}</span></td>
                            <td class="px-4 py-3 text-xs">
                                <p class="text-slate-900">{{ \Illuminate\Support\Str::limit($log->from_email, 30) }}</p>
                                <p class="text-slate-500">→ {{ \Illuminate\Support\Str::limit($log->to_email, 30) }}</p>
                            </td>
                            <td class="px-4 py-3 truncate max-w-xs">{{ \Illuminate\Support\Str::limit($log->subject, 50) }}</td>
                            <td class="px-4 py-3 text-xs">
                                @if($log->ticket)
                                    <a href="{{ route('admin.tickets.show', $log->ticket) }}" class="text-indigo-600 hover:text-indigo-700 font-mono">{{ $log->ticket->uid }}</a>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @php
                                    $statusColor = ['received' => 'slate', 'parsed' => 'emerald', 'failed' => 'rose', 'sent' => 'emerald', 'queued' => 'amber'][$log->status] ?? 'slate';
                                @endphp
                                <span class="text-xs px-2 py-0.5 bg-{{ $statusColor }}-50 text-{{ $statusColor }}-700 rounded">{{ $log->status }}</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.email-logs.show', $log) }}" class="text-xs text-indigo-600 hover:text-indigo-700">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-12 text-center text-slate-400">No email logs yet. Email piping akan otomatis ngisi tabel ini saat ada email masuk.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $logs->links() }}</div>
        @endif
    </div>
</div>

@endsection
