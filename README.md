# HelpDesk AI

Self-hosted, AI-assisted helpdesk & customer support platform built with **Laravel 13**, **MySQL/SQLite**, **Tabler UI** (bundled locally, zero CDN), and a provider-agnostic **AI support copilot**.

Tickets, live conversations, knowledge base, SLA engine, automation rules, email piping, notifications (database + web push), granular RBAC, 2FA/TOTP, audit logs, REST API (Sanctum + scoped API keys), bilingual UI (**ID/EN**), and display timezone **Asia/Jakarta**.

---

## Features

| Area | Highlights |
|---|---|
| Ticketing | Unique ticket numbers, state machine (`open → in_progress → waiting → answered → resolved → closed`), reopen, assign/reassign, department transfer, internal notes (staff-only), public/customer replies, bulk actions, attachments (private storage, auth-checked downloads) |
| Customer portal | Own tickets only (IDOR-tested), replies, attachments, CSAT, knowledge base search |
| Agent workflow | Assigned/team queues, claim, canned responses, AI suggestions, customer history, workload dashboard |
| SLA engine | First-response & resolution targets per priority/department, breach detection, warnings, scheduler-driven checks |
| Automation | `IF conditions THEN actions` rule engine (assign, set priority/status/category/department, internal notes, notifications) with recursion guard and execution audit |
| Email | Inbound email → ticket/reply with threading (`[TKT-XXXXX]`), spoof protection, sender validation, logging; HTML bodies never rendered raw |
| Notifications | Event-driven database notifications + queued web push (VAPID) |
| Knowledge base | Categories, articles, FAQs, search, slugs, SEO metadata, public sitemap |
| AI copilot | Classification, priority prediction, sentiment, suggested replies, summaries — graceful degradation when providers are down (ticketing never depends on AI) |
| AI providers | OpenAI-compatible, Anthropic, Gemini; encrypted keys, connection test, model listing, usage & cost tracking, per-endpoint throttling |
| API | Sanctum tokens **or** scoped API keys (`read`/`read-write`/`full`), consistent `{success, data, message}` envelope, rate limited |
| Security | RBAC (Spatie), 2FA/TOTP + recovery codes, impersonation with audit + stop flow, activity/audit logs, throttled auth & AI endpoints, sanitized AI errors |
| UI | Tabler bundled via Vite (no CDN, works offline), responsive, EN/ID switcher, Asia/Jakarta timestamps (`06 Oktober 2026 19:00 WIB`) |
| Ops | Database queue + scheduler (SLA checks, reminders, backups, IndexNow), system artisan commands, production error pages |

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

See **[DEPLOYMENT.md](DEPLOYMENT.md)** (Ubuntu/Nginx + PHP-FPM, Supervisor, permissions, `storage:link`, Vite build) and **[docs/](docs/)** for PRD, architecture, ERD, API, AI providers, and changelog.

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

Proprietary — activation via marketplace pairing wizard (`/__pair`). See **Admin → License Status**.
