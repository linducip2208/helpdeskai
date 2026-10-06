# 05 — Complete Feature Documentation

---

## 1. Advanced Ticket Management

### Description
Full lifecycle ticket system with CRUD, status workflow, priority management, agent assignment, custom fields, file attachments, and internal notes.

### Status Workflow
```
Open → In Progress → Waiting → Resolved → Closed
  ↓                                              ↑
  └──────────────────── (Reopen) ─────────────────┘
```

### Priority Levels
| Priority | Color | Typical SLA |
|----------|-------|-------------|
| Low | `#10b981` (green) | 24 hours |
| Medium | `#f59e0b` (amber) | 8 hours |
| High | `#f97316` (orange) | 2 hours |
| Urgent | `#ef4444` (red) | 15 minutes |

### Sources
Tickets can originate from: **Web** (form), **Email** (piping), **Chat** (conversion), **API**

### Features
- **UID system:** Auto-generated unique IDs (`TKT-A3B9X`) for reference
- **Custom fields:** JSON column for arbitrary metadata
- **Star/flag:** Mark important tickets for follow-up
- **Internal notes:** Agent-only replies (`is_internal = true`)
- **File attachments:** Upload files on ticket creation or replies
- **Bulk operations:** Assign, close, or delete multiple tickets
- **SLA tracking:** `sla_due_at` auto-calculated from SLA policies
- **Inline status/priority colors:** Computed via model accessors

### How to Use
1. Customer submits ticket via `/dashboard/tickets/create`
2. AI auto-classifies category, department, priority
3. Agent sees ticket in their queue, can assign, reply, add internal notes
4. Status flows through the pipeline until resolved/closed
5. All activity logged in `activity_logs`

---

## 2. AI-Powered Intelligence

### Description
AI co-pilot that assists agents throughout the ticket lifecycle. All AI features use the fully dynamic provider system — no hardcoded model or provider.

### Features

#### a) Auto-Classify (`feature_key: ticket.classify`)
On ticket creation, AI analyzes subject + body and suggests:
- Category (e.g., "Billing", "Technical")
- Department (e.g., "IT", "Finance")
- Priority (based on urgency keywords)

**Configuration:** Provider + model set in `ai_feature_configs` for `ticket.classify`.

#### b) Response Suggestion (`feature_key: ticket.suggest`)
When agent opens a ticket, AI generates a draft reply based on:
- Ticket body
- Conversation history
- Knowledge base articles (RAG)
- Canned responses

Agent can accept, edit, or ignore the suggestion.

#### c) Sentiment Analysis (`feature_key: ticket.sentiment`)
AI analyzes the emotional tone of each message:
- **Positive** — customer is satisfied
- **Neutral** — standard inquiry
- **Negative** — frustrated, urgent attention needed
- **Angry** — escalation risk

A visual indicator shows sentiment on the ticket view. Angry tickets are flagged for priority handling.

#### d) Predictive SLA (`feature_key: ticket.predict`)
AI estimates resolution time based on:
- Historical resolution patterns for similar tickets
- Current agent workload
- Ticket complexity (length, attachments)

`sla_due_at` is set or adjusted accordingly.

#### e) Knowledge Gap Detection (`feature_key: kb.gap`)
AI analyzes clusters of resolved tickets to identify topics lacking knowledge base articles. Admin receives suggestions for new articles.

### Cost Tracking
Every AI call is logged in `ai_usage_logs` with:
- Tokens consumed (input + output)
- Estimated cost (based on user-defined pricing in `ai_provider_models`)
- Latency (ms)
- Success/failure

### How to Configure
1. Add an AI provider: `Admin → AI Providers → Add New`
2. Add models under that provider
3. Enable AI features: `Admin → AI Features` — pick provider + model per feature
4. Set options (temperature, max_tokens, system_prompt) per feature

---

## 3. Real-Time Live Chat (Reverb WebSocket)

### Description
WebSocket-powered live chat using Laravel Reverb. Customers can chat with support agents in real-time. Chats can be converted to tickets for follow-up.

