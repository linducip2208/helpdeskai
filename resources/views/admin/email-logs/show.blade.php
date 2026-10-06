@extends('layouts.admin')
@section('title', 'Email Log Detail')
@section('content')

<div class="max-w-4xl mx-auto space-y-6">
    <div>
        <a href="{{ route('admin.email-logs.index') }}" class="text-sm text-indigo-600 hover:text-indigo-700">&larr; Back to Email Logs</a>
        <h2 class="text-2xl font-bold text-slate-900 mt-1">{{ $log->subject }}</h2>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 p-6 space-y-3 text-sm">
        <div class="grid grid-cols-2 gap-3">
            <div>
                <p class="text-xs text-slate-500">From</p>
                <p class="font-medium">{{ $log->from_name }} &lt;{{ $log->from_email }}&gt;</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">To</p>
                <p class="font-medium">{{ $log->to_email }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">Direction / Status</p>
                <p><span class="text-xs px-2 py-0.5 bg-slate-100 rounded">{{ $log->direction }}</span> <span class="text-xs px-2 py-0.5 bg-slate-100 rounded">{{ $log->status }}</span></p>
            </div>
            <div>
                <p class="text-xs text-slate-500">Received</p>
                <p class="font-medium">{{ $log->created_at?->format('Y-m-d H:i:s') }}</p>
            </div>
        </div>

        @if($log->ticket)
            <div class="pt-3 border-t border-gray-100">
                <p class="text-xs text-slate-500">Linked Ticket</p>
                <a href="{{ route('admin.tickets.show', $log->ticket) }}" class="text-indigo-600 hover:text-indigo-700 font-mono">{{ $log->ticket->uid }}</a>
            </div>
        @endif

        @if($log->error)
            <div class="pt-3 border-t border-gray-100">
                <p class="text-xs text-rose-500">Error</p>
                <pre class="bg-rose-50 text-rose-800 text-xs p-3 rounded mt-1 whitespace-pre-wrap">{{ $log->error }}</pre>
            </div>
        @endif
    </div>

    <div class="bg-white rounded-xl border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-slate-900 mb-3">Body</h3>
        @if($log->body_html)
            <div class="prose prose-sm max-w-none border border-gray-100 rounded p-4 bg-gray-50">{!! $log->body_html !!}</div>
        @endif
        @if($log->body_plain)
            <pre class="bg-gray-50 text-xs p-4 rounded mt-3 whitespace-pre-wrap">{{ $log->body_plain }}</pre>
        @endif
    </div>

    @if($log->headers)
        <details class="bg-white rounded-xl border border-gray-100 p-6">
            <summary class="text-sm font-semibold text-slate-900 cursor-pointer">Headers ({{ count((array) $log->headers) }})</summary>
            <pre class="bg-gray-50 text-xs p-4 rounded mt-3 whitespace-pre-wrap">{{ json_encode($log->headers, JSON_PRETTY_PRINT) }}</pre>
        </details>
    @endif

    <form method="POST" action="{{ route('admin.email-logs.destroy', $log) }}" onsubmit="return confirm('Delete this email log?')" class="text-right">
        @csrf @method('DELETE')
        <button class="text-sm text-rose-600 hover:text-rose-700">Delete this log</button>
    </form>
</div>

@endsection
