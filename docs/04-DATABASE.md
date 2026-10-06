# 04 — Detailed Database Schema

## Migration Order

Migrations are executed in filename order. Always run `php artisan migrate` to apply them sequentially.

```
Order  | Migration File                                  | Tables Created
-------|------------------------------------------------|-------------------------------
1      | 0001_01_01_000000_create_users_table           | users, password_reset_tokens, sessions
2      | 0001_01_01_000001_create_cache_table           | cache, cache_locks
3      | 0001_01_01_000002_create_jobs_table            | jobs, job_batches, failed_jobs
4      | 2026_05_03_195829_create_permission_tables     | permissions, roles, model_has_permissions, model_has_roles, role_has_permissions
5      | 2026_05_04_000001_create_departments_table     | departments
6      | 2026_05_04_000002_create_categories_table      | categories
7      | 2026_05_04_000003_add_role_to_users_table      | (adds columns to users)
8      | 2026_05_04_000004_create_tickets_table         | tickets
9      | 2026_05_04_000005_create_ticket_replies_table  | ticket_replies
10     | 2026_05_04_000006_create_ticket_attachments_table | ticket_attachments
11     | 2026_05_04_000007_create_conversations_table   | conversations
12     | 2026_05_04_000008_create_conversation_messages_table | conversation_messages
13     | 2026_05_04_000009_create_knowledge_categories_table | knowledge_categories
14     | 2026_05_04_000010_create_knowledge_articles_table | knowledge_articles
15     | 2026_05_04_000011_create_knowledge_faqs_table  | knowledge_faqs
16     | 2026_05_04_000012_create_ai_providers_table    | ai_providers
17     | 2026_05_04_000013_create_ai_provider_models_table | ai_provider_models
18     | 2026_05_04_000014_create_ai_feature_configs_table | ai_feature_configs
19     | 2026_05_04_000015_create_ai_usage_logs_table   | ai_usage_logs
20     | 2026_05_04_000016_create_canned_responses_table | canned_responses
21     | 2026_05_04_000017_create_sla_policies_table    | sla_policies
22     | 2026_05_04_000018_create_automation_rules_table | automation_rules
23     | 2026_05_04_000019_create_email_templates_table | email_templates
24     | 2026_05_04_000020_create_settings_table        | settings
25     | 2026_05_04_000021_create_services_table        | services
26     | 2026_05_04_000022_create_posts_table           | posts
27     | 2026_05_04_000023_create_notifications_table   | notifications
28     | 2026_05_04_000024_create_activity_logs_table   | activity_logs
29     | 2026_05_04_000025_create_seo_meta_table        | seo_meta
30     | 2026_05_04_000026_create_api_keys_table        | api_keys
```

---

## Complete Table Definitions

---

### 1. users

```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    role ENUM('admin', 'agent', 'customer') NOT NULL DEFAULT 'customer',
    avatar VARCHAR(255) NULL,
    phone VARCHAR(255) NULL,
    timezone VARCHAR(255) NULL,
    last_active_at TIMESTAMP NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

**Indexes:** `email` UNIQUE

**Relationships:**
- HasMany: tickets, ticketReplies, conversations, apiKeys, activityLogs
- BelongsToMany: roles (via model_has_roles)

**Casts:**
- `email_verified_at` → datetime
- `password` → hashed
- `last_active_at` → datetime
- `is_active` → boolean

---

### 2. password_reset_tokens

```sql
CREATE TABLE password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL
);
```

---

### 3. sessions

```sql
CREATE TABLE sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload LONGTEXT NOT NULL,
    last_activity INT NOT NULL
);
```

**Indexes:** `user_id`, `last_activity`

**Foreign Keys:** `user_id` → `users.id`

---

### 4. cache

```sql
CREATE TABLE cache (
    `key` VARCHAR(255) PRIMARY KEY,
    value MEDIUMTEXT NOT NULL,
    expiration INT NOT NULL
);
```

---

### 5. cache_locks

```sql
CREATE TABLE cache_locks (
    `key` VARCHAR(255) PRIMARY KEY,
    owner VARCHAR(255) NOT NULL,
    expiration INT NOT NULL
);
```

---

### 6. jobs

```sql
CREATE TABLE jobs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    queue VARCHAR(255) NOT NULL,
    payload LONGTEXT NOT NULL,
    attempts TINYINT UNSIGNED NOT NULL,
    reserved_at INT UNSIGNED NULL,
    available_at INT UNSIGNED NOT NULL,
    created_at INT UNSIGNED NOT NULL
);
```

**Indexes:** `queue`

---

### 7. job_batches

```sql
CREATE TABLE job_batches (
    id VARCHAR(255) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    total_jobs INT NOT NULL,
    pending_jobs INT NOT NULL,
    failed_jobs INT NOT NULL,
    failed_job_ids LONGTEXT NOT NULL,
    options MEDIUMTEXT NULL,
    cancelled_at INT NULL,
    created_at INT NOT NULL,
    finished_at INT NULL
);
```

---

### 8. failed_jobs

```sql
CREATE TABLE failed_jobs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(255) UNIQUE NOT NULL,
    connection TEXT NOT NULL,
    queue TEXT NOT NULL,
    payload LONGTEXT NOT NULL,
    exception LONGTEXT NOT NULL,
    failed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

