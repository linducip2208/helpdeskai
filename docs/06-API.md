# 06 — REST API Documentation

> Machine-readable spec: `public/openapi.json`, regenerated from actual routes with `php artisan openapi:generate`. Never hand-edited.

## Overview

HelpDesk AI menyediakan REST API berjumlah **24 route** (lihat `routes/api.php` sebagai sumber kebenaran).
Semua endpoint mengembalikan JSON dan memakai autentikasi **Bearer token** via Laravel Sanctum **atau** scoped API key.

> Dokumen ini hanya memuat endpoint yang benar-benar ada di `routes/api.php`.

---

## Authentication

### Login (Sanctum token)

```http
POST /api/login
Content-Type: application/json

{
  "email": "agent@helpdeskai.test",
  "password": "password",
  "device_name": "mobile-app"
}
```

**Response** `200` (`{success, data, message}` envelope):

```json
{
  "success": true,
  "data": {
    "user": { "id": 2, "name": "Sarah Agent", "email": "agent@helpdeskai.test" },
    "token": "1|aBcDeFgHiJkLmNoP..."
  },
  "message": "Authenticated."
}
```

Throttle: `THROTTLE_API_LOGIN` (default 10/menit).

### Request terautentikasi

```http
Authorization: Bearer <sanctum-token-atau-api-key>
```

Alternatif yang didukung kode (`ApiAuthenticate` / `ApiKeyAuth`):

- `X-API-Key: <api-key>` header

> Catatan: query param `?api_key=` **tidak** didukung kode — jangan dipakai.

### Logout & profil

```http
POST /api/logout   # auth: api.auth — mencabut current Sanctum token
GET  /api/me       # auth: api.auth — user + roles
```

```json
{ "success": true, "data": { "id": 2, "name": "Sarah Agent", "roles": [...] }, "message": "Profile retrieved." }
```

### Scoped API keys

API key dibuat lewat panel admin / dashboard user. Disimpan sebagai **SHA-256 hash**, hanya ditampilkan sekali saat dibuat.
Scope ditegakkan per HTTP method (`ApiKeyAuth::hasScope`):

| Method | Scope minimal |
|---|---|
| `GET`, `HEAD`, `OPTIONS` | `read` |
| `POST`, `PUT`, `PATCH` | `read-write` |
| `DELETE` | `full` |

Level: `read(1) < read-write(2) < full(3)`. Key expired / nonaktif / scope kurang → `401` / `403` envelope `{success: false, message, errors: []}`.
Setiap pemakaian update `usage_count` + `last_used_at`.

---

## Rate Limiting

Batas default dari `config/rate-limits.php` (dapat diubah via env `THROTTLE_*`):

| Scope | Default/menit | Env |
|---|---|---|
| API umum | 120 | `THROTTLE_API` |
| API login | 10 | `THROTTLE_API_LOGIN` |
| AI | 30 | `THROTTLE_AI` |
| Contact (web) | 10 | `THROTTLE_CONTACT` |
| Widget (web) | 5 | `THROTTLE_WIDGET` |

Saat terlampaui → HTTP `429`.

---

## Response Format

### Envelope standar `{success, data, message}`

Dipakai oleh: auth, tickets, conversations, AI, analytics, users.

```json
{
  "success": true,
  "data": { "id": 1, "uid": "TKT-A3B9X", "subject": "Login issue", "status": "open" },
  "message": "Ticket retrieved."
}
```

Error validasi → `422`:

```json
{
  "message": "The subject field is required. (and 1 more error)",
  "errors": { "subject": ["The subject field is required."] }
}
```

API key salah/expired → `401`; scope kurang / bukan staff → `403`:

```json
{ "success": false, "data": null, "message": "API key scope insufficient for this action." }
```

AI provider down → `200` vs `503` via `AiController::envelope`:

```json
{ "success": false, "data": null, "message": "AI provider unavailable. Please try again later." }
```

### Envelope

Semua endpoint memakai envelope `{success, data, message}` — termasuk knowledge endpoints dan `GET /api/users/{user}`.

---

## Auth Endpoints

