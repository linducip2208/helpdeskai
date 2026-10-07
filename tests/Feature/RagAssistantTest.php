<?php

namespace Tests\Feature;

use App\Models\AiFeatureConfig;
use App\Models\AiProvider;
use App\Models\AiProviderModel;
use App\Models\Department;
use App\Models\KnowledgeArticle;
use App\Models\Ticket;
use App\Models\User;
use App\Services\KnowledgeRagService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\BypassesPairing;
use Tests\TestCase;

class RagAssistantTest extends TestCase
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

    private function article(string $title, string $content): KnowledgeArticle
    {
        $user = User::factory()->create();

        return KnowledgeArticle::create([
            'title' => $title,
            'slug' => Str::slug($title).'-'.uniqid(),
            'user_id' => $user->id,
            'content' => $content,
            'status' => 'published',
            'language' => 'id',
        ]);
    }

    public function test_retrieve_scores_relevant_articles(): void
    {
        $this->article('Reset password akun', 'Untuk mereset password akun, buka halaman login lalu klik lupa password.');
        $this->article('Jadwal libur kantor', 'Kantor tutup setiap hari Minggu dan tanggal merah.');

        $hits = app(KnowledgeRagService::class)->retrieve('bagaimana cara reset password akun saya');

        $this->assertNotEmpty($hits);
        $this->assertEquals('Reset password akun', $hits[0]['article']->title);
        $this->assertGreaterThan(0, $hits[0]['score']);
    }

    public function test_answer_grounds_in_sources_with_confidence(): void
    {
        $this->providerWithFake();
        $this->article('Reset password akun', 'Untuk mereset password akun, buka halaman login lalu klik lupa password.');

        Http::fake(['*' => Http::response([
            'choices' => [['message' => ['content' => '{"answer":"Buka halaman login lalu klik lupa password.","confidence":0.9}']]],
            'usage' => ['prompt_tokens' => 10, 'completion_tokens' => 5],
        ], 200)]);

        $result = app(KnowledgeRagService::class)->answer('bagaimana reset password?');

        $this->assertEquals('Buka halaman login lalu klik lupa password.', $result['answer']);
        $this->assertEquals(0.9, $result['confidence']);
        $this->assertNotEmpty($result['sources']);
    }

    public function test_answer_without_sources_returns_error(): void
    {
        $result = app(KnowledgeRagService::class)->answer('pertanyaan yang tidak ada jawabannya xyzzy');

        $this->assertNull($result['answer']);
        $this->assertArrayHasKey('error', $result);
    }

    public function test_recommend_endpoint_lists_articles_for_ticket(): void
    {
        $this->article('Reset password akun', 'Untuk mereset password akun, buka halaman login lalu klik lupa password.');

        $department = Department::create(['name' => 'Support', 'is_active' => true]);
        $owner = User::factory()->create();
        $staff = User::factory()->create();
        $staff->assignRole('admin');

        $ticket = Ticket::create([
            'uid' => Ticket::generateUid(),
            'user_id' => $owner->id,
            'department_id' => $department->id,
            'subject' => 'Tidak bisa login, lupa password',
            'body' => 'Saya lupa password akun saya.',
            'priority' => 'medium',
            'status' => 'open',
            'source' => 'web',
        ]);

        $response = $this->actingAs($staff)->getJson('/admin/tickets/'.$ticket->id.'/ai-recommend');
        $response->assertOk()->assertJsonPath('success', true);
        $this->assertNotEmpty($response->json('data'));
    }

    private function providerWithFake(): void
    {
        $provider = AiProvider::create([
            'name' => 'RAG Test',
            'api_format' => 'openai_compatible',
            'base_url' => 'https://rag.example',
            'api_key_encrypted' => encrypt('k'),
            'is_active' => true,
            'priority' => 10,
            'timeout_seconds' => 10,
            'max_retries' => 1,
        ]);
        $model = AiProviderModel::create([
            'provider_id' => $provider->id,
            'model_id' => 'rag-model',
            'display_name' => 'RAG',
            'capability' => 'chat',
            'is_active' => true,
        ]);
        AiFeatureConfig::where('feature_key', 'knowledge.answer')->update([
            'provider_id' => $provider->id, 'model_id' => $model->id,
        ]);
    }
}