**Indexes:** `uuid` UNIQUE

---

### 9. permissions (Spatie)

```sql
CREATE TABLE permissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    guard_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY (name, guard_name)
);
```

**Seeded permissions (example):**
- `view tickets`, `create tickets`, `edit tickets`, `delete tickets`
- `view users`, `manage users`
- `manage settings`, `manage ai providers`
- `view analytics`, `manage knowledge`

---

### 10. roles (Spatie)

```sql
CREATE TABLE roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    guard_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY (name, guard_name)
);
```

**Seeded roles:**
| Role | Description |
|------|-------------|
| admin | Full system access |
| agent | Ticket management, chat, knowledge base |
| customer | Submit/view tickets, chat, KB access |

---

### 11. model_has_permissions (Spatie)

```sql
CREATE TABLE model_has_permissions (
    permission_id BIGINT UNSIGNED NOT NULL,
    model_type VARCHAR(255) NOT NULL,
    model_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (permission_id, model_id, model_type),
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
);
```

**Indexes:** `(model_id, model_type)`

---

### 12. model_has_roles (Spatie)

```sql
CREATE TABLE model_has_roles (
    role_id BIGINT UNSIGNED NOT NULL,
    model_type VARCHAR(255) NOT NULL,
    model_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (role_id, model_id, model_type),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);
```

**Indexes:** `(model_id, model_type)`

---

### 13. role_has_permissions (Spatie)

```sql
CREATE TABLE role_has_permissions (
    permission_id BIGINT UNSIGNED NOT NULL,
    role_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (permission_id, role_id),
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);
```

---

### 14. departments

```sql
CREATE TABLE departments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

**Relationships:**
- HasMany: tickets, categories, slaPolicies

**Example data:**
| name | description |
|------|-------------|
| Technical Support | Software bugs, errors, integrations |
| Billing | Invoices, payments, refunds |
| Sales | Pre-sales questions, demos |
| General | Other inquiries |

---

### 15. categories

```sql
CREATE TABLE categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    department_id BIGINT UNSIGNED NOT NULL,
    description TEXT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE
);
```

**Indexes:** `slug` UNIQUE

**Relationships:**
- BelongsTo: department
- HasMany: tickets, knowledgeArticles

---

### 16. tickets

```sql
CREATE TABLE tickets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uid VARCHAR(50) NOT NULL UNIQUE,
    user_id BIGINT UNSIGNED NOT NULL,
    assigned_to BIGINT UNSIGNED NULL,
    department_id BIGINT UNSIGNED NOT NULL,
    category_id BIGINT UNSIGNED NOT NULL,
    subject VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    priority ENUM('low', 'medium', 'high', 'urgent') NOT NULL DEFAULT 'medium',
    status ENUM('open', 'in_progress', 'answered', 'resolved', 'closed') NOT NULL DEFAULT 'open',
    source ENUM('web', 'email', 'chat', 'api') NOT NULL DEFAULT 'web',
    sla_due_at DATETIME NULL,
    closed_at DATETIME NULL,
    is_starred TINYINT(1) NOT NULL DEFAULT 0,
    custom_fields JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);