| Method | URL | Auth | Keterangan |
|---|---|---|---|
| POST | `/api/login` | publik (throttle `api_login`) | body: `email`, `password`, `device_name?` → `{user, token}` |
| POST | `/api/logout` | `api.auth` | cabut current Sanctum token |
| GET | `/api/me` | `api.auth` | user + roles |

---

## Ticket Endpoints

`Route::apiResource('tickets', TicketController::class)` + middleware `idempotency` (header `Idempotency-Key` opsional; replay mengembalikan response tersimpan + flag `idempotent_replay`).

| Method | URL | Auth |
|---|---|---|
| GET | `/api/tickets` | `api.auth` |
| POST | `/api/tickets` | `api.auth` |
| GET | `/api/tickets/{ticket}` | `api.auth` (staff, pemilik, atau assignee) |
| PUT/PATCH | `/api/tickets/{ticket}` | `api.auth` |
| DELETE | `/api/tickets/{ticket}` | `api.auth` + role `admin`/`manager` |

### List Tickets

```http
GET /api/tickets?status=open&priority=high&assigned_to=2&per_page=25
```

Filter aktual (`Api\TicketController::index`): `status`, `priority`, `assigned_to`. Non-staff otomatis difilter ke tiket milik sendiri. Paginasi `per_page` (default 25, maks 100).

**Response** `200`:

```json
{
  "success": true,
  "data": { "current_page": 1, "data": [ { "id": 1, "uid": "TKT-A3B9X", "subject": "...", "status": "open" } ], "total": 1 },
  "message": "Tickets retrieved."
}
```

### Create Ticket

```http
POST /api/tickets
Content-Type: application/json

{
  "subject": "Cannot login to dashboard",
  "body": "I'm getting a 500 error when trying to access my account.",
  "department_id": 1,
  "category_id": 3,
  "priority": "high"
}
```

Wajib: `subject` (maks 255), `body`, `department_id` (harus ada di `departments`). Opsional: `category_id`, `priority` (`low|medium|high|urgent`, default `medium`). `user_id` diisi dari user terautentikasi. → `201 Created`, `"message": "Ticket created."`.

### Get Ticket

```http
GET /api/tickets/{ticket}
```

Memuat `user, assignedTo, department, category, replies.user, attachments`. Untuk non-staff, replies dengan `is_internal = true` disembunyikan. → `"message": "Ticket retrieved."`.

### Update Ticket

- **Non-staff** (hanya tiket milik sendiri): boleh ubah `subject` saja.
- **Staff**: `status` (salah satu nilai `TicketStatus`: `open|in_progress|waiting|answered|resolved|closed`), `priority`, `assigned_to`, `department_id`, `category_id`, `subject`.

```http
PUT /api/tickets/42
Content-Type: application/json

{ "status": "in_progress", "priority": "urgent", "assigned_to": 2 }
```

→ `"message": "Ticket updated."`.

### Delete Ticket

```http
DELETE /api/tickets/{ticket}
```

Hanya role `admin`/`manager` (selain itu `403` walau scope API key `full`). → `200`, `"message": "Ticket deleted."`, `data: null`.

> Yang **tidak** ada di API (walau ada di web admin): sub-resource `/replies`, `/status`, `/star`, `/attachments`, `/bulk`. Jangan mengasumsikannya ada.

---

## Knowledge Base Endpoints

| Method | URL | Auth | Controller |
|---|---|---|---|
| GET | `/api/knowledge?search=&per_page=` | `api.auth` | `index` — artikel `published` + relasi `category`, envelope konsisten |
| GET | `/api/knowledge/search?q=&per_page=` | `api.auth` | `search` — `q` wajib min 2 karakter, hanya `published` |
| GET | `/api/knowledge/categories` | `api.auth` | `categories` — kategori aktif + jumlah artikel |
| GET | `/api/knowledge/category/{category}` | `api.auth` | `categoryArticles` — hanya kategori aktif (non-staff 404), artikel `published` |
| GET | `/api/knowledge/{article}` | `api.auth` | `show` — hanya `published` (non-staff 404), increment `view_count` |

