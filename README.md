# HelpDesk AI

> **AI-Powered Customer Support & Helpdesk Platform** — self-hosted, no-CDN, bilingual (ID/EN, WIB).

Self-hosted, AI-assisted helpdesk & customer support platform built with **Laravel 13**, **MySQL/SQLite**, **Tabler UI** (bundled locally, zero CDN), and a provider-agnostic **AI support copilot**.

Tickets, live conversations, knowledge base, SLA engine, automation rules, email piping, notifications (database + web push), granular RBAC, 2FA/TOTP, audit logs, REST API (Sanctum + scoped API keys), bilingual UI (**ID/EN**), and display timezone **Asia/Jakarta**.

Current version: **1.0.0** (see `VERSION` and `config/helpdesk.php`; changelog in [docs/12-CHANGELOG.md](docs/12-CHANGELOG.md)).

---

## Features

| Area | Highlights |
|---|---|
| Ticketing | Unique numbers, enforced state machine, reopen, assign/reassign, department transfer, internal notes (staff-only), public/customer replies, bulk actions, attachments (private storage, auth-checked downloads), tags, links, split, merge (history-preserving), watchers, custom fields |
| Customer portal | Own tickets only (IDOR-tested), replies, attachments, CSAT, knowledge base search, notification preferences, live-update polling |
| Agent workflow | Unified support inbox (8 queues + preview), My Work, command palette + shortcuts, canned responses + one-click macros, AI suggestions, customer 360, collision presence, workload dashboard |
| SLA engine | Business-hours + holidays + timezones, pause on waiting, response & resolution targets, warnings, breach alerts, configurable escalation rules, scheduler-driven |
| Automation | `IF conditions THEN actions` (14 actions, tag/sentiment/SLA conditions), recursion guard, execution runs log with duration + errors |
| Email | Robust piping (Message-ID dedupe, threading, CC, bounce/spam/loop protection, HTML sanitization) + queued customer mails honoring settings |
| Notifications | Event-driven database + queued web push (VAPID) + per-event channel preferences |
| Knowledge base | Categories, articles, FAQs, drafts/review workflow, revisions, scheduled publishing, search + failed-search analytics, feedback, SEO, public sitemap |
| AI copilot | Classification (+confidence/language), sentiment, suggested replies (draft-only), summaries, KB grounded answers with citations, similar tickets, QA scoring — graceful degradation, async jobs, privacy-gated |
| AI providers | OpenAI-compatible, Anthropic, Gemini (+ presets); encrypted keys, priority failover, budgets, timeouts/retries, usage & cost tracking, throttling |
| Channels | Email, web, API, live chat, embeddable widget + WhatsApp Cloud API + Telegram Bot API (signed webhooks, queued outbound) |
| API | Sanctum **or** scoped API keys, `/api` + versioned `/api/v1`, idempotency keys, envelope `{success, data, message}`, auto-generated OpenAPI spec, rate limited |
| Auth | Email/password, Google OAuth (config-gated), 2FA/TOTP + recovery codes, email verification, registration modes (open/closed/approval), session management |
| Security | Granular RBAC (40+ permissions), permission-filtered sidebar, impersonation rank rules, throttled endpoints, security headers, sanitized errors, audit trail |
| UI | Tabler local via npm/Vite (zero CDN, verified incl. static pSEO), dark mode, responsive, EN/ID (550+ keys), WIB timestamps, PWA offline-capable |
| Ops | Database queue + dashboard, scheduler (SLA/reminders/autoclose/cleanup/backup/IndexNow/KB-publish), `/health` + `/ready`, system health page, `setup:check`, retention policies |

---

## Requirements

- PHP 8.3+ (extensions: `bcmath ctype curl dom fileinfo json mbstring openssl pcre pdo_mysql/pdo_sqlite tokenizer xml`, plus `intl`)
- Composer 2
- Node.js 18+ & npm
- MySQL 8.0+ (production) or SQLite (local/dev)
- A queue worker + scheduler entry for background jobs (see Deployment)

---

## Installation

