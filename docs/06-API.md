# 06 — REST API Documentation

## Overview

HelpDesk AI provides a comprehensive REST API (137+ endpoints) for integration with external systems. All endpoints return JSON and use **Bearer token authentication** via Laravel Sanctum.

---

## Authentication

### Obtain an API Key

API keys are generated through the admin panel (`/admin/api-keys`) or via the user dashboard. Each key is a 64-character hex string tied to a user account.

**Header format:**
```http
Authorization: Bearer a1b2c3d4e5f6...
```

The system also accepts:
- `X-Api-Key: a1b2c3d4e5f6...` header
- `?api_key=a1b2c3d4e5f6...` query parameter

### Key Properties

| Property | Description |
|----------|-------------|
| `name` | Human-readable label |
| `key` | 64-char SHA-256 hash |
| `user_id` | Owner |
| `is_active` | Can be disabled |
| `expires_at` | Optional expiry date |
| `last_used_at` | Auto-updated on use |

---

## Rate Limiting

| Limit | Window |
|-------|--------|
| 60 requests | Per minute |

**Response headers:**
```http
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 58
```

When exceeded:
```json
{
  "message": "Too Many Requests",
  "retry_after": 42
}
```
HTTP Status: `429`

---

## Response Format

### Success
```json
{
  "data": {
    "id": 1,
    "uid": "TKT-A3B9X",
    "subject": "Login issue",
    "status": "open"
  },
  "message": "Ticket retrieved successfully"
}
```

### Collection (Paginated)
```json
{
  "data": [
    { "id": 1, "subject": "..." },
    { "id": 2, "subject": "..." }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 72
  },
  "links": {
    "first": "https://example.com/api/tickets?page=1",
    "last": "https://example.com/api/tickets?page=5",
    "prev": null,
    "next": "https://example.com/api/tickets?page=2"
  }
}
```

### Error
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "subject": ["The subject field is required."],
    "body": ["The body field is required."]
  }
}
```
HTTP Status: `422`

### Not Found
```json
{
  "message": "Resource not found."
}
```
HTTP Status: `404`

### Unauthorized
```json
{
  "message": "Unauthenticated."
}
```
HTTP Status: `401`

---

## Ticket Endpoints

### List Tickets
```http
GET /api/tickets?page=1&status=open&priority=high&sort=-created_at
```

**Query Parameters:**
| Param | Type | Description |
|-------|------|-------------|
| `page` | int | Page number |
| `per_page` | int | Items per page (max 100) |
| `status` | string | Filter: open, in_progress, waiting, resolved, closed |
| `priority` | string | Filter: low, medium, high, urgent |
| `assigned_to` | int | Filter by agent ID |
| `department_id` | int | Filter by department |
| `search` | string | Search subject and body |
| `sort` | string | Sort: `created_at`, `-created_at`, `priority` |

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "uid": "TKT-A3B9X",
      "user_id": 5,
      "assigned_to": 2,
      "department_id": 1,
      "category_id": 3,
      "subject": "Cannot login to dashboard",
      "body": "I'm getting a 500 error when trying to access...",
      "priority": "high",
      "status": "open",
      "source": "web",
      "sla_due_at": "2026-05-04T15:00:00Z",
      "closed_at": null,
      "is_starred": false,
      "custom_fields": {"browser": "Chrome 125"},
      "created_at": "2026-05-04T10:30:00Z",
      "updated_at": "2026-05-04T10:30:00Z",
      "user": { "id": 5, "name": "John Doe", "email": "john@example.com" },
      "assigned_to": { "id": 2, "name": "Sarah Agent" },
      "department": { "id": 1, "name": "Technical Support" },
      "category": { "id": 3, "name": "Login Issues" }
    }
  ],
  "meta": { "current_page": 1, "last_page": 1, "per_page": 15, "total": 1 }
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
  "priority": "high",
  "custom_fields": {
    "browser": "Chrome 125"
  }
}
```