> Urutan route sudah benar: `/search` dan `/categories` dideklarasikan sebelum `/{article}` agar tidak tertelan route binding.

---

## Conversation Endpoints

| Method | URL | Auth | Keterangan |
|---|---|---|---|
| GET | `/api/conversations` | `api.auth` | milik user / assigned ke user, `latest('last_message_at')`, 25/page |
| GET | `/api/conversations/{conversation}` | `api.auth` | `{conversation, messages}` + otorisasi owner/assignee/staff |
| POST | `/api/conversations` | `api.auth` + `idempotency` | body: `body` (wajib, maks 10000). `firstOrCreate` conversation `open` + pesan pertama → `201`, `"message": "Message sent."` |
| POST | `/api/conversations/{conversation}/message` | `api.auth` + `idempotency` | body: `body` (wajib, maks 10000), `type` selalu `text` → `201` |

> Tidak ada endpoint `POST /api/conversations/{id}/messages` (jamak), upload file chat, atau `subject` saat start conversation — body di atas adalah kontrak aktual.

---

## AI Endpoints

Semua endpoint AI **staff-only** (`admin`/`manager`/`agent`, selain itu `403`) + throttle `ai` (default 30/menit). Envelope via `AiController::envelope` (sukses `200`, provider down `503`).

| Method | URL | Body aktual |
|---|---|---|
| POST | `/api/ai/classify` | `subject` (wajib, maks 500), `body` (wajib, maks 5000) → saran department/category/priority + `confidence` |
| POST | `/api/ai/suggest` | `ticket_subject` (wajib, maks 500), `ticket_body` (wajib, maks 5000), `tone?` (`professional\|friendly\|empathetic\|brief`) → teks balasan saran |
| POST | `/api/ai/sentiment` | `text` (wajib, maks 5000) → `{sentiment: positive\|neutral\|negative, score, urgency}` |

```http
POST /api/ai/classify
Content-Type: application/json

{ "subject": "Cannot login", "body": "Getting a 500 error since this morning." }
```

```json
{
  "success": true,
  "data": { "department": "Technical Support", "category": "Login Issues", "priority": "high", "confidence": 0.92 },
  "message": "Ticket classified."
}
```

> Tidak ada endpoint `POST /api/ai/summarize` di API (summarize hanya ada di web admin via `AiAssistantController`). Jangan mendokumentasikannya sebagai endpoint API.

---

## Analytics Endpoints

| Method | URL | Auth | Query aktual |
|---|---|---|---|
| GET | `/api/analytics/summary` | `api.auth` | `from?`, `to?` (date) → `ReportService::overview` |
| GET | `/api/analytics/tickets-by-status` | `api.auth` | `from?`, `to?` → `{by_status, by_priority}` |

```json
{
  "success": true,
  "data": { "by_status": { "open": 12 }, "by_priority": { "high": 4 } },
  "message": "Ticket distribution retrieved."
}
```

> `dashboard()` dan `chartData()` (dengan param `days`) ada di controller tetapi tidak di-routing. Tidak ada prefix `/api/admin/analytics/*` dan tidak ada endpoint agent-performance di API.

---

## User Endpoints

| Method | URL | Auth | Keterangan |
|---|---|---|---|
| GET | `/api/users?search=&per_page=` | `api.auth` + role `admin`/`manager` | paginasi (default 25, maks 100); secrets 2FA selalu di-hidden |

```json
{ "success": true, "data": { "current_page": 1, "data": [ { "id": 5, "name": "...", "roles": [...] } ] }, "message": "Users retrieved." }
```

> `show()` ada di controller tetapi tidak di-routing. Tidak ada `GET/PUT /api/user` maupun `/api/user/api-keys` di API ini.

---

## Webhooks (outbound)

Dokumentasi lengkap pindah ke **[docs/14-WEBHOOKS.md](14-WEBHOOKS.md)**: events, signature HMAC-SHA256, headers, retry `1m/5m/15m/1h`, no-retry 4xx, idempotency.

---

## Code Examples

### PHP (Guzzle)