```

**Indexes:** `uid` UNIQUE, `status`, `priority`, `source`, `closed_at`

**Enum values:**
- `status`: open, in_progress, answered, resolved, closed
- `priority`: low, medium, high, urgent
- `source`: web, email, chat, api

**Casts:**
- `status` → TicketStatus enum
- `custom_fields` → array
- `is_starred` → boolean
- `sla_due_at` → datetime
- `closed_at` → datetime

**UID Generation:** `TKT-` prefix + 5 uppercase random characters, collision-checked.

---

### 17. ticket_replies

```sql
CREATE TABLE ticket_replies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    body TEXT NOT NULL,
    is_internal TINYINT(1) NOT NULL DEFAULT 0,
    source ENUM('web', 'email', 'chat', 'api') NOT NULL DEFAULT 'web',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

**Indexes:** `ticket_id`

**Casts:**
- `is_internal` → boolean

**Note:** `is_internal = true` means the reply is only visible to agents/admins, not the customer.

---

### 18. ticket_attachments

```sql
CREATE TABLE ticket_attachments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_id BIGINT UNSIGNED NOT NULL,
    reply_id BIGINT UNSIGNED NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    mime_type VARCHAR(255) NOT NULL,
    size BIGINT NOT NULL,
    path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (reply_id) REFERENCES ticket_replies(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

**Indexes:** `ticket_id`

**Casts:**
- `size` → integer

---

### 19. conversations

```sql
CREATE TABLE conversations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    assigned_to BIGINT UNSIGNED NULL,
    status ENUM('open', 'closed') NOT NULL DEFAULT 'open',
    last_message_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
);
```

**Casts:**
- `last_message_at` → datetime

---

### 20. conversation_messages

```sql
CREATE TABLE conversation_messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conversation_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    body TEXT NOT NULL,
    type ENUM('text', 'file', 'system') NOT NULL DEFAULT 'text',
    metadata JSON NULL,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

**Indexes:** `conversation_id`

**Casts:**
- `metadata` → array
- `read_at` → datetime

---

### 21. knowledge_categories

```sql
CREATE TABLE knowledge_categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT NULL,
    parent_id BIGINT UNSIGNED NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (parent_id) REFERENCES knowledge_categories(id) ON DELETE SET NULL
);
```

**Indexes:** `slug` UNIQUE

**Self-referential:** `parent_id` allows nested category hierarchy.

**Casts:**
- `is_active` → boolean
- `sort_order` → integer

---

### 22. knowledge_articles

```sql
CREATE TABLE knowledge_articles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    category_id BIGINT UNSIGNED NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    content LONGTEXT NOT NULL,
    excerpt TEXT NULL,
    status ENUM('draft', 'published', 'archived') NOT NULL DEFAULT 'draft',
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    view_count INT NOT NULL DEFAULT 0,
    helpful_count INT NOT NULL DEFAULT 0,
    not_helpful_count INT NOT NULL DEFAULT 0,
    meta_title VARCHAR(255) NULL,
    meta_description TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (category_id) REFERENCES knowledge_categories(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

**Indexes:** `slug` UNIQUE, `status`, `is_featured`

**Casts:**
- `is_featured` → boolean
- `view_count` → integer
- `helpful_count` → integer
- `not_helpful_count` → integer

---

### 23. knowledge_faqs

```sql
CREATE TABLE knowledge_faqs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id BIGINT UNSIGNED NULL,
    question TEXT NOT NULL,
    answer TEXT NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (category_id) REFERENCES knowledge_categories(id) ON DELETE SET NULL
);
```

**Casts:**
- `is_active` → boolean
- `sort_order` → integer

---

### 24. ai_providers

```sql
CREATE TABLE ai_providers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    api_format ENUM('openai_compatible', 'anthropic', 'gemini') NOT NULL,
    base_url VARCHAR(500) NOT NULL,
    api_key_encrypted TEXT NOT NULL,
    extra_headers JSON NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_tested_at TIMESTAMP NULL,
    last_test_status VARCHAR(20) NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

**Casts:**
- `extra_headers` → array
- `is_active` → boolean
- `last_tested_at` → datetime

**API Key Encryption:** The `api_key_encrypted` field stores the key encrypted with Laravel's `Crypt::encryptString()` (AES-256-CBC). Decrypted via `getDecryptedApiKeyAttribute()` accessor.

---

### 25. ai_provider_models

```sql
CREATE TABLE ai_provider_models (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    provider_id BIGINT UNSIGNED NOT NULL,
    model_id VARCHAR(200) NOT NULL,
    display_name VARCHAR(200) NOT NULL,
    capability ENUM('chat', 'reasoning', 'embedding', 'image', 'audio', 'vision') NOT NULL,
    cost_input_per_1m DECIMAL(10, 4) NULL,
    cost_output_per_1m DECIMAL(10, 4) NULL,
    max_tokens INT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (provider_id) REFERENCES ai_providers(id) ON DELETE CASCADE
);
```

