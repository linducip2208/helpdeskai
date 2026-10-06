<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\BypassesPairing;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use BypassesPairing, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bypassPairing();
    }

    public function test_home_page_loads(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_blog_index_loads(): void
    {
        $this->get('/blog')->assertOk();
    }

    public function test_sitemap_is_valid_xml(): void
    {
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml')
            ->assertSee('<urlset', false);
    }

    public function test_robots_route_returns_directives(): void
    {
        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Sitemap');
    }

    public function test_blog_rss_feed_lists_published_posts(): void
    {
        $author = User::factory()->create();

        Post::create([
            'title' => 'Hello World',
            'slug' => 'hello-world',
            'user_id' => $author->id,
            'content' => 'Body content here.',
            'excerpt' => 'A short excerpt.',
            'category' => 'News',
            'status' => 'published',
            'published_at' => now(),
        ]);

        Post::create([
            'title' => 'Draft Post',
            'slug' => 'draft-post',
            'user_id' => $author->id,
            'content' => 'Draft body.',
            'status' => 'draft',
        ]);

        $response = $this->get('/blog/feed.xml');

        $response->assertOk();
        $this->assertStringContainsString('application/rss+xml', $response->headers->get('Content-Type'));
        $response->assertSee('<rss', false);
        $response->assertSee('Hello World', false);
        $response->assertDontSee('Draft Post', false);
    }

    public function test_blog_category_filter_loads(): void
    {
        $author = User::factory()->create();

        Post::create([
            'title' => 'Categorized',
            'slug' => 'categorized',
            'user_id' => $author->id,
            'content' => 'Body.',
            'category' => 'Guides',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->get('/blog/category/Guides')
            ->assertOk()
            ->assertSee('Categorized');
    }
}
