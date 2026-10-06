# 11 — Development Guide

## Local Setup

### Prerequisites
- PHP 8.3+
- Composer 2.x
- Node.js 20+
- Git
- SQLite (for dev) or MySQL 8.0+

### Quick Start
```bash
# Clone
git clone <repo-url> helpdeskai
cd helpdeskai

# Install
composer install
npm install

# Environment
cp .env.example .env
php artisan key:generate

# Database (SQLite is default for dev)
# If using MySQL, update .env with your credentials first
php artisan migrate --seed

# Start everything
composer run dev
```

### What `composer run dev` Starts
| Process | Command | Purpose |
|---------|---------|---------|
| server | `php artisan serve` | HTTP server on port 8000 |
| queue | `php artisan queue:listen` | Queue worker |
| logs | `php artisan pail` | Log tail |
| vite | `npm run dev` | Vite dev server (HMR) |

### Access URLs
| URL | Service |
|-----|---------|
| `http://localhost:8000` | Main application |
| `http://localhost:8000/admin` | Admin panel |
| `http://localhost:5173` | Vite HMR (for Vue dev) |
| `ws://localhost:8080` | Reverb WebSocket |

---

## Branch Strategy

### Git Flow (Simplified)
```
main        — Production-ready code
├── develop — Integration branch
│   ├── feature/xxx  — New features
│   ├── fix/xxx      — Bug fixes
│   └── refactor/xxx — Code improvements
└── hotfix/xxx       — Critical production fixes
```

### Branch Naming
- Features: `feature/ticket-kanban`, `feature/ai-classify`
- Fixes: `fix/ticket-uid-generation`, `fix/attachment-upload`
- Refactors: `refactor/ai-adapter-interface`

### Commit Messages
```
feat: Add ticket kanban board view
fix: Prevent duplicate ticket UIDs
refactor: Extract AI adapter to interface
docs: Update API documentation
test: Add ticket lifecycle tests
chore: Update composer dependencies
```

---

## Code Conventions

### PHP (PSR-12)

**Naming:**
- Classes: `PascalCase` — `TicketController`, `AiServiceProvider`
- Methods: `camelCase` — `getStatusColorAttribute()`, `generateUid()`
- Variables: `camelCase` — `$ticketCount`, `$slaDueAt`
- Constants: `UPPER_SNAKE_CASE` — `MAX_ATTACHMENT_SIZE`
- Database tables: `snake_case` plural — `tickets`, `ai_providers`

**File Organization:**
- One class per file
- Namespace matches directory structure
- `declare(strict_types=1);` at top
- Use `#[Attributes]` for PHP 8 native attributes where applicable

**Eloquent:**
- Use `$fillable` or `$guarded` (never both) for mass assignment protection
- Use attribute casting via `casts()` method
- Relations should return type-hinted relation objects

### Vue 3

**Component Naming:**
- Files: `PascalCase.vue` — `TicketList.vue`, `ChatWidget.vue`
- Template tags: `PascalCase` or `kebab-case`

**Composition API Preference:**
```vue
<script setup>
import { ref, computed } from 'vue'

const count = ref(0)
const doubled = computed(() => count.value * 2)
</script>
```

**Props:**
```vue
<script setup>
defineProps({
  ticket: { type: Object, required: true },
  showActions: { type: Boolean, default: true },
})
</script>
```

### TailwindCSS
- Use utility classes directly in templates
- Extract repeated patterns to `@apply` in CSS only when truly repetitive
- Follow mobile-first approach (`sm:`, `md:`, `lg:`)

---

## Project Structure Conventions

### Where to Put Things

| What | Where |
|------|-------|
| New Model | `app/Models/ModelName.php` |
| New Controller (web) | `app/Http/Controllers/FeatureController.php` |
| New Controller (admin) | `app/Http/Controllers/Admin/FeatureController.php` |
| New Migration | `php artisan make:migration create_table_name` |
| New Seeder | `database/seeders/FeatureSeeder.php` |
| New Service | `app/Services/ServiceName.php` |
| New Enum | `app/Enums/EnumName.php` |
| New Vue Page | `resources/js/Pages/Feature/Page.vue` |
| New Vue Component | `resources/js/Components/Component.vue` |
| New Config | `config/feature.php` |
| New Route (web) | `routes/web.php` |
| New Route (api) | `routes/api.php` |
| New Test | `tests/Feature/FeatureTest.php` |

---

## Testing Strategy

### Test Types

