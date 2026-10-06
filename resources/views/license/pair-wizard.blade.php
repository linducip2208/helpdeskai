<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Aktivasi Aplikasi</title>
@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="d-flex flex-column">
<div class="page page-center">
    <div class="container container-tight py-4">
        <div class="text-center mb-4">
            <span class="avatar avatar-xl bg-indigo-lt mb-3">
                <svg width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </span>
            <h1 class="h2">Aktivasi Aplikasi</h1>
            <p class="text-muted">Aplikasi ini perlu di-aktivasi sebelum bisa digunakan.</p>
        </div>

        <form method="POST" action="/__pair" class="card card-md" autocomplete="off">
            @csrf
            <div class="card-body">
                @if($error)
                    <div class="alert alert-danger" role="alert">
                        <div>{{ $error }}</div>
                    </div>
                @endif

                <div class="mb-3">
                    <label class="form-label">Domain Terdeteksi</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>
                        <input type="text" class="form-control font-monospace" value="{{ $domain }}" disabled readonly>
                        <span class="input-group-text text-muted">auto</span>
                    </div>
                    <div class="form-hint">Domain di-deteksi otomatis dari browser kamu — tidak bisa diubah manual.</div>
                </div>

                <div class="mb-3">
                    <label for="activation_key" class="form-label">Activation Key</label>
                    <input
                        type="text"
                        name="activation_key"
                        id="activation_key"
                        value="{{ $old_key }}"
                        placeholder="XXXXX-XXXXX-XXXXX-XXXXX"
                        autocomplete="off"
                        autofocus
                        class="form-control form-control-lg font-monospace text-center text-uppercase"
                        oninput="this.value = this.value.toUpperCase()"
                    >
                    <div class="form-hint">Format: 4 grup × 5 karakter, dipisahkan tanda hubung.</div>
                </div>

                <div class="form-footer">
                    <button type="submit" id="submitBtn" class="btn btn-primary w-100">
                        <span id="submitText">Aktivasi</span>
                        <svg id="submitIcon" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </div>
            </div>
            <div class="card-footer text-center text-muted small">
                Belum punya activation key?
                <a href="{{ $marketplace_url }}/user/licenses" target="_blank" rel="noopener">
                    Buka marketplace
                </a>
                — login → /user/licenses → copy key dari kartu lisensimu.
            </div>
        </form>

        <p class="text-center text-muted small mt-3">
            Setelah aktivasi, file <code>.license.lock</code> akan dibuat otomatis. Domain ter-bind permanen sampai di-revoke dari marketplace.
        </p>
    </div>
</div>

<script>
  document.querySelector('form').addEventListener('submit', function() {
    const btn  = document.getElementById('submitBtn');
    const text = document.getElementById('submitText');
    btn.disabled = true;
    text.textContent = 'Memvalidasi key...';
  });
</script>

</body>
</html>
