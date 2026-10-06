<?php

namespace Tests\Feature;

use App\Models\AutomationRule;
use App\Models\Category;
use App\Models\Department;
use App\Models\Ticket;
use App\Models\User;
use App\Services\EmailPipingService;
use App\Services\TicketService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\BypassesPairing;
use Tests\TestCase;

class EndToEndConnectivityTest extends TestCase
{
    use BypassesPairing, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bypassPairing();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_full_ticket_lifecycle_across_modules(): void
    {
        $support = Department::create(['name' => 'Support', 'is_active' => true]);
        $billing = Department::create(['name' => 'Billing', 'is_active' => true]);
        $category = Category::create(['name' => 'General', 'slug' => 'general', 'department_id' => $support->id, 'is_active' => true]);

        $customer = User::factory()->create();
        $customer->assignRole('customer');
        $agent = User::factory()->create();
        $agent->assignRole('agent');
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        // 1. Automation: urgent tickets move to Billing on creation.
        AutomationRule::create([
            'name' => 'Urgent to billing',
            'trigger_event' => 'ticket_created',
            'conditions' => ['priority' => 'urgent'],
            'actions' => [['type' => 'assign_department', 'value' => $billing->id]],
            'is_active' => true,
            'sort_order' => 0,
        ]);

        // 2. Customer creates ticket via web (TicketService: classify fallback + automation + notify).
        $this->actingAs($customer)->post('/tickets', [
            'subject' => 'Invoice wrong',
            'body' => 'My invoice is wrong.',
            'department_id' => $support->id,
            'category_id' => $category->id,
            'priority' => 'urgent',
        ])->assertRedirect();

        $ticket = Ticket::where('subject', 'Invoice wrong')->first();
        $this->assertNotNull($ticket);
        $this->assertEquals($billing->id, $ticket->fresh()->department_id);
        $this->assertDatabaseHas('activity_logs', ['action' => 'automation_fired']);

        // 3. Agent assigned + replies (first_response_at recorded).
        app(TicketService::class)->assignTicket($ticket, $agent->id);
        $this->assertDatabaseHas('notifications', ['notifiable_id' => $agent->id, 'type' => 'ticket.assigned']);

        app(TicketService::class)->addReply($ticket, $agent->id, 'We are fixing it.', false);
        $this->assertNotNull($ticket->fresh()->first_response_at);
        $this->assertDatabaseHas('notifications', ['notifiable_id' => $customer->id]);

        // 4. Internal note stays hidden from customer portal + API.
        app(TicketService::class)->addReply($ticket, $agent->id, 'INTERNAL-E2E-SECRET', true);

        $this->actingAs($customer)->get('/tickets/'.$ticket->id)
            ->assertOk()
            ->assertDontSee('INTERNAL-E2E-SECRET');

        $apiShow = $this->actingAs($customer, 'sanctum')->getJson('/api/tickets/'.$ticket->id)->assertOk();
        $this->assertNotContains(
            'INTERNAL-E2E-SECRET',
            collect($apiShow->json('data.replies'))->pluck('body')->all()
        );

        // 5. Resolve -> resolved_at + customer notified.
        app(TicketService::class)->changeStatus($ticket, 'resolved');
        $this->assertNotNull($ticket->fresh()->resolved_at);
        $this->assertDatabaseHas('notifications', ['notifiable_id' => $customer->id, 'type' => 'ticket.status_changed']);

        // 6. Email reply from owner threads onto the same ticket.
        $result = app(EmailPipingService::class)->processEmail([
            'from' => $customer->email,
            'subject' => "Re: [{$ticket->uid}] thanks",
            'text' => 'Thanks, confirmed fixed.',
            'message_id' => 'e2e-1',
        ]);
        $this->assertEquals($ticket->id, $result->id);
        $this->assertDatabaseHas('ticket_replies', ['ticket_id' => $ticket->id, 'body' => 'Thanks, confirmed fixed.']);

        // 7. Scheduler commands run clean.
        $this->artisan('sla:check')->assertSuccessful();
        $this->artisan('tickets:reminders')->assertSuccessful();

        // 8. API analytics wiring (previously bound to missing methods).
        $this->actingAs($admin, 'sanctum')->getJson('/api/analytics/summary')->assertOk()
            ->assertJsonPath('success', true);
        $this->actingAs($admin, 'sanctum')->getJson('/api/analytics/tickets-by-status')->assertOk()
            ->assertJsonPath('success', true);

        // 9. Web analytics page renders real numbers, exports stream CSV.
        $this->actingAs($admin)->get('/admin/analytics')->assertOk()->assertSee('Created vs Resolved');
        $this->actingAs($admin)->get('/admin/export/tickets.csv')->assertOk();
        $this->actingAs($admin)->get('/admin/export/agents.csv')->assertOk();
        $this->actingAs($admin)->get('/admin/export/sla.csv')->assertOk();
        $this->actingAs($admin)->get('/admin/export/ai-usage.csv')->assertOk();

        // 10. Admin dashboard renders with charts data.
        $this->actingAs($admin)->get('/admin')->assertOk()->assertSee('Ticket Trends');

        // 11. PWA shell files exist and are referenced by layouts.
        $this->assertFileExists(public_path('manifest.json'));
        $this->assertFileExists(public_path('sw.js'));
        $this->assertFileExists(public_path('offline.html'));
        $this->assertFileExists(public_path('icons/icon-192.svg'));
    }
}