| Type | Directory | Purpose |
|------|-----------|---------|
| Unit | `tests/Unit/` | Model logic, service methods, helpers |
| Feature | `tests/Feature/` | HTTP requests, controller actions, middleware |
| Browser | Not yet configured | E2E with Laravel Dusk (planned) |

### Running Tests
```bash
# All tests
composer run test  # or: php artisan test

# Single test file
php artisan test --filter=TicketTest

# Single test method
php artisan test --filter=TicketTest::test_create_ticket

# With coverage (requires Xdebug)
php artisan test --coverage
```

### Test Database
Tests use SQLite in-memory database by default:
```env
# .env.testing
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
```

### Writing Tests

```php
<?php

use App\Models\User;
use App\Models\Ticket;
use App\Models\Department;
use App\Models\Category;

test('customer can create a ticket', function () {
    $user = User::factory()->create();
    $department = Department::factory()->create();
    $category = Category::factory()->create();

    $response = $this->actingAs($user)->post('/tickets', [
        'subject' => 'Test issue',
        'body' => 'This is a test ticket',
        'department_id' => $department->id,
        'category_id' => $category->id,
        'priority' => 'medium',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('tickets', [
        'subject' => 'Test issue',
        'user_id' => $user->id,
    ]);
});

test('guest cannot create a ticket', function () {
    $response = $this->post('/tickets', [
        'subject' => 'Test',
        'body' => 'Test body',
    ]);

    $response->assertRedirect('/login');
});
```

### Factories
Create factories for all models to enable testing:
```php
// database/factories/TicketFactory.php
public function definition(): array
{
    return [
        'uid' => Ticket::generateUid(),
        'user_id' => User::factory(),
        'department_id' => Department::factory(),
        'category_id' => Category::factory(),
        'subject' => fake()->sentence(),
        'body' => fake()->paragraph(),
        'priority' => fake()->randomElement(['low', 'medium', 'high', 'urgent']),
        'status' => 'open',
    ];
}
```

---

## Adding New Features

### Step-by-Step Guide

#### 1. Plan
- What problem does it solve?
- Which tables/models are affected?
- What routes are needed?
- What permissions are required?

#### 2. Migration
```bash
php artisan make:migration create_feature_table
```

Write the migration with proper columns, indexes, and foreign keys.

#### 3. Model
```php
// app/Models/Feature.php
class Feature extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'is_active'];
    
    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
```

#### 4. Controller
```bash
php artisan make:controller FeatureController
# or for admin:
php artisan make:controller Admin/FeatureController
```

#### 5. Routes
Add routes in `routes/web.php` (web) or `routes/api.php` (API):
```php
// Web
Route::resource('features', FeatureController::class);

// Admin
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::resource('features', Admin\FeatureController::class);
});
```

#### 6. Vue Pages
Create page components:
```
resources/js/Pages/Feature/
├── Index.vue
├── Show.vue
├── Create.vue
└── Edit.vue
```

#### 7. Permissions
Add permissions in `RolesAndPermissionsSeeder.php`:
```php
Permission::create(['name' => 'view features']);
Permission::create(['name' => 'create features']);
Permission::create(['name' => 'edit features']);
Permission::create(['name' => 'delete features']);
```

#### 8. Tests
Write feature and unit tests covering:
- Happy path
- Validation errors
- Authorization (403 for unauthorized)
- Edge cases (empty state, max values)

#### 9. Documentation
Update relevant docs in `docs/` if the feature is significant.

---

## Adding a New AI Provider Adapter

### When Needed
Only add a new adapter if a provider uses an API format that doesn't match any existing format:
- `openai_compatible` — covers 90% of providers
- `anthropic` — Claude-specific format
- `gemini` — Google Gemini-specific format

If a new provider uses a completely different format:

### Steps
1. Create adapter implementing `AiAdapterInterface`:
```php
// app/Adapters/NewFormatAdapter.php
class NewFormatAdapter implements AiAdapterInterface
{
    public function chat(AiProvider $provider, AiProviderModel $model, array $messages, array $options = []): array
    {
        $client = new Client();
        $response = $client->post($provider->base_url . '/custom/endpoint', [
            'headers' => [
                'Authorization' => 'Bearer ' . $provider->decrypted_api_key,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => $model->model_id,
                'messages' => $messages,
                ...$options,
            ],
        ]);
        
        return json_decode($response->getBody(), true);
    }
}
```

