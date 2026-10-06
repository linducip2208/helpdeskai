@extends('layouts.admin')
@section('title', 'License Status')
@section('content')

<div class="max-w-3xl mx-auto space-y-6">
    <div>
        <h2 class="text-2xl font-bold text-slate-900">License Status</h2>
        <p class="text-sm text-slate-500 mt-1">Status license whitelabel.co.id untuk domain ini.</p>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 p-6 space-y-4">
        <div class="flex items-center gap-3">
            @if($paired)
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-emerald-100 text-emerald-700">Paired &amp; Valid</span>
            @else
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-rose-100 text-rose-700">Not Paired / Invalid</span>
            @endif
            <span class="font-mono text-sm text-slate-600">{{ $domain }}</span>
        </div>

        @if($paired && $data)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4 border-t border-gray-100">
                <div>
                    <p class="text-xs text-slate-500">Product</p>
                    <p class="font-medium text-slate-900">{{ $data['product'] ?? $data['product_name'] ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500">Plan / Tier</p>
                    <p class="font-medium text-slate-900">{{ $data['plan'] ?? $data['tier'] ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500">Activated</p>
                    <p class="font-medium text-slate-900">{{ $data['activated_at'] ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500">Expires</p>
                    <p class="font-medium text-slate-900">{{ $data['expires_at'] ?? 'Lifetime' }}</p>
                </div>
                @if(! empty($data['licensee_email']))
                    <div>
                        <p class="text-xs text-slate-500">Licensee</p>
                        <p class="font-medium text-slate-900">{{ $data['licensee_email'] }}</p>
                    </div>
                @endif
                @if(! empty($data['activation_key']))
                    <div>
                        <p class="text-xs text-slate-500">Activation Key</p>
                        <p class="font-mono text-xs text-slate-700">{{ \Illuminate\Support\Str::mask($data['activation_key'], '*', 4, -4) }}</p>
                    </div>
                @endif
            </div>

            <details class="pt-4 border-t border-gray-100">
                <summary class="text-sm text-slate-500 cursor-pointer">Raw payload</summary>
                <pre class="bg-gray-50 text-xs p-3 rounded mt-2 overflow-x-auto">{{ json_encode($data, JSON_PRETTY_PRINT) }}</pre>
            </details>
        @else
            <div class="pt-4 border-t border-gray-100 space-y-3">
                <p class="text-sm text-slate-600">License lock file missing, expired, atau invalid untuk domain <code class="font-mono">{{ $domain }}</code>.</p>
                <a href="{{ url('/__pair') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg">Open Pairing Wizard &rarr;</a>
            </div>
        @endif
    </div>

    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-xs text-amber-900">
        <p class="font-semibold mb-1">Tentang License v3</p>
        <p>Lock file di <code class="font-mono">.license.lock</code> (encrypted AES-256-GCM + RSA-signed payload). Heartbeat ke marketplace tiap 24 jam dengan grace 7 hari kalau marketplace offline.</p>
        <p class="mt-1">Marketplace: <a href="{{ $marketplaceUrl }}" target="_blank" class="underline">{{ $marketplaceUrl ?: 'belum diset di .env' }}</a></p>
    </div>
</div>

@endsection