**Required fields:** `subject`, `body`, `department_id`, `category_id`

**Optional fields:** `priority` (default: medium), `custom_fields` (JSON)

**Response:** `201 Created` — returns created ticket object.

### Get Ticket
```http
GET /api/tickets/{ticket}
```
Returns full ticket with replies, attachments, and relations.

### Update Ticket
```http
PUT /api/tickets/{ticket}
Content-Type: application/json

{
  "subject": "Updated: Cannot login",
  "priority": "urgent",
  "assigned_to": 2
}
```

**Updatable fields:** `subject`, `body`, `priority`, `assigned_to`, `category_id`, `status`, `custom_fields`

### Delete Ticket
```http
DELETE /api/tickets/{ticket}
```
Returns `204 No Content`. Only ticket creator or admin can delete.

### Add Reply
```http
POST /api/tickets/{ticket}/replies
Content-Type: application/json

{
  "body": "Thank you for reporting this. Can you try clearing your cache?",
  "is_internal": false
}
```

### Update Status
```http
PATCH /api/tickets/{ticket}/status
Content-Type: application/json

{
  "status": "in_progress"
}
```

### Toggle Star
```http
POST /api/tickets/{ticket}/star
```
Returns `200` with updated `is_starred` value.

### Upload Attachment
```http
POST /api/tickets/{ticket}/attachments
Content-Type: multipart/form-data

file: screenshot.png
```

---

## Knowledge Base Endpoints

### List Articles
```http
GET /api/knowledge?category={slug}&status=published&search=keyword&page=1
```

### Get Article
```http
GET /api/knowledge/{slug}
```

**Response:**
```json
{
  "data": {
    "id": 1,
    "title": "How to reset your password",
    "slug": "how-to-reset-password",
    "content": "<p>Step 1: ...</p>",
    "excerpt": "Follow these steps to reset...",
    "category": { "id": 1, "name": "Account Help", "slug": "account-help" },
    "author": { "id": 2, "name": "Sarah Agent" },
    "status": "published",
    "is_featured": false,
    "view_count": 1523,
    "helpful_count": 87,
    "not_helpful_count": 3,
    "meta_title": "How to Reset Password - HelpDesk AI",
    "meta_description": "Step-by-step guide...",
    "created_at": "2026-01-15T08:00:00Z"
  }
}
```

### Vote on Article
```http
POST /api/knowledge/{slug}/vote
Content-Type: application/json

{
  "vote": "helpful"  // or "not_helpful"
}
```

### Search Knowledge Base
```http
GET /api/knowledge/search?q=password+reset&limit=10
```

### List Categories
```http
GET /api/knowledge/categories
```

### List FAQs
```http
GET /api/knowledge/faqs?category_id=1
```

---

## Chat Endpoints

### List Conversations
```http
GET /api/conversations
```

### Start Conversation
```http
POST /api/conversations
Content-Type: application/json

{
  "subject": "Need help with billing"
}
```

### Get Conversation
```http
GET /api/conversations/{id}
```
Returns conversation with all messages.

### Send Message
```http
POST /api/conversations/{id}/messages
Content-Type: application/json

{
  "body": "Hello, I need help with my invoice",
  "type": "text"  // text, file
}
```

### Upload File in Chat
```http
POST /api/conversations/{id}/messages
Content-Type: multipart/form-data

body: Here's the screenshot
type: file
file: invoice_error.png
```

---

## AI Endpoints

### Classify Ticket
```http
POST /api/ai/classify
Content-Type: application/json

{
  "subject": "Cannot login to dashboard",
  "body": "I'm getting a 500 error when trying to access my account."
}
```

**Response:**
```json
{
  "data": {
    "suggested_category": "Login Issues",
    "suggested_category_id": 3,
    "suggested_department": "Technical Support",
    "suggested_department_id": 1,
    "suggested_priority": "high",
    "confidence": 0.92
  }
}
```

