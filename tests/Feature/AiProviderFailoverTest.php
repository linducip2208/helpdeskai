<?php

namespace Tests\Feature;

use App\Jobs\AnalyzeTicketSentiment;
use App\Jobs\ClassifyTicketWithAi;
use App\Models\AiBudget;
use App\Models\AiFeatureConfig;
use App\Models\AiProvider;
use App\Models\AiProviderModel;
use App\Models\AiUsageLog;
use App\Models\Department;
use App\Models\Setting;
use App\Models\Ticket;
use App\Models\User;
use App\Services\AiService;
use App\Services\TicketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\BypassesPairing;
use Tests\TestCase;

class AiProviderFailoverTest extends TestCase
{
    use BypassesPairing, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bypassPairing();
    }

    private function provider(string $name, int $priority, string $host = 'https://provider-a.example'): AiProvider
    {
        $provider = AiProvider::create([
            'name' => $name,
            'api_format' => 'openai_compatible',
            'base_url' => $host,
            'api_key_encrypted' => encrypt('test-key'),
            'is_active' => true,
            'priority' => $priority,
            'timeout_seconds' => 10,
            'max_retries' => 1,
        ]);

        $model = AiProviderModel::create([
            'provider_id' => $provider->id,
            'model_id' => 'test-model',
            'display_name' => 'Test Model',
            'capability' => 'chat',
            'cost_input_per_1m' => 1.0,
            'cost_output_per_1m' => 2.0,
            'is_active' => true,
            'priority' => 0,
        ]);

        AiFeatureConfig::firstOrCreate(
            ['feature_key' => 'ticket.classify'],
            [
                'provider_id' => $provider->id,
                'model_id' => $model->id,
                'is_enabled' => true,
            ]
        );

        return $provider;
    }

    private function chatSuccess(string $content, int $in = 10, int $out = 5): array
    {
        return [
            'choices' => [['message' => ['content' => $content]]],
            'usage' => ['prompt_tokens' => $in, 'completion_tokens' => $out],
            'model' => 'test-model',
        ];
    }

    public function test_fallback_to_secondary_on_500(): void
    {
        $this->provider('Primary', 10, 'https://primary.example');
        $this->provider('Secondary', 5, 'https://secondary.example');

        Http::fake(function ($request) {
            if (str_contains($request->url(), 'primary.example')) {
                return Http::response('boom', 500);
            }

            return Http::response($this->chatSuccess('{"priority":"high"}'), 200);
        });

        $result = app(AiService::class)->dispatch('ticket.classify', [
            ['role' => 'user', 'content' => 'hi'],
        ]);

        $this->assertEquals('{"priority":"high"}', $result['content']);
        $this->assertTrue($result['fallback_used']);
        $this->assertEquals('Secondary', $result['provider']);
        $this->assertEquals(2, AiUsageLog::count());
        $this->assertEquals(1, AiUsageLog::where('success', false)->count());
    }

    public function test_all_providers_down_returns_error(): void
    {
        $this->provider('Primary', 10, 'https://primary.example');

        Http::fake(['*' => Http::response('down', 503)]);

        $result = app(AiService::class)->dispatch('ticket.classify', [
            ['role' => 'user', 'content' => 'hi'],
        ]);

        $this->assertArrayHasKey('error', $result);
        $this->assertArrayNotHasKey('content', $result);
    }

    public function test_invalid_json_classification_marks_failed_without_crash(): void
    {
        $this->provider('Primary', 10, 'https://primary.example');

        Http::fake(['*' => Http::response($this->chatSuccess('this is definitely not json {{{'), 200)]);

        $department = Department::create(['name' => 'Support', 'is_active' => true]);
        $user = User::factory()->create();
        $ticket = Ticket::create([
            'uid' => Ticket::generateUid(),
            'user_id' => $user->id,
            'department_id' => $department->id,
            'subject' => 'Help',
            'body' => 'Body',
            'priority' => 'medium',
            'status' => 'open',
            'source' => 'web',
        ]);

        $this->assertNull(app(TicketService::class)->autoClassify($ticket));
        $this->assertNull($ticket->fresh()->ai_classified_at);
    }

    public function test_budget_blocks_dispatch(): void
    {
        $this->provider('Primary', 10, 'https://primary.example');

        AiBudget::create([
            'scope' => 'global',
            'scope_id' => null,
            'period' => 'daily',
            'limit_usd' => 0.0001,
            'is_active' => true,
        ]);

        Http::fake(['*' => Http::response($this->chatSuccess('{"priority":"high"}', 100000, 100000), 200)]);

        $first = app(AiService::class)->dispatch('ticket.classify', [
            ['role' => 'user', 'content' => 'hi'],
        ]);
        $this->assertArrayNotHasKey('error', $first);

        $second = app(AiService::class)->dispatch('ticket.classify', [
            ['role' => 'user', 'content' => 'hi'],
        ]);
        $this->assertStringContainsString('budget', strtolower($second['error']));
    }

    public function test_ticket_creation_queues_classification_job(): void
    {
        Queue::fake();

        $department = Department::create(['name' => 'Support', 'is_active' => true]);
        $user = User::factory()->create();

        app(TicketService::class)->createTicket([
            'user_id' => $user->id,
            'subject' => 'Async AI',
            'body' => 'Body',
            'department_id' => $department->id,
            'priority' => 'medium',
        ]);

        Queue::assertPushed(ClassifyTicketWithAi::class);
    }

    public function test_reply_queues_sentiment_job(): void
    {
        Queue::fake();

        $department = Department::create(['name' => 'Support', 'is_active' => true]);
        $user = User::factory()->create();
        $ticket = Ticket::create([
            'uid' => Ticket::generateUid(),
            'user_id' => $user->id,
            'department_id' => $department->id,
            'subject' => 'Async sentiment',
            'body' => 'Body',
            'priority' => 'medium',
            'status' => 'open',
            'source' => 'web',
        ]);

        app(TicketService::class)->addReply($ticket, $user->id, 'Thanks, great support!', false);

        Queue::assertPushed(AnalyzeTicketSentiment::class);
    }

    public function test_disabled_ai_returns_policy_error_without_http(): void
    {
        $this->provider('Primary', 10, 'https://primary.example');
        Setting::set('ai.enabled', false);

        Http::fake(['*' => Http::response($this->chatSuccess('x'), 200)]);

        $result = app(AiService::class)->dispatch('ticket.classify', [
            ['role' => 'user', 'content' => 'hi'],
        ]);

        $this->assertStringContainsString('disabled', strtolower($result['error']));
        Http::assertNothingSent();
    }
}
