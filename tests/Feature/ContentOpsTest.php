<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\ImportController;
use App\Jobs\ImportJob;
use App\Models\AiFeatureConfig;
use App\Models\AiProvider;
use App\Models\AiProviderModel;
use App\Models\AiQuery;
use App\Models\Department;
use App\Models\ImportLog;
use App\Models\KbSearch;
use App\Models\KnowledgeArticle;
use App\Models\KnowledgeCategory;
use App\Models\User;
use App\Services\KnowledgeRagService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ViewErrorBag;
use Tests\BypassesPairing;
use Tests\TestCase;

class ContentOpsTest extends TestCase
{
    use BypassesPairing, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bypassPairing();
        $this->seed(RolesAndPermissionsSeeder::class);
        AiFeatureConfig::firstOrCreate(['feature_key' => 'knowledge.answer'], [
            'provider_id' => null, 'model_id' => null, 'is_enabled' => true, 'options' => [],
        ]);
    }

    private function admin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        return $user;
    }

    private function category(): KnowledgeCategory
    {
        return KnowledgeCategory::create([
            'name' => 'General',
            'slug' => 'general-'.uniqid(),
            'is_active' => true,
        ]);
    }

    private function article(array $overrides = []): KnowledgeArticle
    {
        $user = User::factory()->create();

        return KnowledgeArticle::create(array_merge([
            'title' => 'Original title',
            'slug' => 'original-title-'.uniqid(),
            'category_id' => $this->category()->id,
            'user_id' => $user->id,
            'content' => 'Original content.',
            'excerpt' => 'Original excerpt.',
            'status' => 'published',
            'language' => 'id',
        ], $overrides));
    }

    private function providerWithFake(): void
    {
        $provider = AiProvider::create([
            'name' => 'ContentOps Test',
            'api_format' => 'openai_compatible',
            'base_url' => 'https://contentops.example',
            'api_key_encrypted' => encrypt('k'),
            'is_active' => true,
            'priority' => 10,
            'timeout_seconds' => 10,
            'max_retries' => 1,
        ]);
        $model = AiProviderModel::create([
            'provider_id' => $provider->id,
            'model_id' => 'contentops-model',
            'display_name' => 'ContentOps',
            'capability' => 'chat',
            'is_active' => true,
        ]);
        AiFeatureConfig::where('feature_key', 'knowledge.answer')->update([
            'provider_id' => $provider->id, 'model_id' => $model->id,
        ]);
    }

    private function registerImportRoutes(): void
    {
        Route::middleware('web')->group(function () {
            Route::get('/_test/imports', [ImportController::class, 'index'])->name('admin.imports.index');
            Route::post('/_test/imports/upload', [ImportController::class, 'upload'])->name('admin.imports.upload');
            Route::post('/_test/imports/{log}/confirm', [ImportController::class, 'confirm'])->name('admin.imports.confirm');
            Route::get('/_test/imports/{log}', [ImportController::class, 'show'])->name('admin.imports.show');
        });
        // Routes registered after app boot are not name-indexed automatically.
        Route::getRoutes()->refreshNameLookups();
        Route::getRoutes()->refreshActionLookups();
    }

    public function test_article_update_creates_revision(): void
    {
        $staff = $this->admin();
        $article = $this->article();

        $this->actingAs($staff)->put(route('admin.knowledge.update', $article), [
            'title' => 'Updated title',
            'category_id' => $article->category_id,
            'content' => 'Updated content.',
            'excerpt' => 'Updated excerpt.',
        ])->assertRedirect();

        $this->assertDatabaseHas('kb_article_revisions', [
            'article_id' => $article->id,
            'title' => 'Original title',
            'content' => 'Original content.',
        ]);
        $this->assertEquals('Updated title', $article->fresh()->title);
    }

    public function test_scheduled_publish_publishes_due_drafts(): void
    {
        $due = $this->article(['status' => 'draft', 'published_at' => now()->subHour()]);
        $future = $this->article(['status' => 'draft', 'published_at' => now()->addDay()]);
        $already = $this->article(['status' => 'published', 'published_at' => now()->subDay()]);

        $this->artisan('kb:publish')->assertSuccessful();

        $this->assertEquals('published', $due->fresh()->status);
        $this->assertEquals('draft', $future->fresh()->status);
        $this->assertEquals('published', $already->fresh()->status);
    }

    public function test_search_is_logged(): void
    {
        $user = User::factory()->create();
        $this->article(['title' => 'Cara reset password', 'content' => 'Panduan reset password akun.']);

        $this->actingAs($user)->get('/knowledge-base/search?q=reset+password')->assertOk();

        $this->assertDatabaseHas('kb_searches', ['query' => 'reset password']);
        $logged = KbSearch::where('query', 'reset password')->first();
        $this->assertGreaterThanOrEqual(1, $logged->results_count);
        $this->assertEquals($user->id, $logged->user_id);
    }

    public function test_rag_answer_is_logged_as_ai_query(): void
    {
        $this->providerWithFake();
        $this->article(['title' => 'Reset password akun', 'content' => 'Untuk mereset password akun, buka halaman login lalu klik lupa password.']);

        Http::fake(['*' => Http::response([
            'choices' => [['message' => ['content' => '{"answer":"Buka halaman login lalu klik lupa password.","confidence":0.9}']]],
            'usage' => ['prompt_tokens' => 10, 'completion_tokens' => 5],
        ], 200)]);

        $result = app(KnowledgeRagService::class)->answer('bagaimana reset password?');

        $this->assertNotNull($result['answer']);
        $this->assertDatabaseHas('ai_queries', [
            'question' => 'bagaimana reset password?',
            'answer' => 'Buka halaman login lalu klik lupa password.',
        ]);
        $logged = AiQuery::where('question', 'bagaimana reset password?')->first();
        $this->assertEquals(0.9, (float) $logged->confidence);
        $this->assertNotEmpty($logged->sources);
        $this->assertNotEmpty($logged->provider);
    }

    public function test_ai_query_feedback_can_be_updated(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $query = AiQuery::create([
            'question' => 'How to reset?',
            'answer' => 'Click forgot password.',
            'confidence' => 0.8,
            'user_id' => $owner->id,
        ]);

        $this->actingAs($owner)->post(route('ai-queries.feedback', $query), [
            'feedback' => 'helpful',
        ])->assertRedirect();
        $this->assertEquals('helpful', $query->fresh()->feedback);

        $this->actingAs($stranger)->post(route('ai-queries.feedback', $query), [
            'feedback' => 'not_helpful',
        ])->assertForbidden();
        $this->assertEquals('helpful', $query->fresh()->feedback);

        $this->actingAs($owner)->post(route('ai-queries.feedback', $query), [
            'feedback' => 'meh',
        ])->assertSessionHasErrors('feedback');
    }

    public function test_import_dry_run_reports_row_errors(): void
    {
        $rows = [
            ['name' => 'Ada', 'email' => 'ada@example.com', 'phone' => '081', 'organization' => 'Acme'],
            ['name' => '', 'email' => 'no-name@example.com', 'phone' => '', 'organization' => ''],
            ['name' => 'Dupe', 'email' => 'ada@example.com', 'phone' => '', 'organization' => ''],
            ['name' => 'Bad', 'email' => 'not-an-email', 'phone' => '', 'organization' => ''],
        ];

        $result = ImportJob::dryRun('customers', $rows);

        $this->assertEquals(4, $result['total']);
        $this->assertEquals(1, $result['valid']);
        $this->assertEquals(3, $result['invalid']);
        $this->assertLessThanOrEqual(20, count($result['errors']));
    }

    public function test_import_job_imports_customers_and_is_idempotent(): void
    {
        Storage::fake('local');
        $staff = $this->admin();

        $csv = "name,email,phone,organization\nAda,ada@example.com,081,Acme\nBudi,budi@example.com,,Acme\n";
        Storage::disk('local')->put('imports/customers.csv', $csv);

        $log = ImportLog::create([
            'type' => 'customers',
            'filename' => 'imports/customers.csv',
            'total_rows' => 2,
            'status' => ImportLog::STATUS_PENDING,
            'user_id' => $staff->id,
        ]);

        (new ImportJob($log->id, ImportJob::defaultMapping('customers', ['name', 'email', 'phone', 'organization'])))->handle();

        $log->refresh();
        $this->assertEquals(ImportLog::STATUS_SUCCESS, $log->status);
        $this->assertEquals(2, $log->imported);
        $this->assertEquals(0, $log->failed);
        $this->assertDatabaseHas('users', ['email' => 'ada@example.com']);

        // Re-run must be safe: no duplicates, status stays success.
        (new ImportJob($log->id))->handle();

        $this->assertEquals(1, User::where('email', 'ada@example.com')->count());
        $this->assertEquals(ImportLog::STATUS_SUCCESS, $log->fresh()->status);
    }

    public function test_import_upload_preview_and_confirm_flow(): void
    {
        $this->registerImportRoutes();
        Storage::fake('local');
        $staff = $this->admin();

        $file = UploadedFile::fake()->createWithContent(
            'customers.csv',
            "name,email,phone\nAda,ada@example.com,081\n,bad-row\n"
        );

        $upload = $this->actingAs($staff)->post(route('admin.imports.upload'), [
            'type' => 'customers',
            'file' => $file,
        ]);
        $upload->assertOk()->assertViewIs('admin.imports.preview');

        $log = ImportLog::latest('id')->first();
        $this->assertNotNull($log);
        $this->assertEquals('customers', $log->type);

        // Dry-run: validates without saving.
        $dry = $this->actingAs($staff)->post(route('admin.imports.confirm', $log), [
            'mapping' => ['name' => 'name', 'email' => 'email', 'phone' => 'phone'],
            'dry_run' => true,
        ]);
        $dry->assertOk();
        $this->assertDatabaseMissing('users', ['email' => 'ada@example.com']);

        // Real confirm dispatches the job (sync queue) and stores results.
        $confirm = $this->actingAs($staff)->post(route('admin.imports.confirm', $log), [
            'mapping' => ['name' => 'name', 'email' => 'email', 'phone' => 'phone'],
        ]);
        $confirm->assertRedirect(route('admin.imports.show', $log));

        $log->refresh();
        $this->assertEquals(1, $log->imported);
        $this->assertEquals(1, $log->failed);
        $this->assertDatabaseHas('users', ['email' => 'ada@example.com']);

        $this->actingAs($staff)->get(route('admin.imports.show', $log))->assertOk()->assertViewIs('admin.imports.show');
        $this->actingAs($staff)->get(route('admin.imports.index'))->assertOk()->assertViewIs('admin.imports.index');
    }

    public function test_failed_searches_view_renders(): void
    {
        $staff = $this->admin();
        $this->actingAs($staff);
        view()->share('errors', new ViewErrorBag);
        KbSearch::create(['query' => 'zzz-no-result', 'results_count' => 0, 'language' => 'id', 'user_id' => $staff->id]);

        $html = view('admin.kb-analytics.failed', [
            'searches' => KbSearch::where('results_count', 0)->latest('id')->paginate(20),
            'topQueries' => [['query' => 'zzz-no-result', 'hits' => 1]],
        ])->render();

        $this->assertStringContainsString('zzz-no-result', $html);
    }

    public function test_ticket_import_creates_tickets_for_existing_customers(): void
    {
        Storage::fake('local');
        $staff = $this->admin();
        $department = Department::create(['name' => 'Support', 'is_active' => true]);
        $customer = User::factory()->create(['email' => 'cust@example.com']);

        $csv = "subject,body,customer_email,department,priority\nLogin issue,Cannot login,cust@example.com,Support,high\n";
        Storage::disk('local')->put('imports/tickets.csv', $csv);

        $log = ImportLog::create([
            'type' => 'tickets',
            'filename' => 'imports/tickets.csv',
            'total_rows' => 1,
            'status' => ImportLog::STATUS_PENDING,
            'user_id' => $staff->id,
        ]);

        (new ImportJob($log->id, ImportJob::defaultMapping('tickets', ['subject', 'body', 'customer_email', 'department', 'priority'])))->handle();

        $log->refresh();
        $this->assertEquals(ImportLog::STATUS_SUCCESS, $log->status);
        $this->assertDatabaseHas('tickets', [
            'subject' => 'Login issue',
            'user_id' => $customer->id,
            'department_id' => $department->id,
            'priority' => 'high',
        ]);
    }
}
