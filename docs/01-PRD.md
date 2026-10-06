# 01 — Product Requirements Document (PRD)

## Project Overview

**HelpDesk AI** is a comprehensive, AI-powered help desk and customer support platform designed to replace fragmented support workflows (email threads, Slack pings, shared inboxes) with a single, intelligent system. It serves as both a ticket management backbone and an AI co-pilot that classifies issues, suggests responses, detects sentiment, and predicts SLA breaches before they happen.

### Vision

*"Make every support interaction faster, smarter, and more human — using AI that the customer owns and controls."*

Unlike SaaS tools that lock users into specific AI models and recurring fees, HelpDesk AI gives users full control: bring your own API keys, choose any LLM provider, and run AI features on your own infrastructure. Zero hardcoded dependencies.

---

## Target Users (3 Personas)

### 1. Enterprise (500+ employees)

**Needs:**
- Multi-department ticket routing (IT, HR, Finance, Legal)
- SLA enforcement with automatic escalation
- Role-based access (admin, manager, agent, viewer)
- Audit logging for compliance (GDPR, SOC2)
- SSO / SAML integration
- Custom automation rules
- Email piping for ticket creation
- API access for integrations with internal tools
- Dedicated agent performance dashboards

**Pain points solved:** Fragmented support across departments, no SLA tracking, manual routing, no audit trail.

### 2. Digital Agency (10–200 employees)

**Needs:**
- White-label support portal for clients
- Knowledge base for self-service
- Live chat widget on client sites
- Canned responses for common queries
- AI-suggested replies to speed up agents
- Analytics on response times
- Multi-language support for global clients
- Budget-friendly (no per-agent SaaS fees)

**Pain points solved:** Slow response times, repetitive answers, no self-service portal, no metrics.

### 3. Small Business / Startup (1–10 employees)

**Needs:**
- Simple ticket system (email → ticket)
- Basic knowledge base
- AI-powered auto-classification
- Low cost (self-hosted, no subscription)
- Easy setup (Laravel-based, shared hosting OK)
- PWA for mobile support on the go

**Pain points solved:** Using personal email for support, no ticket tracking, no AI budget.

---

## Feature List by Module

### A. Advanced Ticket Management
- Ticket CRUD with unique UID (e.g., `TKT-A3B9X`)
- Status lifecycle: Open → In Progress → Waiting → Resolved → Closed
- Priority: Low, Medium, High, Urgent
- Source: Web, Email, Chat, API
- Assignment to agents/departments
- Star/flag important tickets
- Custom fields (JSON)
- File attachments
- Internal notes (agent-only)
- Bulk operations
- Kanban board view

### B. AI-Powered Intelligence
- **Auto-classify:** AI assigns category/department/priority on ticket creation
- **Response suggestion:** AI drafts reply based on ticket body + knowledge base
- **Sentiment analysis:** Detects user frustration, flags angry tickets
- **Predictive SLA:** AI estimates resolution time
- **Knowledge gap detection:** Identifies missing KB articles from ticket patterns

### C. Real-Time Live Chat
- Laravel Reverb WebSocket-powered
- Guest chat → ticket conversion
- Agent assignment
- File sharing in chat
- Typing indicators
- Chat history persistence
- Unread message badges

### D. Knowledge Base Management
- Article CRUD with rich text
- Hierarchical categories
- FAQ system
- Helpful / Not helpful voting
- Search with full-text
- Featured articles
- View tracking
- Related articles

### E. Analytics & Reporting
- Dashboard with key metrics
- Ticket volume over time (charts)
- Agent performance (response time, resolution time)
- Department workload
- Customer satisfaction trends
- SLA compliance rate
- AI usage & cost tracking
- Export to CSV/PDF

### F. User & Organization Management (RBAC)
- Roles: Admin, Agent, Customer
- Permissions via Spatie (granular per action)
- Department membership
- User profile with avatar
- Impersonation (admin → user)
- Status (active/inactive)
- Last active tracking

