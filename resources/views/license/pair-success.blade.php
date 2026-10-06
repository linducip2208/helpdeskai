<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="refresh" content="4; url=/">
<title>Aktivasi Berhasil</title>
<script>
    try {
        document.documentElement.setAttribute('data-bs-theme', localStorage.getItem('helpdeskai-theme') || 'light');
    } catch (e) {}
</script>
@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="d-flex flex-column">
<div class="page page-center">
    <div class="container container-tight py-4">
        <div class="card card-md">
            <div class="card-body text-center py-4">
                <span class="avatar avatar-xl bg-green-lt mb-3">
                    <svg width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 13l4 4L19 7"/></svg>
                </span>
                <h1 class="h2">Aktivasi Berhasil</h1>
                <p class="text-muted">Aplikasi siap digunakan.</p>
            </div>
            <div class="card-body">
                <div class="card bg-surface-secondary mb-3">
                    <div class="card-body">
                        <div class="subheader mb-1">Produk</div>
                        <div class="fw-bold fs-3">{{ $data['product']['name'] ?? 'Aplikasi' }}</div>
                        <div class="text-muted small">Versi v{{ $data['product']['version'] ?? '1.0.0' }}</div>
                    </div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-sm-6">
                        <div class="card bg-surface-secondary h-100">
                            <div class="card-body">
                                <div class="subheader mb-1">Domain Terkunci</div>
                                <div class="font-monospace text-break">{{ $data['domain'] ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                    @if(!empty($data['license']['support_until']))
                    <div class="col-sm-6">
                        <div class="card bg-surface-secondary h-100">
                            <div class="card-body">
                                <div class="subheader mb-1">Support Aktif Sampai</div>
                                <div>{{ wib($data['license']['support_until'], 'd F Y', false) }}</div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <div class="alert alert-success" role="alert">
                    <div><strong>Konfirmasi:</strong> nama produk di atas harus cocok dengan yang kamu beli. Kalau salah, klik "Revoke" di marketplace dan re-pair dengan key yang benar.</div>
                </div>

                <a href="/" class="btn btn-dark w-100">
                    Masuk ke Aplikasi
                </a>

                <p class="text-center text-muted small mt-2 mb-0">
                    Auto-redirect dalam 4 detik...
                </p>
            </div>
        </div>
    </div>
</div>

</body>
</html>