### Architecture
```
Browser (Laravel Echo) ←→ Reverb Server ←→ Laravel App (Event Broadcasting)
```

### Features
- **Real-time messaging:** < 100ms delivery via WebSocket
- **Guest chat:** Unauthenticated users can start chat (converts to ticket on auth)
- **Agent assignment:** Auto or manual assignment
- **Typing indicators:** Real-time "user is typing..." display
- **File sharing:** Images, documents via message `type: file`
- **Chat history:** Persistent, searchable
- **Unread badges:** Red badge on conversations with unread messages
- **System messages:** `type: system` for join/leave/assign events
- **Chat → ticket conversion:** One-click create ticket from conversation

### How to Start

**Server:**
```bash
php artisan reverb:start
```

**Environment (`.env`):**
```env
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=helpdeskai
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
```

**Client (JS):**
```js
import Echo from 'laravel-echo';
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: window.location.hostname,
    wsPort: 8080,
});
```

### Routes
| Method | URL | Auth | Description |
|--------|-----|------|-------------|
| GET | `/conversations` | Yes | List conversations |
| GET | `/conversations/{id}` | Yes | Show conversation |
| POST | `/conversations` | Yes | Start new conversation |
| POST | `/conversations/{id}/message` | Yes | Send message |
| DELETE | `/admin/conversations/{id}` | Admin | Close conversation |

---

## 4. Knowledge Base Management

### Description
Self-service knowledge base with articles, FAQs, categories, voting, and search.

### Features
- **Articles:** Rich text content with `draft → published → archived` workflow
- **Categories:** Hierarchical (unlimited nesting via `parent_id`)
- **FAQs:** Question/answer pairs per category
- **Voting:** Helpful / Not helpful counters on each article
- **Search:** Full-text search across articles
- **Featured articles:** Pinned to category top
- **View tracking:** `view_count` per article
- **Related articles:** Category-based suggestions
- **SEO:** Per-article meta title, description, keywords
- **Author tracking:** Who wrote/edited

### How to Use

**Admin:**
1. Create categories: `Admin → Knowledge → Create Category`
2. Write articles: `Admin → Knowledge → Create Article`
3. Add FAQs: `Admin → Knowledge → Create FAQ`
4. Set status to `published` when ready

**Customer:**
1. Browse: `/knowledge-base`
2. Search: `/kb/search?q=keyword`
3. Vote: "Was this helpful? Yes / No"
4. Self-serve without creating ticket

### Public Routes
| Method | URL | Description |
|--------|-----|-------------|
| GET | `/knowledge-base` | Category listing |
| GET | `/knowledge-base/category/{slug}` | Articles in category |
| GET | `/knowledge-base/article/{slug}` | Full article |
| GET | `/kb/search` | Search articles |

---

## 5. Analytics & Reporting

### Description
Dashboard and reporting system to track support performance, agent productivity, and trends.

### Metrics Tracked
| Metric | Description |
|--------|-------------|
| Total Tickets | All-time count |
| Open Tickets | Currently open |
| Resolved Today | Closed in last 24h |
| Average Response Time | Time to first reply |
| Average Resolution Time | Time from open to closed |
| SLA Compliance % | Tickets resolved within SLA |
| Tickets by Department | Volume breakdown |
| Tickets by Priority | Urgent vs normal mix |
| Agent Performance | Per-agent response/resolution times |
| Customer Satisfaction | Based on sentiment + feedback |
| AI Usage | Tokens consumed, cost, calls |
| Ticket Sources | web vs email vs chat vs api |

### Chart Data
API endpoint `GET /admin/analytics/chart-data` returns JSON for:
- Ticket volume over time (daily/weekly/monthly)
- Resolution time trend
- Priority distribution pie
- Source distribution pie

### Export
- CSV export of ticket/agent data (planned)

---

## 6. User & Organization Management (RBAC)

### Description
Role-based access control using Spatie Laravel Permission with 3 core roles.

