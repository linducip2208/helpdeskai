# 07 — AI Provider System

> **Core principle: ZERO hardcoded providers, models, keys, URLs, or pricing.** All dynamic. User inputs everything through the admin UI. The codebase only has generic format-based adapters that read from the database.

---

## Philosophy

1. **No vendor names in code** — the codebase doesn't know what "OpenAI", "Claude", or "DeepSeek" is. It only knows how to handle 3 generic API formats.
2. **User-defined providers** — admin creates provider entries: any name, any URL, their own API key, their own models.
3. **Empty by default** — fresh install has 0 providers configured. User must add at least 1 before AI features work.
4. **Optional preset templates** — JSON files in `storage/app/ai-presets/*.json` that the admin UI reads for autofill convenience only. Never referenced at runtime.
5. **Per-feature dynamic picker** — dropdowns only show providers the user has added, not a hardcoded list.
6. **User-defined pricing** — user inputs cost per 1M tokens themselves for tracking. We don't maintain a pricing list that would become stale.

---

## What's in Code vs What's in Database

### In Code (generic, vendor-agnostic)

| Component | Purpose |
|-----------|---------|
| `OpenAICompatibleAdapter` | Handles any API following OpenAI format (`/v1/chat/completions`). Covers ~90% of modern providers: OpenAI, DeepSeek, Groq, Mistral, Together, Fireworks, OpenRouter, Ollama, LM Studio, vLLM, xAI Grok, Anyscale, Cerebras, Lepton, and more. |
| `AnthropicFormatAdapter` | Handles Claude API format (`/v1/messages`) |
| `GeminiFormatAdapter` | Handles Google Gemini format (`generateContent`) |
| `AiAdapterInterface` | Contract that all adapters implement |
| `AiAdapterFactory` | Dispatcher: selects adapter based on `api_format` field in DB |

**That's it.** 3-4 adapter classes total. No `OpenAIAdapter`, `DeepSeekAdapter`, `GroqAdapter` — they all use the same format and therefore the same adapter.

### In Database (user-managed)

| Table | Contents |
|-------|----------|
| `ai_providers` | Provider entries (name, URL, key, format, headers) |
| `ai_provider_models` | Models under each provider (model ID, display name, capability, costs) |
| `ai_feature_configs` | Per-feature provider+model mapping + options |
| `ai_usage_logs` | Every AI call: tokens, cost, latency, success/failure |

---

## Database Schema

### ai_providers
```
id              BIGINT PK
name            VARCHAR(100)      — e.g., "My DeepSeek", "Production OpenAI"
api_format      ENUM('openai_compatible', 'anthropic', 'gemini')
base_url        VARCHAR(500)      — e.g., "https://api.deepseek.com/v1"
api_key_encrypted TEXT            — AES-256 encrypted via Laravel Crypt
extra_headers   JSON NULL         — e.g., {"X-Custom-Header": "value"}
is_active       BOOLEAN           — toggle on/off
last_tested_at  TIMESTAMP NULL    — last connection test
last_test_status VARCHAR(20) NULL — "success" or "failure"
notes           TEXT NULL          — user's own notes
```

### ai_provider_models
```
id                  BIGINT PK
provider_id         FK → ai_providers
model_id            VARCHAR(200)     — e.g., "deepseek-chat", "claude-3-5-sonnet"
display_name        VARCHAR(200)     — e.g., "DeepSeek V3", "Claude 3.5 Sonnet"
capability          ENUM('chat', 'reasoning', 'embedding', 'image', 'audio', 'vision')
cost_input_per_1m   DECIMAL(10,4)   — user-entered price per 1M input tokens
cost_output_per_1m  DECIMAL(10,4)   — user-entered price per 1M output tokens
max_tokens          INT NULL         — model context window limit
is_active           BOOLEAN
```

### ai_feature_configs
```
id              BIGINT PK
feature_key     VARCHAR(100) UNIQUE — "ticket.classify", "ticket.suggest", etc.
provider_id     FK → ai_providers NULL  — NULL = feature disabled
model_id        FK → ai_provider_models NULL
options         JSON NULL           — temperature, max_tokens, system_prompt
is_enabled      BOOLEAN
```

