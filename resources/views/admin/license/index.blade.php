@extends('layouts.admin')
@section('title', 'License Status')
@section('content')

<div class="row justify-content-center">
    <div class="col-12 col-lg-10">
        <p class="text-muted mb-3">Status license whitelabel.co.id untuk domain ini.</p>

        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 mb-3">
                    @if($paired)
                        <span class="badge bg-green-lt">Paired &amp; Valid</span>
                    @else
                        <span class="badge bg-red-lt">Not Paired / Invalid</span>
                    @endif
                    <span><code>{{ $domain }}</code></span>
                </div>

                @if($paired && $data)
                    <div class="row g-2 border-top pt-3">
                        <div class="col-md-6">
                            <div class="text-muted">Product</div>
                            <div>{{ $data['product'] ?? $data['product_name'] ?? '—' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted">Plan / Tier</div>
                            <div>{{ $data['plan'] ?? $data['tier'] ?? '—' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted">Activated</div>
                            <div>{{ $data['activated_at'] ?? '—' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted">Expires</div>
                            <div>{{ $data['expires_at'] ?? 'Lifetime' }}</div>
                        </div>
                        @if(! empty($data['licensee_email']))
                            <div class="col-md-6">
                                <div class="text-muted">Licensee</div>
                                <div>{{ $data['licensee_email'] }}</div>
                            </div>
                        @endif
                        @if(! empty($data['activation_key']))
                            <div class="col-md-6">
                                <div class="text-muted">Activation Key</div>
                                <div><code>{{ \Illuminate\Support\Str::mask($data['activation_key'], '*', 4, -4) }}</code></div>
                            </div>
                        @endif
                    </div>

                    <details class="mt-3 border-top pt-3">
                        <summary class="text-muted">Raw payload</summary>
                        <pre class="mt-2">{{ json_encode($data, JSON_PRETTY_PRINT) }}</pre>
                    </details>
                @else
                    <div class="border-top pt-3">
                        <p class="text-muted">License lock file missing, expired, atau invalid untuk domain <code>{{ $domain }}</code>.</p>
                        <a href="{{ url('/__pair') }}" class="btn btn-primary">Open Pairing Wizard &rarr;</a>
                    </div>
                @endif
            </div>
        </div>

        <div class="alert alert-warning">
            <p class="mb-1"><strong>Tentang License v3</strong></p>
            <p class="mb-1">Lock file di <code>.license.lock</code> (encrypted AES-256-GCM + RSA-signed payload). Heartbeat ke marketplace tiap 24 jam dengan grace 7 hari kalau marketplace offline.</p>
            <p class="mb-0">Marketplace: <a href="{{ $marketplaceUrl }}" target="_blank">{{ $marketplaceUrl ?: 'belum diset di .env' }}</a></p>
        </div>
    </div>
</div>

@endsection