### Roles & Permissions

| Role | Access |
|------|--------|
| **Admin** | Full system access: all tickets, users, settings, AI config, analytics, activity logs |
| **Agent** | Tickets assigned to them, chat, knowledge base authoring, canned responses |
| **Customer** | Own tickets, conversations, knowledge base (read-only) |

### Features
- **Granular permissions:** Per-action control via Spatie
- **Department membership:** Agents belong to departments
- **Impersonation:** Admin can log in as any user
- **User profile:** Avatar, phone, timezone
- **Active/inactive toggle:** Disable accounts without deleting
- **Last active tracking:** `last_active_at` timestamp
- **API keys:** Per-user API tokens for REST access

### Admin User Management
| Method | URL | Description |
|--------|-----|-------------|
| GET | `/admin/users` | List all users |
| GET/POST | `/admin/users/create` | Create user |
| GET/PUT | `/admin/users/{id}/edit` | Edit user (role, status) |
| DELETE | `/admin/users/{id}` | Delete user |
| POST | `/admin/users/{id}/impersonate` | Login as user |

---

## 7. Productivity & Automation

### a) Automation Rules

Event-driven pipeline: **Trigger → Conditions → Actions**

**Triggers:**
- `ticket.created` — new ticket submitted
- `ticket.replied` — customer replied
- `ticket.escalated` — priority escalated
- `sla.breached` — SLA deadline missed

**Conditions:** Any combination of field comparisons (equals, not_equals, in, contains, greater_than, less_than)

**Actions:**
- `assign_to` — auto-assign to agent
- `set_priority` — change priority
- `set_category` — move to category
- `set_status` — change status
- `send_email` — trigger email template
- `add_tag` — add internal tag
- `send_notification` — push notification

**Admin URL:** `/admin/automation-rules`

### b) SLA Policies

Define service level agreements per department and priority.

| Field | Description |
|-------|-------------|
| Name | Policy name |
| Department | Which department |
| Priority | Low/Medium/High/Urgent |
| First Response Time | Minutes to first reply |
| Resolution Time | Minutes to resolve |

`sla_due_at` on tickets is calculated: `created_at + first_response_time`

**Admin URL:** `/admin/sla-policies`

### c) Canned Responses

Pre-written reply templates organized by category.

**Features:**
- Category grouping (same as ticket categories)
- Quick insert in ticket reply
- Slug-based identification
- Active/inactive toggle

**Admin URL:** `/admin/canned-responses`

### d) Email Templates

Customizable email notifications.

| Key | Trigger |
|-----|---------|
| `ticket.created` | New ticket |
| `ticket.replied` | New reply |
| `ticket.assigned` | Agent assigned |
| `ticket.resolved` | Ticket resolved |
| `welcome` | Registration |

**Admin URL:** `/admin/email-templates`

---

## 8. Email Piping

### Description
Customers can create and reply to tickets via email without logging into the web portal.

### How It Works
1. Configure mail server to pipe incoming email to Artisan command
2. Laravel parses email (from, subject, body, attachments)
3. New subject → creates new ticket
4. Existing UID in subject → appends reply to ticket
5. Attachments auto-extracted

### Setup
```bash
# Cpanel/WHM pipe to program:
php /path/to/artisan email:parse

# Or via webhook (Mailgun, SendGrid, Postmark):
POST /api/email/webhook
```

---

## 9. Multi-Language Support (i18n)

### Description
15+ languages supported via Laravel localization.

### Supported Languages
`ar` (Arabic), `zh` (Chinese), `nl` (Dutch), `en` (English), `fr` (French), `de` (German), `hi` (Hindi), `id` (Indonesian), `it` (Italian), `ja` (Japanese), `ko` (Korean), `pt` (Portuguese), `ru` (Russian), `es` (Spanish), `tr` (Turkish)

### Features
- Browser language auto-detection
- Manual language switcher
- RTL support for Arabic
- Translation files: `lang/{locale}/*.php`
- Middleware: `SetLocale` reads from session/cookie

