<?php

namespace Tests\Feature;

use App\Models\KnowledgeArticle;
use App\Models\KnowledgeCategory;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\BypassesPairing;
use Tests\TestCase;

class KnowledgeWorkflowTest extends TestCase
{
    use BypassesPairing, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bypassPairing();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_review_approve_reject_flow(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $article = KnowledgeArticle::create([
            'title' => 'Workflow article',
            'slug' => 'workflow-article-'.uniqid(),
            'user_id' => $admin->id,
            'content' => 'Body text here.',
            'status' => 'draft',
        ]);

        $this->actingAs($admin)->post("/admin/knowledge/{$article->id}/submit-review")->assertRedirect();
        $this->assertEquals('review', $article->fresh()->status);

        $this->actingAs($admin)->post("/admin/knowledge/{$article->id}/approve")->assertRedirect();
        $fresh = $article->fresh();
        $this->assertEquals('published', $fresh->status);
        $this->assertNotNull($fresh->published_at);
    }

    public function test_invalid_transitions_rejected(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $article = KnowledgeArticle::create([
            'title' => 'Direct publish attempt',
            'slug' => 'direct-'.uniqid(),
            'user_id' => $admin->id,
            'content' => 'Body text here.',
            'status' => 'draft',
        ]);

        $this->actingAs($admin)->post("/admin/knowledge/{$article->id}/approve")->assertStatus(422);
        $this->actingAs($admin)->post("/admin/knowledge/{$article->id}/reject")->assertStatus(422);
    }

    public function test_kb_article_crud_roundtrip(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $category = KnowledgeCategory::create(['name' => 'Umum', 'slug' => 'umum-'.uniqid(), 'is_active' => true]);

        $this->actingAs($admin)->post('/admin/knowledge', [
            'title' => 'Cara reset password',
            'category_id' => $category->id,
            'content' => 'Langkah-langkah reset password akun anda.',
            'status' => 'draft',
            'tags' => 'password, akun',
        ])->assertRedirect();

        $article = KnowledgeArticle::where('title', 'Cara reset password')->first();
        $this->assertNotNull($article);
        $this->assertEquals(['password', 'akun'], $article->tags);

        $this->actingAs($admin)->put("/admin/knowledge/{$article->id}", [
            'title' => 'Cara reset password',
            'category_id' => $category->id,
            'content' => 'Langkah baru.',
            'status' => 'draft',
        ])->assertRedirect();
        $this->assertDatabaseHas('kb_article_revisions', ['article_id' => $article->id]);

        $this->actingAs($admin)->get('/admin/knowledge')->assertOk()->assertSee('Cara reset password');
    }
}