### Get AI Suggestion
```http
POST /api/ai/suggest
Content-Type: application/json

{
  "ticket_id": 42
}
```

**Response:**
```json
{
  "data": {
    "suggested_reply": "Hello John,\n\nThank you for reaching out. Based on your description...",
    "referenced_articles": [
      { "id": 5, "title": "Troubleshooting Login Errors" }
    ],
    "confidence": 0.85
  }
}
```

### Analyze Sentiment
```http
POST /api/ai/sentiment
Content-Type: application/json

{
  "text": "I've been waiting for 3 days and nobody has helped me. This is ridiculous."
}
```

**Response:**
```json
{
  "data": {
    "sentiment": "angry",
    "score": 0.94,
    "labels": { "positive": 0.02, "neutral": 0.04, "negative": 0.18, "angry": 0.76 }
  }
}
```

### Summarize Ticket
```http
POST /api/ai/summarize
Content-Type: application/json

{
  "ticket_id": 42
}
```

**Response:**
```json
{
  "data": {
    "summary": "User John cannot log into the dashboard after recent password change. Getting 500 error with Chrome 125 on Windows...",
    "key_points": [
      "Login failure after password change",
      "500 server error",
      "Chrome 125, Windows 11"
    ]
  }
}
```

---

## Analytics Endpoints

### Dashboard Stats
```http
GET /api/admin/analytics/stats
```

**Response:**
```json
{
  "data": {
    "total_tickets": 1542,
    "open_tickets": 47,
    "resolved_today": 23,
    "avg_response_time_minutes": 12.5,
    "avg_resolution_time_minutes": 145.3,
    "sla_compliance_pct": 87.2,
    "customer_satisfaction_pct": 91.5
  }
}
```

### Chart Data
```http
GET /api/admin/analytics/chart-data?period=30d&group=daily
```

**Query Params:**
- `period`: 7d, 30d, 3m, 12m
- `group`: daily, weekly, monthly

**Response:**
```json
{
  "data": {
    "labels": ["Apr 5", "Apr 6", "Apr 7", ...],
    "datasets": [
      { "label": "New Tickets", "data": [12, 15, 8, ...] },
      { "label": "Resolved", "data": [10, 14, 9, ...] }
    ]
  }
}
```

### Agent Performance
```http
GET /api/admin/analytics/agents?from=2026-04-01&to=2026-05-01
```

**Response:**
```json
{
  "data": [
    {
      "agent": { "id": 2, "name": "Sarah Agent" },
      "tickets_resolved": 87,
      "avg_response_time_minutes": 8.3,
      "avg_resolution_time_minutes": 102.5,
      "satisfaction_pct": 94.2
    }
  ]
}
```

---

## User Endpoints

### Get Profile
```http
GET /api/user
```

### Update Profile
```http
PUT /api/user
Content-Type: application/json

{
  "name": "John Updated",
  "phone": "+1234567890",
  "timezone": "America/New_York"
}
```

### List My API Keys
```http
GET /api/user/api-keys
```

### Generate API Key
```http
POST /api/user/api-keys
Content-Type: application/json

{
  "name": "Integration Key",
  "expires_at": "2027-01-01T00:00:00Z"
}
```

### Delete API Key
```http
DELETE /api/user/api-keys/{id}
```

---

## pSEO Endpoints

### Best HelpDesk Software
```http
GET /api/pseo/best/{category}?year=2026
```

**Response:**
```json
{
  "data": {
    "title": "Best Help Desk Software in 2026",
    "description": "Discover the best help desk software in 2026...",
    "products": [
      {
        "id": 1,
        "name": "HelpDesk AI Pro",
        "slug": "helpdesk-ai-pro",
        "description": "...",
        "features": ["AI-powered", "Live Chat", "SLA"],
        "rating": 4.8
      }
    ],
    "faqs": [...]
  }
}
```

### Compare Two Solutions
```http
GET /api/pseo/compare/{slugA}-vs-{slugB}
```