### ai_usage_logs
```
id              BIGINT PK
provider_id     FK → ai_providers
model_id        FK → ai_provider_models
feature_key     VARCHAR(100)        — which feature was used
input_tokens    INT                 — tokens consumed (prompt)
output_tokens   INT                 — tokens generated (completion)
cost_estimated  DECIMAL(10,6)       — estimated cost in user's currency
latency_ms      INT NULL            — response time
success         BOOLEAN             — API call succeeded?
error_message   TEXT NULL           — error details if failed
```

---

## How to Add an AI Provider (Admin UI Flow)

### Step 1: Navigate to Admin → AI Providers
```
/admin/ai-providers
```

### Step 2: Click "Add Provider"

Fill in the form:

| Field | Example | Notes |
|-------|---------|-------|
| **Name** | "My DeepSeek" | Any label you want |
| **API Format** | `openai_compatible` | Pick from dropdown |
| **Base URL** | `https://api.deepseek.com/v1` | Provider's API endpoint |
| **API Key** | `sk-xxxx...` | Your key (encrypted at save) |
| **Extra Headers** | `{}` | Optional custom headers |
| **Notes** | "Production account" | Your own notes |
| **Active** | ✅ | |

### Step 3 (Optional): Import from Preset

Click "Import from Template" to autofill from one of the 20+ preset JSON files:
- OpenAI, DeepSeek, Groq, Mistral, Anthropic, Google Gemini
- Together AI, Fireworks, OpenRouter, Cohere, xAI Grok
- Anyscale, Cerebras, Lepton, OctoAI, Perplexity
- Ollama (local), LM Studio (local), vLLM (local)
- Azure OpenAI, AWS Bedrock

Presets only fill the form fields — you must still provide your own API key.

### Step 4: Test Connection

Click "Test Connection" → `POST /admin/ai-providers/{id}/test-connection`

This calls the provider's models endpoint with your key and displays the result (`last_test_status` updated).

### Step 5: Fetch Models

Click "Fetch Models" → `POST /admin/ai-providers/{id}/fetch-models`

If the provider supports auto-discovery (e.g., OpenAI `/v1/models`), this reads available models and adds them to `ai_provider_models`. You can also add models manually.

### Step 6: Add Models Manually (if needed)

For each model under the provider:

| Field | Example |
|-------|---------|
| **Model ID** | `deepseek-chat` |
| **Display Name** | `DeepSeek V3` |
| **Capability** | `chat` |
| **Input Cost / 1M tokens** | `0.27` |
| **Output Cost / 1M tokens** | `1.10` |
| **Max Tokens** | `65536` |

### Step 7: Assign to Features

Navigate to Admin → AI Features (`/admin/ai-features`):

| Feature | Pick Provider | Pick Model | Options |
|---------|--------------|------------|---------|
| `ticket.classify` | My DeepSeek | deepseek-chat | `{"temperature": 0.3}` |
| `ticket.suggest` | My DeepSeek | deepseek-chat | `{"temperature": 0.7}` |
| `ticket.sentiment` | My OpenAI | gpt-4o-mini | `{"temperature": 0.1}` |

Set `is_enabled = true` to activate.

---

## 20 Preset Templates

Located at `storage/app/ai-presets/*.json`:

```
ai-presets/
├── openai.json
├── deepseek.json
├── anthropic.json
├── google-gemini.json
├── groq.json
├── mistral.json
├── together.json
├── fireworks.json
├── openrouter.json
├── cohere.json
├── xai-grok.json
├── anyscale.json
├── cerebras.json
├── lepton.json
├── octoai.json
├── perplexity.json
├── ollama.json           # Local: http://localhost:11434/v1
├── lm-studio.json        # Local: http://localhost:1234/v1
├── vllm.json             # Local: http://localhost:8000/v1
└── azure-openai.json
```