**Indexes:** `provider_id`

**Casts:**
- `is_active` → boolean
- `cost_input_per_1m` → float
- `cost_output_per_1m` → float
- `max_tokens` → integer

**Capability enum:** chat, reasoning, embedding, image, audio, vision

---

### 26. ai_feature_configs

```sql
CREATE TABLE ai_feature_configs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    feature_key VARCHAR(100) NOT NULL UNIQUE,
    provider_id BIGINT UNSIGNED NULL,
    model_id BIGINT UNSIGNED NULL,
    options JSON NULL,
    is_enabled TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (provider_id) REFERENCES ai_providers(id) ON DELETE SET NULL,
    FOREIGN KEY (model_id) REFERENCES ai_provider_models(id) ON DELETE SET NULL
);
```

**Indexes:** `feature_key` UNIQUE

**Casts:**
- `options` → array
- `is_enabled` → boolean

**Pre-defined feature keys:**
| feature_key | Description |
|-------------|-------------|
| `ticket.classify` | Auto-classify ticket category/priority |
| `ticket.suggest` | AI response suggestion |
| `ticket.sentiment` | Sentiment analysis |
| `ticket.summarize` | Ticket summary generation |
| `kb.suggest` | Knowledge base article suggestion |
| `chat.bot_reply` | Chatbot auto-reply |

**options JSON structure:**
```json
{
  "temperature": 0.7,
  "max_tokens": 2000,
  "system_prompt": "You are a helpful support agent...",
  "max_history": 10
}
```

---

### 27. ai_usage_logs

```sql
CREATE TABLE ai_usage_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    provider_id BIGINT UNSIGNED NOT NULL,
    model_id BIGINT UNSIGNED NOT NULL,
    feature_key VARCHAR(100) NOT NULL,
    input_tokens INT NOT NULL DEFAULT 0,
    output_tokens INT NOT NULL DEFAULT 0,
    cost_estimated DECIMAL(10, 6) NOT NULL DEFAULT 0,
    latency_ms INT NULL,
    success TINYINT(1) NOT NULL DEFAULT 1,
    error_message TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (provider_id) REFERENCES ai_providers(id) ON DELETE CASCADE,
    FOREIGN KEY (model_id) REFERENCES ai_provider_models(id) ON DELETE CASCADE
);
```

**Indexes:** `feature_key`, `created_at`

**Casts:**
- `success` → boolean
- `cost_estimated` → float

**Cost formula:** `(input_tokens / 1,000,000 * cost_input_per_1m) + (output_tokens / 1,000,000 * cost_output_per_1m)`

---

### 28. canned_responses

```sql
CREATE TABLE canned_responses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    category_id BIGINT UNSIGNED NULL,
    body TEXT NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);
```

**Indexes:** `slug` UNIQUE

**Casts:** `is_active` → boolean

---

### 29. sla_policies

```sql
CREATE TABLE sla_policies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    department_id BIGINT UNSIGNED NULL,
    priority ENUM('low', 'medium', 'high', 'urgent') NOT NULL,
    first_response_time INT NOT NULL,
    resolution_time INT NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
);
```

**Casts:**
- `is_active` → boolean
- `first_response_time` → integer (minutes)
- `resolution_time` → integer (minutes)

**Example:**
| name | priority | first_response_time | resolution_time |
|------|----------|--------------------|-----------------|
| Urgent SLA | urgent | 15 min | 120 min |
| Standard SLA | medium | 60 min | 480 min |

---

### 30. automation_rules

```sql
CREATE TABLE automation_rules (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    trigger_event VARCHAR(100) NOT NULL,
    conditions JSON NOT NULL,
    actions JSON NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

**Casts:**
- `conditions` → array
- `actions` → array
- `is_active` → boolean
- `sort_order` → integer

**Trigger events:** `ticket.created`, `ticket.replied`, `ticket.escalated`, `sla.breached`

**conditions JSON structure:**
```json
[
  {"field": "priority", "operator": "equals", "value": "urgent"},
  {"field": "department_id", "operator": "equals", "value": 1}
]
```

**actions JSON structure:**
```json
[
  {"type": "assign_to", "value": 5},
  {"type": "set_priority", "value": "urgent"},
  {"type": "send_email", "value": "escalation_template"},
  {"type": "add_tag", "value": "needs_attention"}
]
```

---

### 31. email_templates

```sql
CREATE TABLE email_templates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    `key` VARCHAR(100) NOT NULL UNIQUE,
    subject VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

