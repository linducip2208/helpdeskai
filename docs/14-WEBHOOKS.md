# 14 — Outbound Webhooks

> Sumber kebenaran: `App\Services\WebhookService`, `App\Jobs\DeliverWebhook`, `App\Models\WebhookEndpoint` (`EVENTS`), `App\Models\WebhookDelivery`. Dikelola di **Admin → Webhooks** (permission `webhooks.manage`).

## Events

| Event | Kapan dikirim |
|---|---|
| `ticket.created` | tiket baru dibuat |
| `ticket.replied` | ada balasan (publik/internal) |
| `ticket.status_changed` | status tiket berubah |
| `ticket.assigned` | tiket di-assign ke agen |
| `webhook.test` | tombol "Send test" di admin (one-off, sinkron, tidak masuk antrean) |

Hanya endpoint aktif (`is_active`) yang berlangganan event tersebut yang menerima delivery (`eventList()` = irisan `events` endpoint dengan `EVENTS` di atas).

## Payload

```json
{
  "event": "ticket.created",
  "occurred_at": "2026-10-06T12:00:00+07:00",
  "ticket": {
    "id": 42,
    "uid": "TKT-A3B9X",
    "subject": "Cannot login",
    "status": "open",
    "priority": "high",
    "department": "Technical Support",
    "assigned_to": 2
  },
  "extra": {}
}
```

## Signature HMAC-SHA256

Setiap delivery ditandatangani dengan secret per-endpoint (disimpan terenkripsi via `Crypt`, diatur dengan `WebhookEndpoint::setSecret()`):

```
signature = HMAC-SHA256(key = endpoint_secret, msg = timestamp + "." + raw_json_body)
header    = "sha256=" + hex(signature)
```

Contoh verifikasi (PHP, di sisi penerima):

```php
$body      = file_get_contents('php://input');
$timestamp = $_SERVER['HTTP_X_WEBHOOK_TIMESTAMP'] ?? '';
$given     = $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'] ?? ''; // "sha256=..."
$expected  = 'sha256=' . hash_hmac('sha256', $timestamp . '.' . $body, $WEBHOOK_SECRET);

if (! hash_equals($expected, $given)) {
    http_response_code(401); exit; // signature tidak valid
}
// Opsional: tolak bila |time() - (int) $timestamp| > 300 (replay window 5 menit)
```

## Headers

| Header | Isi |
|---|---|
| `Content-Type` | `application/json` |
| `X-Webhook-Event` | nama event (atau `webhook.test`) |
| `X-Webhook-Timestamp` | unix timestamp (string) saat pengiriman |
| `X-Webhook-Signature` | `sha256=<hex HMAC>` |
| `X-Idempotency-Key` | `sha256(event|ticket_id|action_at)` — stabil untuk event yang sama |

Timeout HTTP mengikuti `timeout_seconds` per endpoint (default 10 dtk; test dibatasi maks 15 dtk).

## Retry & idempotency

- Pengiriman via job `DeliverWebhook`: `tries = 5`, `backoff = [60, 300, 900, 3600]` → jeda retry **1m / 5m / 15m / 1h** setelah kegagalan (5xx / timeout / network error). Setelah retry habis, delivery ditandai `failed` + error tercatat (maks 2000 char) dan `Log::error('Webhook delivery exhausted retries')`.
- **No-retry 4xx**: response `400–499` → delivery langsung `failed` dengan pesan `Permanent client error (HTTP N), not retried.` dan exception `WebhookPermanentFailure` (tidak masuk antrean ulang). Perbaiki URL/payload di sisi penerima sebelum test ulang.
- **Idempotency**: `WebhookDelivery::firstOrCreate(['idempotency_key' => …])` — dispatch ganda untuk event tiket yang sama tidak membuat delivery duplikat. Penerima disarankan menyimpan `X-Idempotency-Key` dan mengabaikan pengulangan.
- Endpoint nonaktif/dihapus saat job berjalan → delivery `skipped`. Delivery yang sudah `sent` tidak dikirim ulang.

## Operasional

- Daftar & status delivery per endpoint dapat dilihat di admin (model `WebhookDelivery`: `status` = `pending|sent|failed|skipped`, `attempts`, `response_status`, `response_body` (maks 4000 char), `error`).
- Retensi: `webhook_deliveries` di-prune oleh `maintenance:cleanup` sesuai `webhook_retention_days` (default 90 hari).
- Test koneksi: tombol test di admin memanggil `WebhookService::sendTest()` secara sinkron dan menampilkan `{ok, status, body}`.
