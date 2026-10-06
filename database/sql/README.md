# Database SQL Dump

File `.sql` siap-import ke MySQL/MariaDB via phpMyAdmin, MySQL Workbench, atau CLI. Berguna untuk hosting yang tidak support `php artisan migrate`.

## File

| File | Isi | Ukuran |
|------|-----|--------|
| `helpdeskai-full.sql` | Schema 39 tabel + seluruh data demo (~7.500 record) | ~2 MB |

## Cara Import

### Via phpMyAdmin (cPanel / shared hosting)

1. Login phpMyAdmin
2. Buat database baru, contoh `helpdeskai`, collation `utf8mb4_unicode_ci`
3. Pilih database tersebut → tab **Import**
4. Pilih file `helpdeskai-full.sql` → klik **Go**
5. Tunggu sampai selesai (biasanya < 30 detik)

### Via CLI

```bash
mysql -u root -p -e "CREATE DATABASE helpdeskai CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p helpdeskai < helpdeskai-full.sql
```

### Via MySQL Workbench

`Server → Data Import → Import from Self-Contained File → pilih helpdeskai-full.sql → Start Import`

## Setelah Import

1. Update `.env` Laravel arahkan ke DB yang baru:
   ```env
   DB_DATABASE=helpdeskai
   DB_USERNAME=...
   DB_PASSWORD=...
   ```
2. Cache config: `php artisan config:cache`
3. Buka aplikasi — semua akun demo & data sudah aktif.

## Akun Demo (semua password: `password`)

| Email | Role |
|-------|------|
| admin@helpdesk.test | admin |
| agent@helpdesk.test | agent |
| customer@helpdesk.test | customer |
| customer0@demo.test → customer299@demo.test | customer (300 akun) |
| agent0@demo.test → agent39@demo.test | agent (40 akun) |

## Re-generate Dump

Setelah update data lokal, regenerate dengan:

```bash
mysqldump -u root \
  --default-character-set=utf8mb4 \
  --set-gtid-purged=OFF \
  --column-statistics=0 \
  --add-drop-table \
  --routines --triggers \
  --single-transaction --quick \
  helpdeskai > database/sql/helpdeskai-full.sql
```

## Catatan Production

- File ini berisi **data demo (fake)** — JANGAN langsung import ke production tanpa hapus data demo dulu, atau:
  - Import → login → hapus user `*@demo.test` & `*@helpdesk.test` via admin panel, atau
  - Edit `.sql` file: hapus `INSERT INTO users` rows dengan email pattern tersebut, plus tabel terkait (`tickets`, `ticket_replies`, dll.)
- Untuk production murni schema-only, lebih baik pakai `php artisan migrate` (tidak include data demo).
- API keys & AI provider credentials di dump ini kosong / dummy — admin perlu re-input via `/admin/ai-providers` setelah import.
