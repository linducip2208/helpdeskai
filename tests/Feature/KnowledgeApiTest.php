<?php

namespace Tests\Feature;

use App\Models\KnowledgeArticle;
use App\Models\KnowledgeCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\BypassesPairing;
use Tests\TestCase;

class KnowledgeApiTest extends TestCase
{
    use BypassesPairing, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bypassPairing();
    }

    private function article(string $title, string $status = 'published'): KnowledgeArticle
    {
        $user = User::factory()->create();

        return KnowledgeArticle::create([
            'title' => $title,
            'slug' => Str::slug($title).'-'.uniqid(),
            'user_id' => $user->id,
            'content' => 'Content about '.$title,
            'status' => $status,
            'language' => 'id',
        ]);
    }

    public function test_search_route_matches_before_article_binding(): void
    {
        $this->article('Reset password guide');
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/knowledge/search?q=password');
        $response->assertOk()->assertJsonPath('success', true);
        $this->assertNotEmpty($response->json('data.data'));
    }

    public function test_draft_articles_hidden_from_customers(): void
    {
        $draft = $this->article('Secret draft', 'draft');
        $customer = User::factory()->create();

        $this->actingAs($customer, 'sanctum')
            ->getJson('/api/knowledge/'.$draft->id)
            ->assertNotFound();

        $response = $this->actingAs($customer, 'sanctum')->getJson('/api/knowledge');
        $response->assertOk()->assertJsonPath('success', true);
        $uids = collect($response->json('data.data'))->pluck('id');
        $this->assertNotContains($draft->id, $uids);
    }

    public function test_categories_and_category_articles_endpoints(): void
    {
        $category = KnowledgeCategory::create(['name' => 'Umum', 'slug' => 'umum', 'is_active' => true]);
        $this->article('Public article');
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')->getJson('/api/knowledge/categories')
            ->assertOk()->assertJsonPath('success', true);

        $this->actingAs($user, 'sanctum')->getJson('/api/knowledge/category/'.$category->id)
            ->assertOk()->assertJsonPath('success', true);
    }
}
