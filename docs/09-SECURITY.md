# 09 — Security Documentation

## Overview

HelpDesk AI implements defense-in-depth security across all layers of the application stack. This document covers authentication, authorization, data protection, attack prevention, and compliance considerations.

---

## Authentication & Authorization

### Authentication Methods

| Method | Use Case | Implementation |
|--------|----------|---------------|
| Session (cookie) | Web UI (browser) | Laravel Breeze + Sanctum SPA auth |
| Bearer Token | REST API | Laravel Sanctum API tokens |
| API Key Header | REST API (alt) | `X-Api-Key` header |
| API Key Query | REST API (fallback) | `?api_key=` parameter |

### Authentication Flow

```
1. User logs in via /login (email + password)
2. Breeze authenticates, creates session
3. Sanctum middleware checks session for SPA requests
4. For API: Bearer token validated against api_keys table
5. Token active check: is_active = true, expires_at > now()
6. Failed attempts: throttled (5 per minute)
```

### Password Policy

| Rule | Implementation |
|------|---------------|
| Minimum length | 8 characters (configurable) |
| Hashing algorithm | bcrypt |
| Bcrypt rounds | 12 |
| Password confirmation | Required for changes |
| Reset token expiry | 60 minutes |
| Reset token uniqueness | Single-use |

### Session Security

| Setting | Value |
|---------|-------|
| Driver | `database` |
| Lifetime | 120 minutes |
| Encryption | Yes |
| HTTP Only | Yes |
| SameSite | `lax` |
| Secure | `true` in production |
| Path | `/` |

---

## Role-Based Access Control (RBAC)

### Implementation
- **Package:** Spatie Laravel Permission v7.4+
- **Roles:** admin, agent, customer
- **Permissions:** granular per-action (view, create, edit, delete per resource)

### Middleware Protection

```php
// Web routes
Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')...

// API routes  
Route::middleware(['auth:sanctum'])->group(function () {
    // User-level endpoints
});

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Admin-level endpoints
});
```

### Permission Checking

```php
// In controllers
if (!auth()->user()->can('edit tickets')) {
    abort(403);
}

// In Blade/Vue
@can('edit tickets')
    <button>Edit</button>
@endcan

// Via middleware
Route::delete('/tickets/{ticket}')->middleware('permission:delete tickets');
```

---

## CSRF Protection

### Web
- All POST/PUT/PATCH/DELETE requests require CSRF token
- Token embedded in meta tag + automatically included by Inertia
- `VerifyCsrfToken` middleware active on all web routes

### API
- CSRF protection **NOT** applied to `api/*` routes
- API routes use token auth instead
- `routes/api.php` excluded from CSRF middleware group

### Excluded Routes
```php
// In bootstrap/app.php
->withMiddleware(function (Middleware $middleware) {
    $middleware->validateCsrfTokens(except: [
        'stripe/*',       // Webhook endpoints
        'webhooks/*',     // External webhooks
    ]);
})
```

---

## XSS Prevention

### Output Escaping
- **Blade:** `{{ $variable }}` auto-escapes HTML
- **Vue:** `{{ variable }}` auto-escapes HTML
- **Raw output:** `{!! $variable !!}` avoided; sanitized when necessary

### Content Security Policy (CSP)
```http
Content-Security-Policy:
  default-src 'self';
  script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net;
  style-src 'self' 'unsafe-inline' https://fonts.googleapis.com;
  img-src 'self' data: https:;
  font-src 'self' https://fonts.gstatic.com;
  connect-src 'self' wss://* ws://*;
  frame-ancestors 'none';
  form-action 'self';
```

### Rich Text Content
- Knowledge base articles and ticket bodies accept HTML
- Sanitize before rendering using HTMLPurifier or similar
- Strip `<script>`, `<iframe>`, `on*` event handlers

---

## SQL Injection Protection

