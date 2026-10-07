# Release Checklist — HelpDesk AI v1.0.0

> Jalankan checklist ini berurutan sebelum setiap rilis. Tandai `[x]` bila lolos.

## 1. Version

- [ ] `VERSION` berisi versi rilis (saat ini `1.0.0`)
- [ ] `config/helpdesk.php` → `'version'` sama dengan isi `VERSION` (`1.0.0`)
- [ ] `docs/12-CHANGELOG.md` memiliki entri untuk versi ini
- [ ] `/health` menampilkan versi yang benar (dibaca dari file `VERSION`)

## 2. Tests

```bash
vendor/bin/pint --test
php artisan test        # sqlite :memory: (lihat phpunit.xml)
npm run build           # bundle frontend harus sukses
```

- [ ] `composer validate --strict` lolos
- [ ] `vendor/bin/pint --test` lolos (Laravel preset)
- [ ] `php artisan test` hijau (auth, IDOR/ownership, attachments, automation, AI fallback, email piping, API keys)
- [ ] `npm ci && npm run build` sukses tanpa error

## 3. Security grep

- [ ] Tidak ada secret di repo: `grep -r "sk-\|AKIA\|BEGIN PRIVATE KEY" --include="*.php" --include="*.env" .` (kecuali placeholder)
- [ ] Kunci API/AI terenkripsi di rest (`Crypt`, `*_encrypted`), tidak pernah di-log / dikembalikan di response / muncul di error
- [ ] Attachment: private storage, generated filename, MIME + extension allowlist, per-download authorization
- [ ] Internal notes tidak bocor ke customer (web + API memfilter `is_internal`)
- [ ] Throttle aktif: auth & API login (`THROTTLE_API_LOGIN`), AI (`THROTTLE_AI`), widget (`THROTTLE_WIDGET`), contact
- [ ] Impersonation: rank check (actor > target), audit log, stop flow, cegah nested impersonation
- [ ] 2FA/TOTP + recovery codes aktif untuk akun staff

## 4. Migration (fresh + seed)

```bash
php artisan migrate:fresh --force
php artisan db:seed --force
```

- [ ] `migrate:fresh` sukses dari nol (MySQL 8.0+ DAN sqlite)
- [ ] `db:seed` sukses: `RolesAndPermissionsSeeder` (5 role, 32 permission), akun demo admin/manager/agent/customer
- [ ] Tidak ada migrasi yang merusak data (cek `doctrine/dbal` bila ada rename/drop kolom)

## 5. Build

- [ ] `npm ci && npm run build` → `public/build` terisi (manifest + hashed assets)
- [ ] Tidak ada aset build/runtime yang dimuat dari CDN (Tabler/Inter/ApexCharts via npm lokal, `grep -ri "cdn\.\|unpkg\|jsdelivr" resources/views | grep -v "cdn-cgi"` kosong)
- [ ] `php artisan storage:link` ada di target deploy
- [ ] Production optimize: `config:cache && route:cache && view:cache && event:cache`

## 6. License

- [ ] File `LICENSE` ada (proprietary commercial)
- [ ] `composer.json` → `"license": "proprietary"`
- [ ] Footer aplikasi memuat atribusi proprietary (© Lindu Cipta Pranayama / HelpdeskAI)
- [ ] Pairing wizard `/__pair` + halaman **Admin → License Status** dapat dibuka
- [ ] `THIRD_PARTY_LICENSES.md` mutakhir (`composer licenses` + `npm list --depth=0`)

## 7. Documentation

- [ ] `docs/00-README.md` … `docs/14-WEBHOOKS.md` konsisten dengan kode (tidak ada klaim fiktif)
- [ ] `docs/06-API.md` hanya memuat endpoint yang ada di `routes/api.php`
- [ ] `docs/13-ACCESS-MATRIX.md` cocok dengan `database/seeders/RolesAndPermissionsSeeder.php`
- [ ] `README.md`: positioning, fitur aktual, lisensi proprietary + kontak 081296052010, link ke docs

## 8. Backup

