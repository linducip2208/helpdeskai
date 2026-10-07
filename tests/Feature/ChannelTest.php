<?php

namespace Tests\Feature;

use App\Jobs\SendChannelMessage;
use App\Models\Department;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use App\Services\Channels\TelegramChannel;
use App\Services\Channels\WhatsAppChannel;
use App\Services\TicketService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\BypassesPairing;
use Tests\TestCase;

class ChannelTest extends TestCase
{
    use BypassesPairing, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bypassPairing();
        $this->seed(RolesAndPermissionsSeeder::class);

        config([
            'services.whatsapp.phone_number_id' => '123',
            'services.whatsapp.token' => 'wa-token',
            'services.whatsapp.app_secret' => 'wa-secret',
            'services.whatsapp.verify_token' => 'verify-me',
            'services.telegram.bot_token' => 'tg-token',
            'services.telegram.webhook_secret' => 'tg-secret',
        ]);
    }

    private function waPayload(string $from, string $body): array
    {
        return ['entry' => [[
            'changes' => [[
                'value' => [
                    'contacts' => [['wa_id' => $from, 'profile' => ['name' => 'WA User']]],
                    'messages' => [[
                        'from' => $from,
                        'id' => 'wamid.'.uniqid(),
                        'type' => 'text',
                        'text' => ['body' => $body],
                    ]],
                ],
            ]],
        ]]];
    }

    private function sign(string $body): string
    {
        return 'sha256='.hash_hmac('sha256', $body, 'wa-secret');
    }

    public function test_whatsapp_verify_handshake(): void
    {
        $this->get('/webhooks/whatsapp?hub_mode=subscribe&hub_verify_token=verify-me&hub_challenge=abc123')
            ->assertOk()
            ->assertSee('abc123', false);

        $this->get('/webhooks/whatsapp?hub_mode=subscribe&hub_verify_token=wrong&hub_challenge=abc123')
            ->assertForbidden();
    }

    public function test_whatsapp_inbound_creates_and_threads_ticket(): void
    {
        Department::create(['name' => 'Support', 'is_active' => true]);

        $payload = $this->waPayload('6281000001', 'Halo, butuh bantuan');
        $response = $this->call('POST', '/webhooks/whatsapp', [], [], [], [
            'HTTP_X-Hub-Signature-256' => $this->sign(json_encode($payload)),
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($payload));

        $response->assertOk()->assertJsonPath('data.ingested', 1);
        $this->assertDatabaseHas('tickets', ['source' => 'chat']);
        $this->assertDatabaseHas('users', ['whatsapp_id' => '6281000001']);

        $payload2 = $this->waPayload('6281000001', 'Masih butuh bantuan');
        $this->call('POST', '/webhooks/whatsapp', [], [], [], [
            'HTTP_X-Hub-Signature-256' => $this->sign(json_encode($payload2)),
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($payload2))->assertOk();

        $this->assertEquals(1, Ticket::count());
        $this->assertEquals(2, TicketReply::count());
    }

    public function test_whatsapp_rejects_bad_signature(): void
    {
        $this->call('POST', '/webhooks/whatsapp', [], [], [], [
            'HTTP_X-Hub-Signature-256' => 'sha256=invalid',
            'CONTENT_TYPE' => 'application/json',
        ], '{}')->assertForbidden();
    }

    public function test_telegram_inbound_and_secret_check(): void
    {
        Department::create(['name' => 'Support', 'is_active' => true]);

        $payload = ['message' => [
            'message_id' => 42,
            'from' => ['id' => 777001, 'first_name' => 'Tono'],
            'text' => 'Halo admin',
        ]];

        $this->postJson('/webhooks/telegram', $payload, ['X-Telegram-Bot-Api-Secret-Token' => 'tg-secret'])
            ->assertOk()
            ->assertJsonPath('data.ingested', 1);

        $this->assertDatabaseHas('users', ['telegram_id' => '777001']);

        $this->postJson('/webhooks/telegram', $payload, ['X-Telegram-Bot-Api-Secret-Token' => 'wrong'])
            ->assertForbidden();
    }

    public function test_agent_reply_sends_channel_message(): void
    {
        Queue::fake();

        $department = Department::create(['name' => 'Support', 'is_active' => true]);
        $owner = User::factory()->create(['whatsapp_id' => '6281000002']);
        $agent = User::factory()->create();
        $agent->assignRole('agent');

        $ticket = Ticket::create([
            'uid' => Ticket::generateUid(),
            'user_id' => $owner->id,
            'department_id' => $department->id,
            'subject' => 'WA ticket',
            'body' => 'Body',
            'priority' => 'medium',
            'status' => 'open',
            'source' => 'chat',
        ]);

        app(TicketService::class)->addReply($ticket, $agent->id, 'Balasan via WA.', false);

        Queue::assertPushed(SendChannelMessage::class);
    }

    public function test_outbound_http_shapes(): void
    {
        Http::fake([
            'graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.x']]], 200),
            'api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 9]], 200),
        ]);

        $wa = app(WhatsAppChannel::class);
        $this->assertEquals('wamid.x', $wa->send('6281', 'Halo'));

        $tg = app(TelegramChannel::class);
        $this->assertEquals('9', $tg->send('777', 'Halo'));

        Http::assertSentCount(2);
    }
}