**Indexes:** `key` UNIQUE

**Pre-defined keys:**
| key | Purpose |
|-----|---------|
| `ticket.created` | New ticket notification |
| `ticket.replied` | New reply notification |
| `ticket.assigned` | Ticket assigned to agent |
| `ticket.resolved` | Ticket resolved notification |
| `welcome` | Welcome email after registration |

---

### 32. settings

```sql
CREATE TABLE settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(100) NOT NULL UNIQUE,
    value TEXT NULL,
    type VARCHAR(50) NOT NULL DEFAULT 'string',
    `group` VARCHAR(50) NOT NULL DEFAULT 'general',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

**Indexes:** `key` UNIQUE

**Note:** `$timestamps = false` on the model — timestamps are managed by the migration.

**Type values:** `string`, `boolean`, `integer`, `json`, `array`

**Groups:** `general`, `mail`, `payment`, `seo`, `whitelabel`

**Static helpers:**
```php
Setting::get('site_name', 'HelpDesk AI');  // retrieve
Setting::set('site_name', 'My Support');    // store (auto-detects type)
```

---

### 33. services

```sql
CREATE TABLE services (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    icon VARCHAR(50) NULL,
    description TEXT NULL,
    content LONGTEXT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

**Indexes:** `slug` UNIQUE

**Casts:**
- `is_active` → boolean
- `sort_order` → integer

**Used for:** Service pages (`/services/{slug}`) and pSEO listings.

---

### 34. posts

```sql
CREATE TABLE posts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    user_id BIGINT UNSIGNED NOT NULL,
    content LONGTEXT NOT NULL,
    excerpt TEXT NULL,
    featured_image VARCHAR(255) NULL,
    category VARCHAR(255) NULL,
    status ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
    published_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

**Indexes:** `slug` UNIQUE, `status`, `published_at`

**Casts:** `published_at` → datetime

---

### 35. notifications

```sql
CREATE TABLE notifications (
    id CHAR(36) PRIMARY KEY,
    type VARCHAR(255) NOT NULL,
    notifiable_type VARCHAR(255) NOT NULL,
    notifiable_id BIGINT UNSIGNED NOT NULL,
    data TEXT NOT NULL,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

**Indexes:** `(notifiable_type, notifiable_id)`

**Casts:**
- `data` → array
- `read_at` → datetime

**Note:** Uses UUID primary key (Laravel Notifiable default).

---

### 36. activity_logs

```sql
CREATE TABLE activity_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    action VARCHAR(100) NOT NULL,
    target_type VARCHAR(100) NOT NULL,
    target_id BIGINT NOT NULL,
    target_label VARCHAR(255) NULL,
    meta JSON NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

**Indexes:** `action`, `(target_type, target_id)`, `created_at`

**Casts:** `meta` → array

**Polymorphic target:** `target_type` + `target_id` identifies the entity acted upon (e.g., `App\Models\Ticket`, ID 42).

**Example actions:** `created`, `updated`, `deleted`, `assigned`, `closed`, `replied`

---

### 37. seo_meta

```sql
CREATE TABLE seo_meta (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    target_type VARCHAR(100) NOT NULL,
    target_id BIGINT NOT NULL,
    meta_title VARCHAR(255) NULL,
    meta_description TEXT NULL,
    meta_keywords VARCHAR(255) NULL,
    og_title VARCHAR(255) NULL,
    og_description TEXT NULL,
    og_image VARCHAR(255) NULL,
    twitter_title VARCHAR(255) NULL,
    twitter_description TEXT NULL,
    twitter_image VARCHAR(255) NULL,
    canonical_url VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

**Indexes:** `(target_type, target_id)`

**Polymorphic relationship:** Any model can have SEO meta via `MorphTo('target')`.

**Usage:**
```php
$seo = SeoMeta::firstOrNew(['target_type' => Service::class, 'target_id' => $service->id]);
$seo->fill(['meta_title' => '...', 'meta_description' => '...'])->save();
```

---

### 38. api_keys

```sql
CREATE TABLE api_keys (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    `key` VARCHAR(64) NOT NULL UNIQUE,
    last_used_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

**Indexes:** `key` UNIQUE, `user_id`

**Casts:**
- `is_active` → boolean
- `last_used_at` → datetime
- `expires_at` → datetime

**Key generation:** `hash('sha256', Str::random(64))` — 64-char hex string.

---

## Enum Values Summary

| Table | Column | Values |
|-------|--------|--------|
| users | role | `admin`, `agent`, `customer` |
| tickets | status | `open`, `in_progress`, `answered`, `resolved`, `closed` |
| tickets | priority | `low`, `medium`, `high`, `urgent` |
| tickets | source | `web`, `email`, `chat`, `api` |
| ticket_replies | source | `web`, `email`, `chat`, `api` |
| conversations | status | `open`, `closed` |
| conversation_messages | type | `text`, `file`, `system` |
| knowledge_articles | status | `draft`, `published`, `archived` |
| posts | status | `draft`, `published` |
| ai_providers | api_format | `openai_compatible`, `anthropic`, `gemini` |
| ai_provider_models | capability | `chat`, `reasoning`, `embedding`, `image`, `audio`, `vision` |
| sla_policies | priority | `low`, `medium`, `high`, `urgent` |
| settings | type | `string`, `boolean`, `integer`, `json`, `array` |
| settings | group | `general`, `mail`, `payment`, `seo`, `whitelabel` |

---

## JSON Column Structures

### tickets.custom_fields
```json
{
  "browser": "Chrome 125",
  "os": "Windows 11",
  "plan": "Enterprise",
  "company_size": "50-200"
}
```

### ai_feature_configs.options
```json
{
  "temperature": 0.7,
  "max_tokens": 2000,
  "system_prompt": "You are a helpful support agent...",
  "max_history": 10
}
```

### ai_providers.extra_headers
```json
{
  "X-Custom-Header": "value",
  "X-API-Version": "v2"
}
```

### automation_rules.conditions
```json
[
  {"field": "priority", "operator": "equals", "value": "urgent"},
  {"field": "department_id", "operator": "in", "value": [1, 2]}
]
```

### automation_rules.actions
```json
[
  {"type": "assign_to", "value": 5},
  {"type": "set_priority", "value": "urgent"},
  {"type": "send_email", "value": "escalation_template"}
]
```

### conversation_messages.metadata
```json
{
  "filename": "screenshot.png",
  "file_size": 204800,
  "mime_type": "image/png"
}
```

### activity_logs.meta
```json
{
  "old_status": "open",
  "new_status": "in_progress",
  "changed_by": "admin@helpdeskai.test"
}
```

---

## Foreign Key Constraints

| Child Table | Column | Parent Table | On Delete |
|-------------|--------|-------------|-----------|
| sessions | user_id | users | SET NULL |
| categories | department_id | departments | CASCADE |
| tickets | user_id | users | CASCADE |
| tickets | assigned_to | users | SET NULL |
| tickets | department_id | departments | CASCADE |
| tickets | category_id | categories | CASCADE |
| ticket_replies | ticket_id | tickets | CASCADE |
| ticket_replies | user_id | users | CASCADE |
| ticket_attachments | ticket_id | tickets | CASCADE |
| ticket_attachments | reply_id | ticket_replies | SET NULL |
| ticket_attachments | user_id | users | CASCADE |
| conversations | user_id | users | CASCADE |
| conversations | assigned_to | users | SET NULL |
| conversation_messages | conversation_id | conversations | CASCADE |
| conversation_messages | user_id | users | CASCADE |
| knowledge_categories | parent_id | knowledge_categories | SET NULL |
| knowledge_articles | category_id | knowledge_categories | SET NULL |
| knowledge_articles | user_id | users | CASCADE |
| knowledge_faqs | category_id | knowledge_categories | SET NULL |
| ai_provider_models | provider_id | ai_providers | CASCADE |
| ai_feature_configs | provider_id | ai_providers | SET NULL |
| ai_feature_configs | model_id | ai_provider_models | SET NULL |
| ai_usage_logs | provider_id | ai_providers | CASCADE |
| ai_usage_logs | model_id | ai_provider_models | CASCADE |
| canned_responses | category_id | categories | SET NULL |
| sla_policies | department_id | departments | SET NULL |
| posts | user_id | users | CASCADE |
| activity_logs | user_id | users | CASCADE |
| api_keys | user_id | users | CASCADE |