- [ ] `php artisan db:backup --keep=14` menulis `storage/app/backups/backup-*.sql` (MySQL via `mysqldump`; non-MySQL di-skip dengan warning)
- [ ] Retensi prune berjalan (hanya N file terbaru dipertahankan)
- [ ] Cara restore terdokumentasi (`docs/10-DEPLOYMENT.md`): restore SQL → restore `storage/app/public` → re-cache → restart worker
- [ ] File backup + `.env` ikut dalam rencana backup server (rsync/S3 snapshot)

## 9. Queue

- [ ] `QUEUE_CONNECTION=database` adalah path yang didukung default (migrasi `jobs` tersedia)
- [ ] Worker supervisor berjalan: `php artisan queue:work --sleep=3 --tries=3 --max-time=3600`
- [ ] Job async terdaftar: `DeliverWebhook`, `ClassifyTicketWithAi`, `AnalyzeTicketSentiment`, `SendWebPushNotification`, mail (`TicketCreatedMail`, `TicketReplyMail`)
- [ ] (Opsional) Redis: `QUEUE_CONNECTION=redis` + supervisor `queue:work` bila traffic tinggi; Horizon **tidak** diinstal default (butuh Redis)

## 10. Scheduler

```cron
* * * * * cd /var/www/helpdeskai && php artisan schedule:run >> /dev/null 2>&1
```

- [ ] Cron `schedule:run` per menit terpasang. Entries aktual (`routes/console.php`):

| Command | Jadwal |
|---|---|
| `sla:check` | hourly |
| `tickets:reminders` | daily 08:00 |
| `tickets:autoclose` | daily 03:00 |
| `maintenance:cleanup` | daily 04:00 |
| `db:backup` | daily 02:00 |
| `seo:indexnow` | daily 02:45 |

## 11. Health (`/health` + `/ready`)

- [ ] `GET /health` → `{status, app, version, time}` (version dari file `VERSION`)
- [ ] `GET /ready` → `{ready, checks: {database, cache, storage}}`, HTTP 503 bila belum ready
- [ ] Halaman admin **System Health** (`/admin/system-health`, permission `settings.manage`) dapat dibuka
- [ ] Monitor eksternal (UptimeRobot/Pingdom) menunjuk ke `/health` atau `/ready`

## 12. Assets (no CDN)

- [ ] `vite.config.js` + `package.json`: `@tabler/core`, `@fontsource/inter`, `apexcharts` via npm
- [ ] Blade tidak memuat `<link>`/`<script>` ke CDN eksternal untuk CSS/JS runtime
- [ ] PWA: `/manifest.json`, `/sw.js`, `/offline.html` reachable (dikecualikan dari `RequirePair`)

## 13. Environment (`.env.example` lengkap)

- [ ] Semua key yang dipakai kode ada di `.env.example` **tanpa secret asli**:

| Grup | Keys |
|---|---|
| App | `APP_KEY`, `APP_TIMEZONE`, `APP_LOCALE`, `APP_FALLBACK_LOCALE` |
| DB | `DB_CONNECTION`, `DB_URL`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` |
| Queue/Cache | `QUEUE_CONNECTION`, `CACHE_STORE`, `SESSION_DRIVER`, `REDIS_*` |
| Mail | `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_*` |
| Throttle | `THROTTLE_API`, `THROTTLE_API_LOGIN`, `THROTTLE_AI`, `THROTTLE_CONTACT`, `THROTTLE_WIDGET` |
| Realtime | `REVERB_APP_ID/KEY/SECRET/HOST/PORT/SCHEME`, `VITE_REVERB_*`, `BROADCAST_CONNECTION` |
| Push | `VAPID_SUBJECT`, `VAPID_PUBLIC_KEY`, `VAPID_PRIVATE_KEY` |
| License | `LICENSE_SERVER_URL`, `LICENSE_DEV_BYPASS`, `LICENSE_HEARTBEAT_INTERVAL`, `LICENSE_HEARTBEAT_GRACE` |
| SEO | `INDEXNOW_KEY` |
| Helpdesk | `HELPDESK_*` (prefix, defaults, SLA, AI/chat/KB/PSEO toggles) |
| Ops | `MYSQLDUMP_PATH` |

## 14. Changelog

- [ ] Entri versi baru di `docs/12-CHANGELOG.md` hanya mengklaim fitur yang terverifikasi via `grep` ke `app/` (lihat catatan verifikasi di tiap entri)
