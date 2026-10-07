# 13 — Access Matrix (Role × Aksi)

> Sumber kebenaran: `database/seeders/RolesAndPermissionsSeeder.php` (32 permission, 5 role).
> Dokumen ini adalah cerminan seeder tersebut — bila seeder berubah, perbarui tabel ini.
> Terakhir disinkronkan: 2026-10-06.

## Legenda

- ✅ = diberikan via `syncPermissions` / `hasRole` / ownership check
- ❌ = tidak diberikan (403 / diblokir middleware / difilter query)
- `(own)` = hanya pada milik sendiri (ownership check di controller, bukan permission Spatie)

## Matriks

| Aksi (permission) | super-admin | admin | manager | agent | customer |
|---|---|---|---|---|---|
| Lihat tiket (`tickets.view`) | ✅ | ✅ | ✅ | ✅ | ✅ (own) |
| Buat tiket (`tickets.create`) | ✅ | ✅ | ✅ | ✅ | ✅ (own) |
| Ubah tiket (`tickets.update`) | ✅ | ✅ | ✅ | ✅ | ✅ subject saja (own) |
| Hapus tiket (`tickets.delete`) | ✅ | ✅ | ✅ | ❌ | ❌ |
| Assign tiket (`tickets.assign`) | ✅ | ✅ | ✅ | ✅ | ❌ |
| Balas tiket (`tickets.reply`) | ✅ | ✅ | ✅ | ✅ | ✅ (own, non-internal) |
| Internal note (`tickets.internal_note`) | ✅ | ✅ | ✅ | ✅ | ❌ (difilter dari response) |
| Close tiket (`tickets.close`) | ✅ | ✅ | ✅ | ✅ | ❌ |
| Reopen tiket (`tickets.reopen`) | ✅ | ✅ | ✅ | ✅ | ❌ |
| Merge tiket (`tickets.merge`) | ✅ | ✅ | ✅ | ❌ | ❌ |
| Lihat customer (`customers.view`) | ✅ | ✅ | ✅ | ✅ | ❌ |
| Ubah customer (`customers.update`) | ✅ | ✅ | ✅ | ❌ | ❌ (profil sendiri via akun) |
| Lihat AI (`ai.view`) | ✅ | ✅ | ✅ | ✅ | ❌ |
| Pakai AI (`ai.use`) | ✅ | ✅ | ✅ | ✅ | ❌ |
| Konfigurasi AI (`ai.configure`) | ✅ | ✅ | ❌ | ❌ | ❌ |
| Lihat SLA (`sla.view`) | ✅ | ✅ | ✅ | ✅ | ❌ |
| Kelola SLA (`sla.manage`) | ✅ | ✅ | ✅ | ❌ | ❌ |
| Lihat automation (`automation.view`) | ✅ | ✅ | ✅ | ❌ | ❌ |
| Kelola automation (`automation.manage`) | ✅ | ✅ | ✅ | ❌ | ❌ |
| Lihat laporan (`reports.view`) | ✅ | ✅ | ✅ | ✅ | ❌ |
| Kelola settings (`settings.manage`) | ✅ | ✅ | ❌ | ❌ | ❌ |
| Kelola API (`api.manage`) | ✅ | ✅ | ❌ | ❌ | ❌ |
| Lihat audit (`audit.view`) | ✅ | ✅ | ✅ | ❌ | ❌ |
| Impersonate user (`users.impersonate`) | ✅ | ✅ | ❌ | ❌ | ❌ |
| Kelola departments (`departments.manage`) | ✅ | ✅ | ✅ | ❌ | ❌ |
| Kelola categories (`categories.manage`) | ✅ | ✅ | ✅ | ❌ | ❌ |
| Kelola custom fields (`custom_fields.manage`) | ✅ | ✅ | ✅ | ❌ | ❌ |
| Kelola email (`email.manage`) | ✅ | ✅ | ✅ | ❌ | ❌ |
| Kelola webhooks (`webhooks.manage`) | ✅ | ✅ | ✅ | ❌ | ❌ |
| Kelola users (`manage_users`) | ✅ | ✅ | ✅ | ❌ | ❌ |
| Kelola settings app (`manage_settings`) | ✅ | ✅ | ❌ | ❌ | ❌ |
| Kelola knowledge (`manage_knowledge`) | ✅ | ✅ | ✅ | ❌ | ❌ |
| System health page | ✅ | ✅ | ❌ (`settings.manage`) | ❌ | ❌ |
| CSAT rating | — | — | — | — | ✅ (own, sekali per tiket) |

Catatan: role `customer` dibuat tanpa permission (`Role::firstOrCreate(['name' => 'customer'])` tanpa `syncPermissions`). Semua akses customer ditegakkan lewat ownership check di controller (`authorizeTicket`, `baseQuery` memfilter `user_id`), bukan via permission.

## Enforcement (bagaimana matriks ditegakkan)

### 1. Middleware `permission:*` (Spatie, web admin)
Rute staff memakai `->middleware('permission:tickets.view')` dsb. (lihat `routes/web.php` grup `admin.`).
Contoh:
- `POST /admin/tickets/{ticket}/merge` → `permission:tickets.merge`
- `POST /admin/users/{user}/impersonate` → `permission:users.impersonate`
- `GET /admin/system-health` → `permission:settings.manage`
- AI staff (`suggest`, `ai-summary`, `ai-similar`, `ai-recommend`, `ai-answer`) → `permission:ai.use`

### 2. Middleware `staff` (`EnsureStaff`)
Grup `admin.` juga dibungkus `staff` — hanya user dengan role staff (super-admin/admin/manager/agent) yang lolos; customer ditolak meski sudah login.

### 3. API scopes (API key + Sanctum)
- `api.auth` (`ApiAuthenticate`): token berisi `|` → Sanctum; selain itu → `ApiKeyAuth` (key = hex polos via `Bearer` / `X-API-Key`).
- Scope API key per HTTP method (`ApiKeyAuth::hasScope`): `GET/HEAD/OPTIONS → read`, `POST/PUT/PATCH → read-write`, `DELETE → full`. Level: `read(1) < read-write(2) < full(3)`.
- Key disimpan sebagai SHA-256 hash, dicek `is_active` + `expires_at`; tiap pakai update `usage_count` + `last_used_at`.
- Batasan tambahan di controller meski token valid:
  - `Api\TicketController::destroy` → hanya role `admin`/`manager`.
  - `Api\TicketController::update` → non-staff hanya boleh ubah `subject` milik sendiri.
  - `Api\TicketController::show` → staff, pemilik, atau assignee; replies internal disembunyikan dari non-staff.
  - `Api\AiController` (`classify/suggest/sentiment`) → staff-only (`ensureStaff`: admin/manager/agent).
  - `Api\UserController::index` → `admin`/`manager`; `show` → `admin`/`manager` atau diri sendiri. Secrets 2FA selalu di-hidden.
- Idempotency: `Idempotency-Key` pada mutasi tickets & conversations (replay aman, flag `idempotent_replay`).

### 4. Impersonation ranks
`Admin\UserController`: rank `customer=1 < agent=2 < manager=3 < admin=4 < super-admin=5`; actor hanya boleh mengimpersonasi target dengan rank **lebih rendah**. Tercatat di audit log, ada alur stop, dan nested impersonation dicegah.

### 5. Customer isolation (IDOR)
- Web + API memfilter tiket/conversation/attachment ke milik sendiri untuk role customer.
- Test negatif: customer A tidak bisa akses tiket/attachment/conversation customer B; internal-note tidak bocor (web + API); email-reply spoofing ditolak.
