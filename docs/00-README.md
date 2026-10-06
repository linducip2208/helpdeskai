# HelpDesk AI — Project Overview

## What is HelpDesk AI?

**HelpDesk AI** is a full-stack, production-ready **help desk & customer support platform** built on Laravel 13 with Vue 3, Inertia, TailwindCSS, and Reverb WebSockets. It provides enterprise-grade ticket management, AI-powered intelligence, real-time live chat, knowledge base, analytics, email piping, automation rules, SLA management, REST API, and programmatic SEO — all in one monolith.

Key differentiators:
- **AI features classify, suggest, analyze sentiment** on every ticket — without hardcoding any AI provider
- **Real-time chat** via Laravel Reverb (WebSocket)
- **RBAC** with Spatie Laravel Permission
- **Programmatic SEO** built-in from day one (best pages, comparisons, alternatives)
- **Fully dynamic AI provider system** — zero hardcoded vendors, user adds their own keys

---

## Tech Stack

| Layer | Technology | Version |
|-------|-----------|---------|
| Backend Framework | Laravel | 13.x |
| PHP | PHP | 8.3+ |
| Database | MySQL / SQLite | 8.0+ / 3.x |
| Frontend | Vue 3 + Inertia.js | 3.4+ / 2.x |
| CSS | TailwindCSS | 3.x |
| Build Tool | Vite | 8.x |
| WebSocket Server | Laravel Reverb | 1.10+ |
| Auth + RBAC | Laravel Breeze + Spatie Permission | 2.4+ / 7.4+ |
| API Auth | Laravel Sanctum | 4.x |
| PDF Export | barryvdh/laravel-dompdf | 3.x |
| Routing (JS) | tightenco/ziggy | 2.x |

---

## Quick Start

### Prerequisites

- PHP 8.3+
- Composer 2.x
- Node.js 20+
- MySQL 8.0+ (or SQLite for dev)
- Git

### Installation

```bash
# Clone the repository
git clone <repo-url> helpdeskai
cd helpdeskai

# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Run migrations & seeders
php artisan migrate --seed

# Build frontend assets
npm run build

# Start development server
composer run dev
```

This starts 4 processes concurrently:
- `php artisan serve` — HTTP server (port 8000)
- `php artisan queue:listen` — Queue worker
- `php artisan pail` — Log tail
- `npm run dev` — Vite dev server

### Access

| URL | Description |
|-----|-------------|
| `http://localhost:8000` | Frontend landing page |
| `http://localhost:8000/login` | Login page |
| `http://localhost:8000/register` | Registration |
| `http://localhost:8000/admin` | Admin panel (admin only) |
| `http://localhost:8000/dashboard` | User dashboard |

### Default Credentials

| Role | Email | Password |
|------|-------|----------|
| Admin | `admin@helpdeskai.test` | `password` |
| Agent | `agent@helpdeskai.test` | `password` |
| Customer | `customer@helpdeskai.test` | `password` |

> **Note:** Run `php artisan db:seed` to create these accounts after `migrate`.

---

## Project Structure

```
helpdeskai/
├── app/
│   ├── Enums/                # TicketStatus enum
│   ├── Http/
│   │   ├── Controllers/      # Web controllers (Admin/, Auth/)
│   │   ├── Middleware/        # HandleInertiaRequests
│   │   └── Requests/         # Form requests
│   ├── Models/               # 26 Eloquent models
│   └── Providers/            # Service providers
├── config/                   # 13 config files
├── database/
│   ├── factories/            # Model factories
│   ├── migrations/           # 30 migration files
│   └── seeders/              # Database seeders
├── docs/                     # Documentation (you are here)
├── public/                   # Public web root
├── resources/
│   ├── css/                  # TailwindCSS
│   ├── js/                   # Vue 3 + Inertia components
│   └── views/                # Blade templates
├── routes/
│   ├── web.php               # Web routes (113 lines)
│   ├── auth.php              # Auth routes (Breeze)
│   ├── channels.php          # WebSocket channel auth
│   └── console.php           # Artisan scheduler
├── storage/                  # Logs, uploads, cache
├── tests/                    # PHPUnit tests
├── .env.example              # Environment template
├── composer.json             # PHP dependencies
├── package.json              # Node dependencies
├── tailwind.config.js
└── vite.config.js
```

---

## Key Features at a Glance

- **Advanced Ticket Management** — CRUD, status lifecycle, priorities, assignments, custom fields, star/flag
- **AI-Powered Intelligence** — auto-classify tickets, suggest responses, sentiment analysis, predictive SLA
- **Real-Time Live Chat** — WebSocket-powered via Reverb, agent assignment, typing indicators
- **Knowledge Base** — articles, FAQs, categories with helpful/not helpful voting, search
- **Analytics & Reporting** — dashboard stats, charts, ticket volume, agent performance
- **User & Organization Management** — RBAC (admin/agent/customer), department assignment, impersonation
- **Productivity & Automation** — automation rules (event → condition → action), SLA policies, canned responses
- **Email Piping** — create/reply to tickets via email
- **Multi-Language** — i18n support for 15+ languages
- **REST API** — 137+ endpoints with Sanctum token auth, rate limiting
- **Programmatic SEO** — `/best-helpdesk-software-{year}`, `/compare/{a}-vs-{b}`, `/alternatives-to/{slug}`
- **Security** — CSRF, XSS prevention, encrypted API keys, 2FA, audit logging, rate limiting
- **PWA & Mobile** — PWA-ready with service worker

---

## Development Commands

```bash
# Start everything concurrently
composer run dev

# Run tests
composer run test

# Run individual test
php artisan test --filter=YourTest

# Queue worker (dedicated)
php artisan queue:work

# Reverb WebSocket server (dedicated)
php artisan reverb:start

# Scheduler (cron: * * * * * php artisan schedule:run)
php artisan schedule:run

# Lint (PHP)
./vendor/bin/pint

# Build for production
npm run build
```

---

## Documentation Index

| File | Description |
|------|-------------|
| `00-README.md` | Project overview (this file) |
| `01-PRD.md` | Product Requirements Document |
| `02-ARCHITECTURE.md` | System Architecture |
| `03-ERD.md` | Entity Relationship Diagram |
| `04-DATABASE.md` | Detailed Database Schema |
| `05-FEATURES.md` | Complete Feature Documentation |
| `06-API.md` | REST API Documentation |
| `07-AI-PROVIDERS.md` | AI Provider System |
| `08-PSEO.md` | Programmatic SEO Strategy |
| `09-SECURITY.md` | Security Documentation |
| `10-DEPLOYMENT.md` | Deployment Guide |
| `11-DEVELOPMENT.md` | Development Guide |
| `12-CHANGELOG.md` | Version Changelog |
| `PROGRESS.md` | Build Progress Tracker |

---

## License

This project is proprietary software. All rights reserved.