**Template format example** (`deepseek.json`):
```json
{
  "name_suggestion": "DeepSeek",
  "api_format": "openai_compatible",
  "base_url": "https://api.deepseek.com/v1",
  "models": [
    {
      "model_id": "deepseek-chat",
      "display_name": "DeepSeek V3",
      "capability": "chat",
      "cost_input_per_1m": 0.27,
      "cost_output_per_1m": 1.10,
      "max_tokens": 65536
    },
    {
      "model_id": "deepseek-reasoner",
      "display_name": "DeepSeek R1",
      "capability": "reasoning",
      "cost_input_per_1m": 0.55,
      "cost_output_per_1m": 2.19,
      "max_tokens": 65536
    }
  ]
}
```

---

## OpenAI-Compatible Adapter (Covers 15+ Providers)

The `openai_compatible` format is the most versatile — any provider that implements the OpenAI chat completions API works automatically:

```php
// The adapter doesn't care which provider — it reads base_url from DB
POST {base_url}/chat/completions
Authorization: Bearer {api_key}
Content-Type: application/json

{
  "model": "{model_id from DB}",
  "messages": [
    {"role": "system", "content": "You are a helpful support agent."},
    {"role": "user", "content": "Classify this ticket..."}
  ],
  "temperature": 0.3,
  "max_tokens": 2000
}
```

### Providers that work with this adapter:
- **Cloud:** OpenAI, DeepSeek, Groq, Mistral, Together AI, Fireworks, OpenRouter, xAI Grok, Anyscale, Cerebras, Lepton, OctoAI, Perplexity, Cohere (with `/v1` compat mode)
- **Local:** Ollama, LM Studio, vLLM, LocalAI, Text Generation Web UI, llama.cpp server
- **Enterprise:** Azure OpenAI, AWS Bedrock (with proxy)

---

## Anthropic Adapter

For Claude API format (`/v1/messages`):

```php
POST {base_url}/v1/messages
x-api-key: {api_key}
anthropic-version: 2023-06-01
Content-Type: application/json

{
  "model": "{model_id}",
  "max_tokens": 2000,
  "system": "You are a helpful support agent.",
  "messages": [
    {"role": "user", "content": "Classify this ticket..."}
  ]
}
```

---

## Gemini Adapter

For Google Gemini API format (`generateContent`):

```php
POST {base_url}/v1beta/models/{model_id}:generateContent?key={api_key}
Content-Type: application/json

{
  "systemInstruction": {
    "parts": [{"text": "You are a helpful support agent."}]
  },
  "contents": [
    {
      "role": "user",
      "parts": [{"text": "Classify this ticket..."}]
    }
  ],
  "generationConfig": {
    "temperature": 0.3,
    "maxOutputTokens": 2000
  }
}
```

---

## Auto-Fetch Models Feature

When the admin clicks "Fetch Models" on a provider:

1. System calls the provider's model listing endpoint
2. For `openai_compatible`: `GET {base_url}/models`
3. For `anthropic`: `GET {base_url}/v1/models`
4. For `gemini`: `GET {base_url}/v1beta/models`
5. Parses the response and imports model IDs + capabilities
6. Admin can then edit display names and costs

This is a **convenience feature** — admin can always add models manually.

---

## Per-Feature Provider Picker

The admin UI at `/admin/ai-features` shows each feature with dropdowns:

```
┌─────────────────────────────────────────────────────────┐
│ Feature: Ticket Classification                          │
│ Enabled: [✓]                                            │
│ Provider: [My DeepSeek ▾]                               │
│ Model:    [deepseek-chat ▾]                             │
│ Options:                                                │
│   Temperature: [0.3      ]                              │
│   Max Tokens:  [2000     ]                              │
│   System Prompt:                                        │
│   [You are a ticket classification AI...]               │
│                                                         │
│ [Save]                                                  │
└─────────────────────────────────────────────────────────┘
```

Dropdowns only show providers/models the user has added. Empty state shows "No providers configured. Add one first."

