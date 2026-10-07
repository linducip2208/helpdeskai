<?php

namespace Tests\Feature;

use App\Models\AiFeatureConfig;
use App\Models\AiProvider;
use App\Models\AiProviderModel;
use App\Models\Department;
use App\Models\QaScore;
use App\Models\Setting;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\BypassesPairing;
use Tests\TestCase;

class QualityAssuranceTest extends TestCase
{
    use BypassesPairing, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bypassPairing();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_qa_scoring_disabled_by_default(): void
    {
        $department = Department::create(['name' => 'Support', 'is_active' => true]);
        $owner = User::factory()->create();
        $agent = User::factory()->create();
        $agent->assignRole('agent');

        $ticket = Ticket::create([
            'uid' => Ticket::generateUid(),
            'user_id' => $owner->id,
            'department_id' => $department->id,
            'subject' => 'QA off',
            'body' => 'Body',
            'priority' => 'medium',
            'status' => 'open',
            'source' => 'web',
        ]);

        app(TicketService::class)->addReply($ticket, $agent->id, 'Fixed it.', false);

        $this->assertDatabaseCount('qa_scores', 0);
    }

    public function test_qa_scoring_scores_agent_reply(): void
    {
        Setting::set('ai.qa_enabled', true);

        $provider = AiProvider::create([
            'name' => 'QA Test',
            'api_format' => 'openai_compatible',
            'base_url' => 'https://qa.example',
            'api_key_encrypted' => encrypt('k'),
            'is_active' => true,
            'priority' => 10,
            'timeout_seconds' => 10,
            'max_retries' => 1,
        ]);
        $model = AiProviderModel::create([
            'provider_id' => $provider->id,
            'model_id' => 'qa-model',
            'display_name' => 'QA',
            'capability' => 'chat',
            'is_active' => true,
        ]);
        AiFeatureConfig::create([
            'feature_key' => 'ticket.qa_score',
            'provider_id' => $provider->id,
            'model_id' => $model->id,
            'is_enabled' => true,
        ]);

        Http::fake(['*' => Http::response([
            'choices' => [['message' => ['content' => '{"accuracy":5,"completeness":4,"tone":5,"empathy":4,"policy_compliance":5,"knowledge_correctness":4,"feedback":"Good reply."}']]],
            'usage' => ['prompt_tokens' => 10, 'completion_tokens' => 5],
        ], 200)]);

        $department = Department::create(['name' => 'Support', 'is_active' => true]);
        $owner = User::factory()->create();
        $agent = User::factory()->create();
        $agent->assignRole('agent');

        $ticket = Ticket::create([
            'uid' => Ticket::generateUid(),
            'user_id' => $owner->id,
            'department_id' => $department->id,
            'subject' => 'QA on',
            'body' => 'Body',
            'priority' => 'medium',
            'status' => 'open',
            'source' => 'web',
        ]);

        app(TicketService::class)->addReply($ticket, $agent->id, 'Fixed it, please verify.', false);

        $score = QaScore::first();
        $this->assertNotNull($score);
        $this->assertEquals(4.5, $score->overall);

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)->get('/admin/qa')
            ->assertOk()
            ->assertSee('Agent Quality');
    }
}
