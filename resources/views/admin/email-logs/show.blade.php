@extends('layouts.admin')
@section('title', 'Email Log Detail')
@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-10">
        <div class="mb-3">
            <a href="{{ route('admin.email-logs.index') }}">&larr; Back to Email Logs</a>
            <h2 class="page-title mt-1">{{ $log->subject }}</h2>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-6 mb-3">
                        <p class="form-label">From</p>
                        <p><strong>{{ $log->from_name }} &lt;{{ $log->from_email }}&gt;</strong></p>
                    </div>
                    <div class="col-sm-6 mb-3">
                        <p class="form-label">To</p>
                        <p><strong>{{ $log->to_email }}</strong></p>
                    </div>
                    <div class="col-sm-6 mb-3">
                        <p class="form-label">Direction / Status</p>
                        <p><span class="badge bg-secondary-lt">{{ $log->direction }}</span> <span class="badge bg-secondary-lt">{{ $log->status }}</span></p>
                    </div>
                    <div class="col-sm-6 mb-3">
                        <p class="form-label">Received</p>
                        <p><strong>{{ wib($log->created_at) }}</strong></p>
                    </div>
                </div>

                @if($log->ticket)
                    <hr>
                    <p class="form-label">Linked Ticket</p>
                    <a href="{{ route('admin.tickets.show', $log->ticket) }}" class="font-monospace">{{ $log->ticket->uid }}</a>
                @endif

                @if($log->error)
                    <hr>
                    <p class="form-label text-danger">Error</p>
                    <pre class="alert alert-danger">{{ $log->error }}</pre>
                @endif
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Body</h3>
            </div>
            <div class="card-body">
                @if($log->body_html)
                    <div class="alert alert-warning">HTML body disembunyikan demi keamanan. Lihat versi teks di bawah.</div>
                @endif
                @if($log->body_plain)
                    <pre class="card bg-secondary-lt p-3 small">{{ $log->body_plain }}</pre>
                @endif
            </div>
        </div>

        @if($log->headers)
            <details class="card mb-3">
                <summary class="card-header card-title cursor-pointer">Headers ({{ count((array) $log->headers) }})</summary>
                <div class="card-body">
                    <pre class="small">{{ json_encode($log->headers, JSON_PRETTY_PRINT) }}</pre>
                </div>
            </details>
        @endif

        <form method="POST" action="{{ route('admin.email-logs.destroy', $log) }}" onsubmit="return confirm('Delete this email log?')" class="text-end">
            @csrf @method('DELETE')
            <button class="btn btn-danger btn-sm">Delete this log</button>
        </form>
    </div>
</div>
@endsection