---

## Usage Logging & Cost Tracking

Every AI call automatically writes to `ai_usage_logs`:

```php
AiUsageLog::create([
    'provider_id'    => $provider->id,
    'model_id'       => $model->id,
    'feature_key'    => 'ticket.classify',
    'input_tokens'   => 245,
    'output_tokens'  => 78,
    'cost_estimated' => (245/1000000 * 0.27) + (78/1000000 * 1.10), // = 0.00015
    'latency_ms'     => 320,
    'success'        => true,
]);
```

### Cost Calculation
```
cost = (input_tokens / 1,000,000 × cost_input_per_1m) + (output_tokens / 1,000,000 × cost_output_per_1m)
```

### Analytics on Usage
The admin analytics page shows:
- Total AI calls per day/week/month
- Total tokens consumed
- Total estimated cost
- Breakdown by feature
- Breakdown by provider
- Average latency per provider

---

## Budget Settings

Planned: per-feature monthly budget caps stored in settings. When a feature exceeds its budget, it automatically disables with a notification to admin.

```env
# Planned (not yet implemented)
AI_BUDGET_CLASSIFY=5.00     # $5/month for classification
AI_BUDGET_SUGGEST=20.00     # $20/month for suggestions
AI_BUDGET_SENTIMENT=2.00    # $2/month for sentiment
```

---

## Security

### API Key Encryption
All API keys are encrypted at rest using Laravel's `Crypt` facade (AES-256-CBC):

```php
// Storing
$provider->api_key_encrypted = Crypt::encryptString($apiKey);

// Retrieving
$key = Crypt::decryptString($provider->api_key_encrypted);
```

The decrypted key is accessed via `$provider->decrypted_api_key` accessor and is **never**:
- Logged (masked in logs: `sk-...xxxx`)
- Returned in API responses
- Exposed in error messages
- Stored in session or cache

### Key Rotation
Admin can update the API key at any time. Old key is overwritten in the encrypted column.

---

## Self-Host Options (Ollama, LM Studio)

### Ollama
```
Name: Ollama Local
API Format: openai_compatible
Base URL: http://localhost:11434/v1
API Key: ollama  (or any string — Ollama ignores it)
```

### LM Studio
```
Name: LM Studio Local
API Format: openai_compatible
Base URL: http://localhost:1234/v1
API Key: lm-studio
```

Both work out-of-the-box because they implement the OpenAI-compatible API format. **No special adapter needed.**

---

## Error Handling

When an AI call fails:

1. The error is caught by the adapter
2. `AiUsageLog` is created with `success = false` and `error_message` set
3. The calling service receives a fallback/default response
4. Admin can view failure logs in the usage log table

### Common Errors

| Error | Likely Cause | Fix |
|-------|-------------|-----|
| 401 Unauthorized | Invalid API key | Update key in provider settings |
| 404 Not Found | Wrong base URL or model ID | Verify URL and model ID |
| 429 Rate Limited | Exceeded provider rate limit | Wait or upgrade plan |
| 500 Provider Error | Provider service issue | Retry later |
| Connection Timeout | Network or firewall | Check connectivity from server |

---

## Testing

### Test Connection
```bash
# Admin clicks "Test Connection" which triggers:
POST /api/admin/ai-providers/{id}/test-connection

# System makes a lightweight API call:
GET {base_url}/models
# or
POST {base_url}/chat/completions with minimal prompt

# Result: last_tested_at and last_test_status updated
```

### Manual Test via Artisan
```bash
php artisan ai:test-connection {provider_id}
# Returns: "Provider My DeepSeek: Connection successful (150ms)"
```

---

## Reference Implementation

This system follows the specification originally designed for the FoodScan project:

**Reference:** `D:\project laravel\foodscan\docs\10-AI-PROVIDERS.md`

The same pattern applies across all projects per `CLAUDE.md` global preferences:
- No hardcoded providers
- Format-based generic adapters
- Database-driven configuration
- User-managed keys and pricing
- Optional preset templates for convenience only
