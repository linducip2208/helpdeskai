# 12 — Changelog

## v1.0.0 (2026-05-04) — Initial Release

### Foundation

#### Project Setup
- Laravel 13 scaffold with Breeze authentication (Blade stack)
- Vite 8 build tooling with Tabler CSS + Alpine.js + ApexCharts (all via npm, local bundle, no CDN)
- SQLite default for development, MySQL 8.0+ for production
- Spatie Laravel Permission v7.4 for RBAC
- Laravel Reverb for real-time WebSocket
- Laravel Sanctum for API token authentication
- `@tabler/core` via npm for CSS/JS (local Vite bundle, no CDN), `alpinejs` for interactivity, `apexcharts` for dashboard charts
- barryvdh/laravel-dompdf for PDF generation

#### Database Schema (30 tables)
- `users` with roles (admin, agent, customer), avatar, phone, timezone
- `departments` and `categories` for ticket organization
- `tickets` with UID generation, status lifecycle, priorities, SLA tracking
- `ticket_replies` with internal note support
- `ticket_attachments` with file metadata
- `conversations` and `conversation_messages` for live chat
- `knowledge_categories` (hierarchical), `knowledge_articles`, `knowledge_faqs`
- `ai_providers`, `ai_provider_models`, `ai_feature_configs`, `ai_usage_logs`
- `canned_responses`, `sla_policies`, `automation_rules`, `email_templates`
- `settings` (key-value with type detection)
- `services`, `posts`, `notifications`, `activity_logs`, `seo_meta`, `api_keys`
- Spatie permission tables (`permissions`, `roles`, `model_has_*`, `role_has_permissions`)
- Laravel core: `password_reset_tokens`, `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`

#### Models (26 Eloquent models)
- `User`, `Ticket`, `TicketReply`, `TicketAttachment`
- `Department`, `Category`
- `Conversation`, `ConversationMessage`
- `KnowledgeCategory`, `KnowledgeArticle`, `KnowledgeFaq`
- `AiProvider`, `AiProviderModel`, `AiFeatureConfig`, `AiUsageLog`
- `CannedResponse`, `SlaPolicy`, `AutomationRule`, `EmailTemplate`
- `Setting`, `Service`, `Post`, `Notification`, `ActivityLog`, `SeoMeta`, `ApiKey`

#### Enums
- `TicketStatus` — open, in_progress, waiting, resolved, closed

### Core Features

#### Ticket Management
- Full CRUD with unique UID (`TKT-XXXXX`) auto-generation
- 5-stage status lifecycle (open → in_progress → waiting → resolved → closed)
- 4 priority levels (low, medium, high, urgent) with color-coded badges
- 4 source channels (web, email, chat, api)
- Agent assignment with department routing
- Star/flag for important tickets
- Custom fields via JSON column
- File attachments on tickets and replies
- Internal notes (agent-only replies)
- Status color and priority color computed accessors
- SLA due date tracking per ticket

#### AI Provider System (Fully Dynamic)
- **ZERO hardcoded providers** — all user-defined via admin UI
- 3 API format adapters: `openai_compatible`, `anthropic`, `gemini`
- `openai_compatible` covers 15+ providers (OpenAI, DeepSeek, Groq, Ollama, etc.)
- AES-256 encrypted API key storage (`api_key_encrypted`)
- Per-feature provider+model picker (`ai_feature_configs`)
- Auto-fetch models from provider API
- Usage logging with cost estimation
- 20 preset templates (`storage/app/ai-presets/*.json`)
- Test connection functionality
- Feature keys: `ticket.classify`, `ticket.suggest`, `ticket.sentiment`, `ticket.summarize`, `kb.suggest`, `chat.bot_reply`

#### Real-Time Live Chat
- Laravel Reverb WebSocket integration
- Conversation CRUD with agent assignment
- Message types: text, file, system
- Typing indicators support
- Read receipts (`read_at` timestamp)
- Metadata JSON for rich messages
- Chat-to-ticket conversion capability

#### Knowledge Base
- Article CRUD with draft/published/archived workflow
- Hierarchical category system (self-referential parent_id)
- FAQ management per category
- Helpful/not helpful voting counters
- View count tracking
- Featured article pinning
- Per-article SEO meta (title, description)
- Author attribution
- Search functionality

#### Analytics & Reporting (Backend)
- Dashboard statistics endpoint
- Chart data endpoint (period-based grouping)
- Agent performance metrics
- Ticket volume trends
- AI usage analytics

#### Automation
- Production rules: event → condition → action pipeline
- Trigger events: `ticket.created`, `ticket.replied`, `ticket.escalated`, `sla.breached`
- JSON-based conditions (field + operator + value)
- JSON-based actions (assign, set_priority, send_email, add_tag)
- Schedulable sort_order

#### SLA Management
- Per-department, per-priority SLA policies
- First response time (minutes)
- Resolution time (minutes)
- Active/inactive toggle
- `sla_due_at` auto-calculated on ticket creation

#### Canned Responses
- Pre-written template library
- Category organization (same as ticket categories)
- Quick insert support
- Slug-based identification

#### Email Templates
- Customizable per notification type
- Predefined keys: `ticket.created`, `ticket.replied`, `ticket.assigned`, `ticket.resolved`, `welcome`
- Variable placeholders for dynamic content

#### Notifications
- Database notification channel
- UUID primary keys
- Polymorphic notifiable (`notifiable_type` + `notifiable_id`)
- Read/unread tracking