### G. Productivity & Automation
- **Automation rules:** event → condition → action pipeline
  - Events: ticket.created, ticket.replied, ticket.escalated, sla.breached
  - Conditions: priority == urgent, department == billing, etc.
  - Actions: assign_to, set_priority, send_email, add_tag
- **SLA policies:** first response time, resolution time, per priority, per department
- **Canned responses:** template library with category, quick insert
- **Email templates:** customizable per notification type
- **Notifications:** database + email, read/unread tracking

### H. Email Piping
- Inbound email → automatic ticket creation
- Email → ticket reply
- Attachment extraction from email
- Email template for outbound notifications

### I. REST API (137+ endpoints)
- Sanctum token authentication
- Full CRUD for tickets, knowledge base, users
- AI endpoints (classify, suggest, sentiment)
- Analytics endpoints
- Rate limiting: 60 req/min

### J. Programmatic SEO
- `/best-helpdesk-software-{year}` — top 10 list
- `/compare/{a}-vs-{b}` — head-to-head comparison
- `/alternatives-to/{slug}` — alternatives directory
- JSON-LD schema, meta tags, 300+ word content
- Auto-generated sitemap.xml
- robots.txt configuration

### K. Security Features
- Sanctum token auth (API)
- CSRF protection (web)
- XSS prevention (content escaping)
- SQL injection protection (Eloquent ORM)
- Encrypted API keys at rest (AES-256 via Laravel Crypt)
- Audit logging (all admin actions)
- Rate limiting
- CSP headers
- 2FA support

### L. PWA & Mobile Support
- Service worker
- Offline fallback page
- Install prompt
- Push notifications

---

## User Stories

### Ticket Management

| ID | As a... | I want to... | So that... |
|----|---------|-------------|-----------|
| US-01 | Customer | Submit a support ticket via web form | I can get help without emailing |
| US-02 | Customer | See all my ticket history with status | I don't re-submit duplicates |
| US-03 | Agent | See tickets assigned to me | I know what to work on |
| US-04 | Agent | Reply to a ticket with internal notes | I can collaborate without customer seeing |
| US-05 | Agent | Use canned responses | I reply faster to common questions |
| US-06 | Admin | Assign tickets to departments | Tickets go to the right team |
| US-07 | Admin | Set SLA policies | We meet response commitments |
| US-08 | Admin | Bulk close/assign tickets | I can clean up efficiently |

### AI Intelligence

| ID | As a... | I want to... | So that... |
|----|---------|-------------|-----------|
| US-09 | Agent | AI to suggest a reply draft | I respond 3x faster |
| US-10 | Agent | AI to classify incoming tickets | Routing is automatic |
| US-11 | Admin | See sentiment on angry tickets | I can prioritize de-escalation |
| US-12 | Admin | Configure which AI provider to use | I control costs and models |

### Live Chat

| ID | As a... | I want to... | So that... |
|----|---------|-------------|-----------|
| US-13 | Customer | Chat with support in real-time | I get instant answers |
| US-14 | Agent | See typing indicators | I know customer is still typing |
| US-15 | Agent | Convert chat to a ticket | Follow-up is tracked |

### Knowledge Base

| ID | As a... | I want to... | So that... |
|----|---------|-------------|-----------|
| US-16 | Customer | Search knowledge base | I self-serve without opening a ticket |
| US-17 | Customer | Mark article as helpful/not | Content quality improves |
| US-18 | Admin | Create knowledge articles | Agents have reference material |

### Automation

| ID | As a... | I want to... | So that... |
|----|---------|-------------|-----------|
| US-19 | Admin | Auto-assign urgent tickets | No high-priority ticket sits unseen |
| US-20 | Admin | Auto-close resolved tickets after 48h | Ticket list stays clean |

---

## Non-Functional Requirements

### Performance
- Page load < 2 seconds (TTFB < 200ms)
- API response < 200ms (p95)
- WebSocket message delivery < 100ms
- Support 10,000 concurrent users
- Support 100,000 tickets in DB