### Eloquent ORM
All database queries use parameterized statements via Eloquent:
```php
// SAFE — parameterized
User::where('email', $email)->first();
Ticket::where('status', $status)->where('priority', $priority)->get();

// SAFE — parameterized
DB::select('SELECT * FROM tickets WHERE status = ?', [$status]);

// UNSAFE — NEVER do this
DB::select("SELECT * FROM tickets WHERE status = '{$status}'");
```

### Raw Query Precautions
When raw queries are necessary:
- Use parameter binding: `DB::select('...', [$param1, $param2])`
- Never concatenate user input into SQL strings
- Use `DB::raw()` only with hardcoded values

---

## API Key Encryption at Rest

All third-party API keys (AI providers) are encrypted with AES-256-CBC:

```php
// Storing
$provider->api_key_encrypted = Crypt::encryptString($rawApiKey);

// Retrieving (only in memory, never persisted decrypted)
$decrypted = Crypt::decryptString($provider->api_key_encrypted);

// Accessor on model
$provider->decrypted_api_key; // Decrypts on access, not stored
```

### Key Exposure Prevention
- Decrypted keys **never** appear in:
  - API responses (masked: `sk-...a1b2`)
  - Log files
  - Error messages
  - Cache/session storage
  - Database dumps (column is encrypted at rest)
- `api_key_encrypted` excluded from model serialization

---

## Audit Logging

### What is Logged
All admin actions and critical user actions:

| Action | Target | Metadata |
|--------|--------|----------|
| created | Ticket, User, Article | Full attributes |
| updated | Ticket, User, Article | Changed fields |
| deleted | Ticket, User, Article | ID + name |
| assigned | Ticket | Old agent → new agent |
| status_changed | Ticket | Old status → new status |
| login | User | IP, user agent |
| api_key_used | ApiKey | Endpoint called |
| ai_call | AiUsageLog | Tokens, cost, latency |

### Storage
```sql
-- activity_logs table
user_id       — Who performed the action
action        — What happened
target_type   — Polymorphic target model
target_id     — Polymorphic target ID
target_label  — Human-readable target name
meta          — JSON with before/after values
ip_address    — Request IP
created_at    — Timestamp
```

### Retention
- Activity logs retained indefinitely (admin-level)
- Can be pruned via scheduled command: `activity:prune --days=365`

---

## Rate Limiting

### API Rate Limiting
| Limit | Window | Scope |
|-------|--------|-------|
| 60 requests | 1 minute | Per API token |
| 6 requests | 1 minute | Auth endpoints (login, register, password reset) |
| 100 requests | 1 minute | Public pSEO pages |

### Implementation
```php
// In routes/api.php
Route::middleware('throttle:60,1')->group(function () { ... });

// Custom throttle for auth
Route::middleware('throttle:6,1')->group(function () {
    Route::post('/login', ...);
    Route::post('/register', ...);
    Route::post('/forgot-password', ...);
});
```

### Response Headers
```http
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 58
Retry-After: 42
```

### Rate Limit Exceeded Response
```json
{
  "message": "Too Many Requests",
  "retry_after": 42
}
```
HTTP Status: `429`

---

## File Upload Security

### Validation Rules

| Rule | Description |
|------|-------------|
| Extensions | Whitelist only: jpg, jpeg, png, gif, pdf, doc, docx, xls, xlsx, csv, txt, zip |
| Max size | 10 MB per file |
| MIME type | Verified server-side (not trusted from client) |
| Filename | Sanitized — special chars removed, unique name generated |

### Storage
- Uploaded files stored in `storage/app/public/uploads/` (not web-accessible directly)
- Access via `Storage::disk('public')->url()` which generates proper URLs
- Execute permission removed on stored files
- Virus scanning recommended in production (ClamAV integration)

### Image Processing
- Large images resized before storage
- EXIF data stripped from uploaded images (prevents GPS leak)

---

## Data Encryption

### At Rest
| Data | Encryption |
|------|-----------|
| AI provider API keys | AES-256-CBC (Laravel Crypt) |
| User passwords | bcrypt (12 rounds) |
| Session data | Database driver + encrypted payload |
| Sensitive settings | Optional: encrypted settings column |