### Alternatives
```http
GET /api/pseo/alternatives/{slug}
```

---

## Admin Endpoints (admin scope required)

### Manage Departments
```http
GET    /api/admin/departments
POST   /api/admin/departments
PUT    /api/admin/departments/{id}
DELETE /api/admin/departments/{id}
```

### Manage Categories
```http
GET    /api/admin/categories
POST   /api/admin/categories
PUT    /api/admin/categories/{id}
DELETE /api/admin/categories/{id}
```

### Manage Users
```http
GET    /api/admin/users?search=&role=&page=
POST   /api/admin/users
GET    /api/admin/users/{id}
PUT    /api/admin/users/{id}
DELETE /api/admin/users/{id}
```

### AI Providers
```http
GET    /api/admin/ai-providers
POST   /api/admin/ai-providers
PUT    /api/admin/ai-providers/{id}
DELETE /api/admin/ai-providers/{id}
POST   /api/admin/ai-providers/{id}/test-connection
POST   /api/admin/ai-providers/{id}/fetch-models
```

### AI Feature Configs
```http
GET    /api/admin/ai-features
PUT    /api/admin/ai-features/{id}
```

### Activity Log
```http
GET /api/admin/activity-log?action=&user_id=&from=&to=&page=
```

### Settings
```http
GET  /api/admin/settings?group=
POST /api/admin/settings
```

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

// Create ticket
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

// List open tickets
$response = $client->get('tickets', [
    'query' => ['status' => 'open', 'sort' => '-created_at'],
]);

$tickets = json_decode($response->getBody(), true)['data'];
```

### JavaScript (fetch)
```javascript
const API_BASE = 'https://yourdomain.com/api';
const API_KEY = 'a1b2c3d4e5f6...';

