<?php

namespace Tests\Feature;

use App\Jobs\DeliverWebhook;
use App\Models\Department;
use App\Models\Ticket;
use App\Models\User;
use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use App\Services\TicketService;
use App\Services\WebhookService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\BypassesPairing;
use Tests\TestCase;

class WebhookTest extends TestCase
{
    use BypassesPairing, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bypassPairing();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function endpoint(array $overrides = []): WebhookEndpoint
    {
        $endpoint = WebhookEndpoint::create(array_merge([
            'name' => 'Test hook',
            'url' => 'https://example.com/hooks/helpdesk',
            'events' => ['ticket.created', 'ticket.replied'],
            'is_active' => true,
            'timeout_seconds' => 10,
        ], $overrides));
        $endpoint->setSecret('test-secret-123');

        return $endpoint;
    }

    public function test_ticket_created_queues_signed_delivery(): void
    {
        Queue::fake();

        $this->endpoint();
        $department = Department::create(['name' => 'Support', 'is_active' => true]);
        $user = User::factory()->create();

        $ticket = app(TicketService::class)->createTicket([
            'user_id' => $user->id,
            'subject' => 'Hook me',
            'body' => 'Body',
            'department_id' => $department->id,
            'priority' => 'medium',
        ]);

        $this->assertDatabaseHas('webhook_deliveries', [
            'event' => 'ticket.created',
        ]);

        Queue::assertPushed(DeliverWebhook::class);

        $delivery = WebhookDelivery::where('event', 'ticket.created')->first();
        $this->assertEquals($ticket->id, $delivery->payload['ticket']['id']);
    }

    public function test_inactive_endpoint_gets_no_delivery(): void
    {
        Queue::fake();

        $this->endpoint(['is_active' => false]);
        $department = Department::create(['name' => 'Support', 'is_active' => true]);
        $user = User::factory()->create();

        app(TicketService::class)->createTicket([
            'user_id' => $user->id,
            'subject' => 'No hook',
            'body' => 'Body',
            'department_id' => $department->id,
            'priority' => 'medium',
        ]);

        $this->assertDatabaseCount('webhook_deliveries', 0);
        Queue::assertNotPushed(DeliverWebhook::class);
    }

    public function test_send_signs_request_with_hmac(): void
    {
        $endpoint = $this->endpoint();
        $user = User::factory()->create();
        $department = Department::create(['name' => 'Support', 'is_active' => true]);
        $ticket = Ticket::create([
            'uid' => Ticket::generateUid(),
            'user_id' => $user->id,
            'department_id' => $department->id,
            'subject' => 'Sign me',
            'body' => 'Body',
            'priority' => 'medium',
            'status' => 'open',
            'source' => 'web',
        ]);

        $delivery = WebhookDelivery::create([
            'webhook_endpoint_id' => $endpoint->id,
            'event' => 'ticket.created',
            'payload' => ['event' => 'ticket.created', 'ticket' => ['id' => $ticket->id]],
            'idempotency_key' => hash('sha256', 'test-key-1'),
            'status' => 'pending',
        ]);

        Http::fake(['*' => Http::response('ok', 200)]);

        app(WebhookService::class)->send($delivery);

        Http::assertSent(function ($request) {
            if (! $request->hasHeader('X-Webhook-Signature')) {
                return false;
            }

            $timestamp = $request->header('X-Webhook-Timestamp')[0];
            $expected = 'sha256='.hash_hmac('sha256', $timestamp.'.'.$request->body(), 'test-secret-123');

            return hash_equals($expected, $request->header('X-Webhook-Signature')[0])
                && $request->hasHeader('X-Idempotency-Key');
        });

        $this->assertEquals('sent', $delivery->fresh()->status);
    }

    public function test_failed_send_throws_for_retry(): void
    {
        $endpoint = $this->endpoint();

        $delivery = WebhookDelivery::create([
            'webhook_endpoint_id' => $endpoint->id,
            'event' => 'ticket.created',
            'payload' => ['event' => 'ticket.created'],
            'idempotency_key' => hash('sha256', 'test-key-2'),
            'status' => 'pending',
        ]);

        Http::fake(['*' => Http::response('error', 500)]);

        $this->expectException(\RuntimeException::class);

        app(WebhookService::class)->send($delivery);
    }

    public function test_admin_can_manage_endpoints(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)->post('/admin/webhooks', [
            'name' => 'CRM',
            'url' => 'https://crm.example.com/hook',
            'events' => ['ticket.created'],
        ])->assertRedirect();

        $this->assertDatabaseHas('webhook_endpoints', ['name' => 'CRM']);

        $this->actingAs($admin)->post('/admin/webhooks', [
            'name' => 'Bad',
            'url' => 'not-a-url',
            'events' => ['ticket.created'],
        ])->assertSessionHasErrors('url');
    }
}