2. Add a new `api_format` enum value:
```php
// Migration: add to ai_providers.api_format ENUM
ALTER TABLE ai_providers MODIFY api_format 
  ENUM('openai_compatible', 'anthropic', 'gemini', 'new_format');
```

3. Register the adapter in `AiAdapterFactory`:
```php
public static function make(string $format): AiAdapterInterface
{
    return match ($format) {
        'openai_compatible' => new OpenAICompatibleAdapter(),
        'anthropic' => new AnthropicFormatAdapter(),
        'gemini' => new GeminiFormatAdapter(),
        'new_format' => new NewFormatAdapter(),
        default => throw new \InvalidArgumentException("Unknown format: {$format}"),
    };
}
```

4. Optionally create a preset template:
```json
// storage/app/ai-presets/new-provider.json
{
  "name_suggestion": "New Provider",
  "api_format": "new_format",
  "base_url": "https://api.newprovider.com",
  "models": [...]
}
```

---

## Adding a New pSEO Pattern

### Example: Add `/best-{category}-{year}` pattern

#### 1. Add Route
```php
// routes/web.php
Route::get('/best-{category}-{year}', [ProgrammaticSeoController::class, 'bestCategoryYear']);
```

#### 2. Add Controller Method
```php
// ProgrammaticSeoController.php
public function bestCategoryYear(string $category, int $year)
{
    // Resolve category
    $categoryModel = Category::where('slug', $category)->firstOrFail();
    
    // Resolve year
    $year = max(2020, min((int) date('Y'), $year));
    
    // Get data
    $services = Service::where('is_active', true)
        ->where('category_id', $categoryModel->id)
        ->orderByDesc('sort_order')
        ->limit(10)
        ->get();
    
    if ($services->isEmpty()) {
        abort(404);
    }
    
    return Inertia::render('Seo/BestCategory', [
        'category' => $categoryModel,
        'year' => $year,
        'services' => $services,
        'seoTitle' => "Best {$categoryModel->name} in {$year}",
        'seoDescription' => "Discover the best {$categoryModel->name} for {$year}...",
        'faqs' => $this->buildFaqs($categoryModel, $year),
    ]);
}
```

#### 3. Create Vue Page
```vue
<!-- resources/js/Pages/Seo/BestCategory.vue -->
<script setup>
defineProps({ category: Object, year: Number, services: Array, faqs: Array })
</script>
```

#### 4. Add to Sitemap Generator
Update the sitemap generation to include new pattern URLs.

#### 5. Update robots.txt
Add the new path pattern to the allow list.

---

## Customization Guide

### Changing Brand Colors
1. Go to Admin → Settings
2. Update `whitelabel_primary_color` — sets CSS `--primary` variable
3. Update `whitelabel_secondary_color`

### Custom Email Templates
1. Go to Admin → Email Templates
2. Edit the template key (e.g., `ticket.created`)
3. Available placeholders: `{user_name}`, `{ticket_uid}`, `{ticket_subject}`, `{reply_body}`, `{app_name}`, `{app_url}`

### Adding Custom Ticket Fields
1. Add a migration to add columns to `tickets` table
2. Or use the `custom_fields` JSON column for flexible additional data

### Changing Default Role Permissions
1. Edit `database/seeders/RolesAndPermissionsSeeder.php`
2. Re-run: `php artisan db:seed --class=RolesAndPermissionsSeeder`
3. Or update in code: `php artisan permission:cache-reset`

---

## Contribution Guidelines

### Before Submitting
1. Run tests: `php artisan test`
2. Run linter: `./vendor/bin/pint`
3. Check for security issues: `composer audit`
4. Ensure no `.env` or credential files are included
5. Write/update tests for new code
6. Update relevant documentation

### Pull Request Template
```markdown
## Summary
Brief description of changes.

## Type
- [ ] Bug fix
- [ ] New feature
- [ ] Refactor
- [ ] Documentation
- [ ] Test

## Testing
- [ ] Unit tests pass
- [ ] Feature tests pass
- [ ] Manual testing completed

## Screenshots (if UI change)
Attach before/after screenshots.

## Checklist
- [ ] Code follows PSR-12
- [ ] No sensitive data in logs
- [ ] Migration is reversible (down() method)
- [ ] API changes documented
```

### Code Review Points
- Does it follow existing conventions?
- Are there tests?
- Is there documentation?
- Are permissions handled correctly?
- Is user input properly validated?
- Is XSS/SQL injection prevented?
- Are API keys properly encrypted?
- Are there any N+1 queries?