#### Activity Logging
- All admin actions logged: created, updated, deleted, assigned
- Polymorphic target reference (`target_type` + `target_id`)
- IP address tracking
- JSON metadata for before/after values
- Filterable by action, date range

#### Settings System
- Key-value store with type detection (string, boolean, integer, json)
- Grouped by category (general, mail, payment, seo, whitelabel)
- Static helpers: `Setting::get()`, `Setting::set()`
- Type-aware retrieval with automatic casting

### Content Management

#### Services
- Service/product pages for landing (`/services/{slug}`)
- Title, slug, icon, description, rich content
- Active/inactive toggle with sort order
- Used by pSEO for listings and comparisons

#### Blog
- Post CRUD (`/blog`, `/blog/{slug}`)
- Status workflow: draft → published
- Featured image support
- Category tags
- Published date tracking
- Author attribution

### Programmatic SEO

#### Patterns
- `/best-helpdesk-software-{year}` — top 10 ranking
- `/compare/{a}-vs-{b}` — head-to-head comparison
- `/alternatives-to/{slug}` — alternatives directory

#### SEO Features
- Dynamic meta title and description
- JSON-LD structured data (ItemList, Product, FAQPage)
- Open Graph tags
- Twitter Card tags
- Canonical URL generation
- 300+ word unique content per page
- Inline FAQ generation

### REST API (structure established)

#### Authentication
- Sanctum token authentication
- 64-char API key generation
- Expiry and active status support

#### Endpoint Categories Designed
- Tickets: CRUD, status change, starring, attachments
- Knowledge Base: articles, categories, FAQs, search, voting
- Chat: conversations, messages
- AI: classify, suggest, sentiment, summarize
- Analytics: stats, chart data, agent performance
- Users: profile, API keys
- Admin: users, departments, categories, AI providers, settings

### Security
- CSRF protection on all web routes
- Encrypted API keys at rest (AES-256)
- Rate limiting (60 req/min for API)
- Parameterized queries (Eloquent ORM)
- Content escaping (Blade auto-escaping)
- Database session driver
- Role-based access control via Spatie
- Audit logging for all admin actions

### Seeders
- `RolesAndPermissionsSeeder` — creates admin/agent/customer roles with granular permissions
- `AdminUserSeeder` — creates admin@helpdeskai.test / password
- `AgentUserSeeder` — creates agent@helpdeskai.test / password
- `CustomerUserSeeder` — creates customer@helpdeskai.test / password

### Routes
- Public: home, blog, services, knowledge base, pSEO, contact
- Authenticated: dashboard, profile, tickets, conversations
- Admin (role:admin): dashboard, tickets, departments, categories, users, conversations, knowledge, services, canned responses, SLA policies, automation rules, AI providers, AI features, posts, email templates, settings, API keys, activity log, analytics, front pages

### Configuration
- `config/app.php` — app defaults
- `config/auth.php` — auth guards and providers
- `config/broadcasting.php` — Reverb connection
- `config/database.php` — MySQL/SQLite
- `config/filesystems.php` — local/public/S3
- `config/mail.php` — SMTP/log
- `config/permission.php` — Spatie settings
- `config/reverb.php` — WebSocket settings
- `config/sanctum.php` — API token settings
- `config/services.php` — third-party services
- `config/session.php` — session settings
- `config/queue.php` — queue connection
- `config/cache.php` — cache store

### Documentation (14 docs)
- `docs/00-README.md` — Project overview, quick start, stack, credentials
- `docs/01-PRD.md` — Product requirements, personas, feature list, user stories, MVP scope
- `docs/02-ARCHITECTURE.md` — System architecture, design patterns, request lifecycle, deployment diagram
- `docs/03-ERD.md` — Entity relationship diagram, all 30+ tables, relationships, indexes
- `docs/04-DATABASE.md` — Complete schema for every table, enums, JSON structures, migration order
- `docs/05-FEATURES.md` — Feature documentation with configuration, how-to-use, endpoints
- `docs/06-API.md` — REST API with auth, endpoints, response examples, code samples (PHP/JS/cURL)
- `docs/07-AI-PROVIDERS.md` — AI provider philosophy, adapters, admin flow, presets, self-host
- `docs/08-PSEO.md` — pSEO patterns, content strategy, JSON-LD, sitemap, GSC submission
- `docs/09-SECURITY.md` — Auth, CSRF, XSS, encryption, RBAC, GDPR, security checklist
- `docs/10-DEPLOYMENT.md` — Server setup, Nginx, Supervisor, Docker, backup, SSL
- `docs/11-DEVELOPMENT.md` — Local setup, conventions, testing, adding features, contribution
- `docs/12-CHANGELOG.md` — This file
- `docs/PROGRESS.md` — Build progress tracker

---

## Planned for v1.1.0

### Frontend Polish
- Complete admin dashboard UI with live charts
- Kanban board view for tickets
- Settings UI with form validation
- User notification preferences
- Rich text editor for knowledge base articles

### Features
- Email piping (inbound email → ticket)
- Chatbot auto-responder
- 2FA (TOTP) implementation
- PDF ticket export
- CSV data export
- Bulk email to users
- User impersonation UI
- Advanced ticket search with filters

### Testing
- Comprehensive test suite (target 80%+ coverage)
- Browser tests with Laravel Dusk
- API tests with full endpoint coverage

### Performance
- Redis cache driver support
- Redis queue driver
- Query optimization (eager loading N+1 fixes)
- Response caching for public pages