### In Transit
- HTTPS enforced in production
- HSTS header recommended
- TLS 1.2 minimum

---

## 2FA Support

### Architecture (Planned)
- TOTP-based (Time-based One-Time Password)
- Compatible with Google Authenticator, Authy, 1Password
- Per-user opt-in

### Database Fields (users table)
```
two_factor_secret      — Encrypted TOTP secret
two_factor_enabled     — Boolean toggle
two_factor_recovery_codes — JSON array of hashed codes
```

### Flow
```
1. User enables 2FA in profile settings
2. QR code displayed (via qrserver.com API)
3. User scans with authenticator app
4. User verifies with one-time code
5. Recovery codes generated and displayed (one-time view)
6. Subsequent logins require TOTP code
```

---

## GDPR Considerations

### User Rights
| Right | Implementation |
|-------|---------------|
| Right to access | Export all user data as JSON |
| Right to rectification | Edit profile, ticket content |
| Right to erasure | Account deletion cascade |
| Right to portability | Data export endpoint |
| Right to restrict | Account suspension (preserves data) |
| Right to object | Opt-out of non-essential emails |

### Data Export
```bash
php artisan user:export {email} --format=json
```

### Account Deletion
- Cascade deletes: tickets, replies, conversations, API keys
- Activity logs preserved (anonymized — user_id set to null)
- AI usage logs preserved (anonymized)
- 30-day grace period before permanent deletion (soft delete)

### Cookie Consent
- Essential cookies (session): no consent needed
- Analytics cookies: opt-in consent banner (if added)
- No third-party tracking cookies by default

---

## Security Checklist for Deployment

### Pre-Launch
- [ ] `APP_DEBUG=false` in `.env`
- [ ] `APP_ENV=production`
- [ ] `APP_KEY` is a strong, random 32-char string
- [ ] Database password is strong and not default
- [ ] HTTPS configured (Let's Encrypt or commercial SSL)
- [ ] HSTS header enabled
- [ ] CSP header configured
- [ ] File permissions: `storage/` and `bootstrap/cache/` writable, everything else read-only
- [ ] `.env` not exposed (blocked by web server)
- [ ] `composer.lock` committed, dependencies audited: `composer audit`

### Authentication
- [ ] Default admin password changed
- [ ] Bcrypt rounds ≥ 12
- [ ] Session lifetime appropriate (120 min default)
- [ ] Rate limiting active on auth endpoints
- [ ] 2FA available for admin accounts

### API
- [ ] API rate limiting active (60 req/min)
- [ ] API keys encrypted at rest
- [ ] Expired API keys automatically rejected
- [ ] Inactive API keys rejected

### Data
- [ ] Database backups scheduled (daily minimum)
- [ ] Backup encryption enabled
- [ ] File upload size limits enforced
- [ ] File upload type whitelist enforced

### Monitoring
- [ ] Error logging configured (Sentry, Flare, or log files)
- [ ] Failed login monitoring
- [ ] Rate limit exceeded alerts
- [ ] File integrity monitoring (optional)
- [ ] SSL certificate expiry monitoring

### Post-Launch
- [ ] Run security scanner: `composer audit`
- [ ] Run: `php artisan security:check` (if available)
- [ ] Penetration test (optional but recommended for enterprise)
- [ ] Review access logs for anomalies
- [ ] Subscribe to Laravel security advisories
- [ ] Set up automated dependency updates (Dependabot)

---

## Incident Response

### If a Security Issue is Found
1. **Contain** — revoke affected API keys, disable affected accounts
2. **Investigate** — check `activity_logs`, `ai_usage_logs`, server access logs
3. **Fix** — patch the vulnerability
4. **Notify** — inform affected users (if personal data exposed)
5. **Report** — file with relevant authorities if required by law
6. **Post-mortem** — document root cause and prevention

### Responsible Disclosure
Report security vulnerabilities to: `security@helpdeskai.com`
Do NOT open public issues for security vulnerabilities.