### Security
- All API keys encrypted at rest (AES-256)
- HTTPS enforced in production
- Rate limiting on all API/chat endpoints
- XSS: all user content HTML-escaped
- CSRF: all state-changing requests protected
- SQL injection: Eloquent parameterized queries only
- File upload: whitelist extensions, scan for malware
- Password: bcrypt with 12 rounds
- 2FA: TOTP support via authenticator app
- Session: database driver, encrypted
- GDPR: data export, right to deletion

### Internationalization (i18n)
- Default: English
- Supported: 15+ languages (Arabic, Chinese, Dutch, English, French, German, Hindi, Indonesian, Italian, Japanese, Korean, Portuguese, Russian, Spanish, Turkish)
- RTL support for Arabic
- Language auto-detection from browser

### Scalability
- Database queue driver (Redis for production)
- Cache driver (Redis for production)
- Horizontal scaling via load balancer + shared DB
- WebSocket: Reverb supports horizontal scaling via Redis pub/sub

### Maintainability
- PSR-12 coding standard
- Full test coverage (target 80%+)
- Comprehensive documentation
- Migration-based schema management

---

## Success Metrics

| Metric | Target |
|--------|--------|
| First response time | < 15 minutes (business hours) |
| Resolution time | < 4 hours (average) |
| Customer satisfaction | > 90% |
| Ticket deflection via KB | > 30% |
| Agent productivity lift (AI) | 3× faster replies |
| AI classification accuracy | > 85% |
| System uptime | 99.9% |

---

## MVP Scope vs Future Phases

### MVP (v1.0.0) — Phase 0 + Phase 1

**Included:**
- Laravel scaffold with Breeze (Blade) + Tabler CSS + Alpine.js
- Database schema (30 tables)
- 26 Eloquent models
- Authentication (login, register, password reset, email verification)
- RBAC (admin, agent, customer roles)
- Ticket management (CRUD, status lifecycle, priorities, assignments)
- Ticket replies with attachments
- Internal notes
- Department & category management
- Live chat via Reverb WebSocket
- Knowledge base (articles, categories, FAQs)
- AI provider system (fully dynamic, no hardcoded providers)
- AI feature configs (per-feature provider picker)
- AI usage logging & cost tracking
- Canned responses
- SLA policies
- Automation rules
- Email templates
- Settings system
- Services management
- Blog system (posts)
- Notifications (database)
- Activity logging (audit trail)
- SEO meta integration
- API keys management
- Programmatic SEO (3 patterns)
- Admin dashboard
- User dashboard
- REST API structure

**Not in MVP (Planned for Phase 2+):**
- Frontend landing page (polished)
- Admin dashboard UI (polished with charts)
- Settings UI (full admin panel)
- Kanban board UI
- PDF ticket export
- 2FA UI
- Chatbot (AI auto-responder)
- Multi-tenancy
- SSO/SAML
- Mobile app (Flutter)
- Customer portal customization (whitelabel colors)

---

## Dependencies & Integrations

### Third-Party Packages

| Package | Version | Purpose |
|---------|---------|---------|
| laravel/framework | 13.x | Core framework |
| laravel/breeze | 2.4+ | Auth scaffolding (Blade) |
| laravel/reverb | 1.10+ | WebSocket server |
| laravel/sanctum | 4.x | API token auth |
| laravel/tinker | 3.x | Interactive shell |
| spatie/laravel-permission | 7.4+ | Roles & permissions |
| barryvdh/laravel-dompdf | 3.x | PDF generation |
| @tabler/core | 1.6+ | Tabler CSS/JS via npm (local Vite bundle, no CDN) |
| alpinejs | 3.x | Lightweight JS interactions in Blade views |
| apexcharts | 7.x | Dashboard charts via npm |

### Infrastructure

- **Web Server:** Nginx or Apache
- **Database:** MySQL 8.0+ or SQLite (dev)
- **Queue:** Database (dev) or Redis (production)
- **Cache:** Database (dev) or Redis (production)
- **WebSocket:** Laravel Reverb (standalone process)
- **Email:** SMTP or log driver (dev)
- **File Storage:** Local or S3-compatible