### Configuration
```env
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
```

---

## 10. REST API (137+ endpoints)

### Description
Full REST API with Sanctum token authentication for integration with external systems.

### Authentication
```http
Authorization: Bearer {api_key}
```

### Rate Limiting
60 requests per minute per token.

### Endpoint Categories
- **Tickets:** CRUD, status change, assignment, starring
- **Knowledge Base:** Articles, categories, FAQs, search, voting
- **Chat:** Conversations, messages
- **AI:** Classify, suggest, sentiment, summarize
- **Users:** Profile, API keys
- **Admin:** Analytics, settings, departments, categories
- **pSEO:** Best pages, comparisons, alternatives data

> **Full API reference:** See `docs/06-API.md`

---

## 11. Programmatic SEO

### Description
Built-in SEO landing pages that auto-generate from database content.

### URL Patterns
| Pattern | Page | Data Source |
|---------|------|-------------|
| `/best-helpdesk-software-{year}` | Top 10 ranking | `services` table |
| `/compare/{a}-vs-{b}` | Side-by-side comparison | `services` table |
| `/alternatives-to/{slug}` | Alternatives list | `services` table |

### SEO Components per Page
- Dynamic `<title>` and `<meta description>`
- JSON-LD structured data (ItemList, FAQPage)
- Open Graph tags
- Twitter Card tags
- Canonical URL
- 300+ words unique content
- Inline FAQ section

> **Full pSEO spec:** See `docs/08-PSEO.md`

---

## 12. Security Features

### Description
Comprehensive security measures across authentication, data protection, and attack prevention.

### Features
- **CSRF Protection:** All state-changing routes protected
- **XSS Prevention:** Blade auto-escaping + Content Security Policy headers
- **SQL Injection:** Eloquent ORM parameterized queries
- **API Key Encryption:** AES-256 via Laravel Crypt (`api_key_encrypted`)
- **Rate Limiting:** 60 req/min on API, 6 req/min on auth endpoints
- **Audit Logging:** All admin actions logged in `activity_logs`
- **Password Hashing:** bcrypt with 12 rounds
- **2FA Support:** TOTP (planned)
- **File Upload Security:** Whitelist extensions, mime-type validation
- **Session Security:** Database driver, encrypted data

> **Full security spec:** See `docs/09-SECURITY.md`

---

## 13. PWA & Mobile Support

### Description
Progressive Web App support for mobile-friendly access.

### Features
- Service worker registration
- Offline fallback page
- "Add to Home Screen" prompt
- Push notification support (planned)
- Responsive Tabler CSS design (mobile-first, local Vite bundle from npm, no CDN)

### PWA Manifest
Located at `public/manifest.json` (to be created).

---

## Feature Enablement Matrix

| Feature | Requires | Default |
|---------|----------|---------|
| Ticket Management | None | Enabled |
| AI Classify | AI Provider + Model configured | Disabled |
| AI Suggest | AI Provider + Model configured | Disabled |
| AI Sentiment | AI Provider + Model configured | Disabled |
| Live Chat | Reverb server running | Enabled (requires Reverb) |
| Knowledge Base | None | Enabled |
| Email Piping | Mail server config + Artisan command | Requires setup |
| SLA Policies | SLA policy records created | No policies by default |
| Automation Rules | Automation rule records created | No rules by default |
| REST API | API key generated | Requires key |
| pSEO | Service records in DB | Enabled (needs data) |
| 2FA | User activation | Opt-in per user |
| PWA | Service worker built | Planned |

---

## Configuration Dependencies

```env
# AI Features (user-configured, not hardcoded)
# No env vars needed — all provider config in DB

# Live Chat
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=helpdeskai
REVERB_APP_KEY=your-key
REVERB_APP_SECRET=your-secret

# Email
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=...
MAIL_PASSWORD=...

# Queue (for email, notifications)
QUEUE_CONNECTION=database

# i18n
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
```