```bash
composer install
cp .env.example .env
php artisan key:generate

# Database (SQLite for quick start; MySQL for production)
touch database/database.sqlite
php artisan migrate --force

# Roles, permissions, demo data
php artisan db:seed --force

# Frontend (Tabler + Inter + ApexCharts, all local via npm)
npm install
npm run build

php artisan storage:link
```

Key `.env` settings:

```
APP_NAME="HelpDesk AI"
APP_TIMEZONE=Asia/Jakarta
APP_LOCALE=id            # id | en (user-switchable, stored in session)

DB_CONNECTION=mysql      # or sqlite
DB_HOST=127.0.0.1
DB_DATABASE=helpdeskai

QUEUE_CONNECTION=database
MAIL_MAILER=smtp         # log | smtp ...
```

AI providers and keys are configured in **Admin → AI Providers** (keys encrypted at rest, never displayed back).

---

## Running locally

```bash
composer dev   # serve + queue:listen + pail + vite (concurrently)
```

Default seeded accounts (see `database/seeders/*`): admin, manager, agents, customers — change passwords immediately.

---

## Scheduler & queue (production)

```bash
* * * * * cd /var/www/helpdeskai && php artisan schedule:run >> /dev/null 2>&1
php artisan queue:work --tries=3 --backoff=30
```

Scheduled: `sla:check` (hourly), `tickets:reminders` (08:00), `db:backup` (02:00), `seo:indexnow` (02:45). Queued: web-push delivery, notifications.

---

## Testing

```bash
php artisan test            # 72 tests: auth, IDOR/ownership, attachments, automation, AI fallback, email piping, API keys
vendor/bin/pint --test      # code style (Laravel preset)
npm run build               # frontend bundle must succeed
```

Security coverage includes negative tests: customer A ↔ ticket/attachment/conversation of customer B, internal-note leakage (web + API), revoked/expired/scoped API keys, email-reply spoofing, nested impersonation, AI-outage fallback.

---

## Deployment

See **[DEPLOYMENT.md](DEPLOYMENT.md)** (Ubuntu/Nginx + PHP-FPM, Supervisor, permissions, `storage:link`, Vite build) and the docs set:

- [docs/05-FEATURES.md](docs/05-FEATURES.md) — feature reference
- [docs/06-API.md](docs/06-API.md) — REST API (22 routes, Sanctum + scoped keys + idempotency)
- [docs/07-AI-PROVIDERS.md](docs/07-AI-PROVIDERS.md) — DB-driven providers, failover, budgets, RAG
- [docs/09-SECURITY.md](docs/09-SECURITY.md) — security model
- [docs/10-DEPLOYMENT.md](docs/10-DEPLOYMENT.md) — deployment (Redis opsional, scheduler aktual, backup/restore, health)
- [docs/12-CHANGELOG.md](docs/12-CHANGELOG.md) — changelog (1.0.0 stable)
- [docs/13-ACCESS-MATRIX.md](docs/13-ACCESS-MATRIX.md) — Role × Aksi access matrix
- [docs/14-WEBHOOKS.md](docs/14-WEBHOOKS.md) — outbound webhooks (HMAC, retry, idempotency)

Production checklist:

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

No build or runtime asset is loaded from a CDN — the UI works fully offline from `public/build`.

---

## Security

- Report vulnerabilities privately to the repository owner. Never expose `.env`, `storage/*.key`, or AI provider keys.
- API keys are stored as SHA-256 hashes and shown once at creation; scopes enforced per HTTP method.
- Attachments live in private storage with generated filenames, MIME + extension allowlists, and per-download authorization.

## License

This software is proprietary commercial software. See [LICENSE](LICENSE).

- © 2026 Lindu Cipta Pranayama / HelpdeskAI. All rights reserved.
- Source code is not open source. Redistribution, resale, or sublicensing without written permission is prohibited.
- Production use requires a valid commercial license (activation via marketplace pairing wizard `/__pair`, or **Admin → License Status**).
- Third-party dependencies keep their own licenses — see [THIRD_PARTY_LICENSES.md](THIRD_PARTY_LICENSES.md).

### Commercial purchase

Untuk pembelian lisensi dan informasi komersial: **081296052010**