```php
<?php
use GuzzleHttp\Client;

$client = new Client([
    'base_uri' => 'https://yourdomain.com/api/',
    'headers' => [
        'Authorization' => 'Bearer ' . $apiKey,
        'Accept' => 'application/json',
    ],
]);

// Create ticket (field aktual)
$response = $client->post('tickets', [
    'json' => [
        'subject' => 'Login issue',
        'body' => 'Cannot access dashboard',
        'department_id' => 1,
        'category_id' => 3,
        'priority' => 'high',
    ],
]);

$ticket = json_decode($response->getBody(), true)['data'];

// List open tickets (filter aktual)
$response = $client->get('tickets', [
    'query' => ['status' => 'open', 'per_page' => 25],
]);

$tickets = json_decode($response->getBody(), true)['data'];
```

### JavaScript (fetch)

```javascript
const API_BASE = 'https://yourdomain.com/api';
const API_KEY = 'a1b2c3d4e5f6...';

// AI classify (staff key, field aktual)
async function classifyTicket(subject, body) {
  const response = await fetch(`${API_BASE}/ai/classify`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${API_KEY}`,
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    },
    body: JSON.stringify({ subject, body }),
  });

  if (!response.ok) {
    const error = await response.json();
    throw new Error(error.message);
  }

  return response.json(); // { success, data, message }
}
```

### cURL

```bash
# Login
curl -X POST "https://yourdomain.com/api/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email": "agent@example.com", "password": "secret", "device_name": "cli"}'

# Create ticket (dengan idempotency key)
curl -X POST "https://yourdomain.com/api/tickets" \
  -H "Authorization: Bearer <key>" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Idempotency-Key: 550e8400-e29b-41d4-a716-446655440000" \
  -d '{"subject": "Login issue", "body": "Cannot access dashboard", "department_id": 1, "priority": "high"}'

# List tickets
curl -X GET "https://yourdomain.com/api/tickets?status=open&per_page=25" \
  -H "Authorization: Bearer <key>" \
  -H "Accept: application/json"

# AI sentiment
curl -X POST "https://yourdomain.com/api/ai/sentiment" \
  -H "Authorization: Bearer <staff-key>" \
  -H "Content-Type: application/json" \
  -d '{"text": "I have been waiting for 3 days!"}'

# Analytics
curl -X GET "https://yourdomain.com/api/analytics/summary?from=2026-09-01&to=2026-10-01" \
  -H "Authorization: Bearer <key>" \
  -H "Accept: application/json"
```

---

## Full Endpoint Index (aktual, 22 route)

| Method | URL | Auth |
|---|---|---|
| POST | `/api/login` | publik (throttle login) |
| POST | `/api/logout` | `api.auth` |
| GET | `/api/me` | `api.auth` |
| GET | `/api/tickets` | `api.auth` |
| POST | `/api/tickets` | `api.auth` + idempotency |
| GET | `/api/tickets/{ticket}` | `api.auth` (owner/assignee/staff) |
| PUT/PATCH | `/api/tickets/{ticket}` | `api.auth` |
| DELETE | `/api/tickets/{ticket}` | `api.auth` + admin/manager |
| GET | `/api/knowledge` | `api.auth` |
| GET | `/api/knowledge/{article}` | `api.auth` |
| GET | `/api/knowledge/category/{category}` | `api.auth` (⚠️ action belum ada) |
| GET | `/api/knowledge/search` | `api.auth` (⚠️ action belum ada) |
| GET | `/api/conversations` | `api.auth` |
| GET | `/api/conversations/{conversation}` | `api.auth` |
| POST | `/api/conversations` | `api.auth` + idempotency |
| POST | `/api/conversations/{conversation}/message` | `api.auth` + idempotency |
| POST | `/api/ai/classify` | `api.auth` + staff + throttle ai |
| POST | `/api/ai/suggest` | `api.auth` + staff + throttle ai |
| POST | `/api/ai/sentiment` | `api.auth` + staff + throttle ai |
| GET | `/api/analytics/summary` | `api.auth` |
| GET | `/api/analytics/tickets-by-status` | `api.auth` |
| GET | `/api/users` | `api.auth` + admin/manager |
