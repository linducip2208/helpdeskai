<?php

namespace Tests\Feature;

use App\Services\Seo\IndexNowService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IndexNowTest extends TestCase
{
    public function test_submit_sends_urls_and_dedupes(): void
    {
        config(['services.indexnow.key' => 'testkey123']);
        Http::fake(['api.indexnow.org/*' => Http::response('', 200)]);

        $service = new IndexNowService();

        $first = $service->submit('https://example.com/blog/a');
        $this->assertSame(1, $first);

        // Second submission of the same URL is skipped (cached).
        $second = $service->submit('https://example.com/blog/a');
        $this->assertSame(0, $second);

        Http::assertSentCount(1);
    }

    public function test_submit_without_key_returns_zero(): void
    {
        config(['services.indexnow.key' => null]);
        Http::fake();

        $service = new IndexNowService();

        $this->assertSame(0, $service->submit('https://example.com/x'));
        Http::assertNothingSent();
    }
}