// Create ticket
async function createTicket(data) {
  const response = await fetch(`${API_BASE}/tickets`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${API_KEY}`,
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    },
    body: JSON.stringify(data),
  });
  
  if (!response.ok) {
    const error = await response.json();
    throw new Error(error.message);
  }
  
  return response.json();
}

// Usage
createTicket({
  subject: 'Login issue',
  body: 'Cannot access dashboard',
  department_id: 1,
  category_id: 3,
  priority: 'high',
}).then(data => console.log(data.data));
```

### cURL
```bash
# Create ticket
curl -X POST "https://yourdomain.com/api/tickets" \
  -H "Authorization: Bearer a1b2c3d4e5f6..." \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "subject": "Login issue",
    "body": "Cannot access dashboard",
    "department_id": 1,
    "category_id": 3,
    "priority": "high"
  }'

# List tickets
curl -X GET "https://yourdomain.com/api/tickets?status=open&sort=-created_at" \
  -H "Authorization: Bearer a1b2c3d4e5f6..." \
  -H "Accept: application/json"

# Get ticket with replies
curl -X GET "https://yourdomain.com/api/tickets/42" \
  -H "Authorization: Bearer a1b2c3d4e5f6..." \
  -H "Accept: application/json"

# Add reply
curl -X POST "https://yourdomain.com/api/tickets/42/replies" \
  -H "Authorization: Bearer a1b2c3d4e5f6..." \
  -H "Content-Type: application/json" \
  -d '{"body": "Can you try clearing your cache?"}'

# AI sentiment analysis
curl -X POST "https://yourdomain.com/api/ai/sentiment" \
  -H "Authorization: Bearer a1b2c3d4e5f6..." \
  -H "Content-Type: application/json" \
  -d '{"text": "I have been waiting for 3 days!"}'

# Upload attachment
curl -X POST "https://yourdomain.com/api/tickets/42/attachments" \
  -H "Authorization: Bearer a1b2c3d4e5f6..." \
  -F "file=@screenshot.png"
```

---

## Full Endpoint Index

### Tickets
| Method | URL | Auth |
|--------|-----|------|
| GET | `/api/tickets` | User |
| POST | `/api/tickets` | User |
| GET | `/api/tickets/{ticket}` | User |
| PUT | `/api/tickets/{ticket}` | User/Admin |
| DELETE | `/api/tickets/{ticket}` | User/Admin |
| POST | `/api/tickets/{ticket}/replies` | User |
| PATCH | `/api/tickets/{ticket}/status` | User/Admin |
| POST | `/api/tickets/{ticket}/star` | User/Admin |
| POST | `/api/tickets/{ticket}/attachments` | User |
| POST | `/api/tickets/bulk` | Admin |

### Knowledge Base
| Method | URL | Auth |
|--------|-----|------|
| GET | `/api/knowledge` | Public |
| GET | `/api/knowledge/{slug}` | Public |
| POST | `/api/knowledge/{slug}/vote` | User |
| GET | `/api/knowledge/search` | Public |
| GET | `/api/knowledge/categories` | Public |
| GET | `/api/knowledge/faqs` | Public |

### Conversations
| Method | URL | Auth |
|--------|-----|------|
| GET | `/api/conversations` | User |
| POST | `/api/conversations` | User |
| GET | `/api/conversations/{id}` | User |
| POST | `/api/conversations/{id}/messages` | User |

### AI
| Method | URL | Auth |
|--------|-----|------|
| POST | `/api/ai/classify` | User/Admin |
| POST | `/api/ai/suggest` | User/Admin |
| POST | `/api/ai/sentiment` | User/Admin |
| POST | `/api/ai/summarize` | User/Admin |

### Analytics
| Method | URL | Auth |
|--------|-----|------|
| GET | `/api/admin/analytics/stats` | Admin |
| GET | `/api/admin/analytics/chart-data` | Admin |
| GET | `/api/admin/analytics/agents` | Admin |

### Users
| Method | URL | Auth |
|--------|-----|------|
| GET | `/api/user` | User |
| PUT | `/api/user` | User |
| GET | `/api/user/api-keys` | User |
| POST | `/api/user/api-keys` | User |
| DELETE | `/api/user/api-keys/{id}` | User |

### Admin
| Method | URL | Auth |
|--------|-----|------|
| GET | `/api/admin/users` | Admin |
| POST | `/api/admin/users` | Admin |
| GET | `/api/admin/users/{id}` | Admin |
| PUT | `/api/admin/users/{id}` | Admin |
| DELETE | `/api/admin/users/{id}` | Admin |
| GET | `/api/admin/departments` | Admin |
| POST | `/api/admin/departments` | Admin |
| PUT | `/api/admin/departments/{id}` | Admin |
| DELETE | `/api/admin/departments/{id}` | Admin |
| GET | `/api/admin/categories` | Admin |
| POST | `/api/admin/categories` | Admin |
| PUT | `/api/admin/categories/{id}` | Admin |
| DELETE | `/api/admin/categories/{id}` | Admin |
| GET | `/api/admin/ai-providers` | Admin |
| POST | `/api/admin/ai-providers` | Admin |
| PUT | `/api/admin/ai-providers/{id}` | Admin |
| DELETE | `/api/admin/ai-providers/{id}` | Admin |
| POST | `/api/admin/ai-providers/{id}/test-connection` | Admin |
| POST | `/api/admin/ai-providers/{id}/fetch-models` | Admin |
| GET | `/api/admin/ai-features` | Admin |
| PUT | `/api/admin/ai-features/{id}` | Admin |
| GET | `/api/admin/activity-log` | Admin |
| GET | `/api/admin/settings` | Admin |
| POST | `/api/admin/settings` | Admin |

### pSEO
| Method | URL | Auth |
|--------|-----|------|
| GET | `/api/pseo/best/{category}` | Public |
| GET | `/api/pseo/compare/{slugA}-vs-{slugB}` | Public |
| GET | `/api/pseo/alternatives/{slug}` | Public |
