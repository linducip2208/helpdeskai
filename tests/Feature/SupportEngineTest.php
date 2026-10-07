<?php

namespace Tests\Feature;

use App\Events\TicketAssigned;
use App\Events\TicketReplied;
use App\Events\TicketStatusChanged;
use App\Models\AutomationRule;
use App\Models\Department;
use App\Models\Macro;
use App\Models\Setting;
use App\Models\SlaEscalationRule;
use App\Models\Ticket;
use App\Models\User;
use App\Services\EscalationService;
use App\Services\TicketAssignmentService;
use App\Services\TicketService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\BypassesPairing;
use Tests\TestCase;

class SupportEngineTest extends TestCase
{
    use BypassesPairing, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bypassPairing();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function agent(): User
    {
        $agent = User::factory()->create();
        $agent->assignRole('agent');

        return $agent;
    }

    private function ticket(User $owner, array $over = []): Ticket
    {
        $department = Department::create(['name' => 'Support '.uniqid(), 'is_active' => true]);

        return Ticket::create(array_merge([
            'uid' => Ticket::generateUid(),
            'user_id' => $owner->id,
            'department_id' => $department->id,
            'subject' => 'Engine ticket',
            'body' => 'Body',
            'priority' => 'medium',
            'status' => 'open',
            'source' => 'web',
        ], $over));
    }

    public function test_illegal_status_transition_rejected(): void
    {
        $owner = User::factory()->create();
        $ticket = $this->ticket($owner, ['status' => 'closed']);

        $this->expectException(HttpException::class);

        app(TicketService::class)->changeStatus($ticket, 'resolved');
    }

    public function test_tags_links_watchers_and_split(): void
    {
        $owner = User::factory()->create();
        $staff = User::factory()->create();
        $staff->assignRole('admin');
        $ticket = $this->ticket($owner);
        $svc = app(TicketService::class);

        $svc->addTag($ticket, 'VIP');
        $this->assertDatabaseHas('tags', ['name' => 'vip']);
        $this->assertEquals(1, $ticket->fresh()->tags()->count());

        $other = $this->ticket($owner);
        $svc->linkTickets($ticket, $other, 'related', $staff->id);
        $this->assertDatabaseHas('ticket_links', ['ticket_id' => $ticket->id, 'relation' => 'related']);

        $svc->watch($ticket, $staff->id);
        $this->assertDatabaseHas('watchers', ['ticket_id' => $ticket->id, 'user_id' => $staff->id]);
        $svc->unwatch($ticket, $staff->id);
        $this->assertDatabaseMissing('watchers', ['ticket_id' => $ticket->id, 'user_id' => $staff->id]);

        $followUp = $svc->splitTicket($ticket, 'Follow up', 'Split body', $staff->id);
        $this->assertDatabaseHas('tickets', ['id' => $followUp->id]);
        $this->assertDatabaseHas('ticket_links', ['ticket_id' => $ticket->id, 'relation' => 'follow_up']);
    }

    public function test_macro_applies_actions(): void
    {
        $owner = User::factory()->create();
        $agent = $this->agent();
        $staff = User::factory()->create();
        $staff->assignRole('admin');
        $ticket = $this->ticket($owner);

        $macro = Macro::create([
            'name' => 'Triage',
            'visibility' => 'shared',
            'user_id' => $staff->id,
            'actions' => [
                'reply' => 'Looking into it.',
                'priority' => 'high',
                'assigned_to' => $agent->id,
                'add_tags' => ['triage'],
            ],
            'is_active' => true,
        ]);

        $this->actingAs($staff)->post("/admin/macros/{$macro->id}/apply/{$ticket->id}")->assertRedirect();

        $fresh = $ticket->fresh();
        $this->assertEquals('high', $fresh->priority);
        $this->assertEquals($agent->id, $fresh->assigned_to);
        $this->assertDatabaseHas('ticket_replies', ['ticket_id' => $ticket->id, 'body' => 'Looking into it.']);
        $this->assertDatabaseHas('tags', ['name' => 'triage']);
    }

    public function test_mention_notifies_agent(): void
    {
        $owner = User::factory()->create();
        $agent = User::factory()->create(['name' => 'AgentSmith']);
        $agent->assignRole('agent');
        $ticket = $this->ticket($owner);

        app(TicketService::class)->addReply($ticket, $owner->id, 'Please check this @AgentSmith', true);

        $this->assertDatabaseHas('notifications', ['notifiable_id' => $agent->id, 'type' => 'ticket.mentioned']);
    }

    public function test_assignment_strategies(): void
    {
        $owner = User::factory()->create();
        $a1 = $this->agent();
        $a2 = $this->agent();

        $this->ticket($owner, ['assigned_to' => $a1->id, 'status' => 'open']);
        $this->ticket($owner, ['assigned_to' => $a1->id, 'status' => 'open']);

        $ticket = $this->ticket($owner);
        $picked = app(TicketAssignmentService::class)->strategy();
        $this->assertEquals('manual', $picked->name());

        Setting::set('assignment.strategy', 'least_load');
        $picked = app(TicketAssignmentService::class)->strategy();
        $this->assertEquals($a2->id, $picked->pick($ticket)->id);

        Setting::set('assignment.strategy', 'round_robin');
        $picked = app(TicketAssignmentService::class)->strategy();
        $this->assertNotNull($picked->pick($ticket));
    }

    public function test_escalation_rule_fires_once(): void
    {
        $owner = User::factory()->create();
        $agent = $this->agent();
        $ticket = $this->ticket($owner, [
            'assigned_to' => $agent->id,
            'sla_breached' => true,
            'sla_due_at' => now()->subHours(2),
        ]);

        SlaEscalationRule::create([
            'name' => 'Breach bump',
            'trigger' => 'resolution_breach',
            'after_minutes' => 60,
            'action_priority' => 'urgent',
            'notify_assignee' => true,
            'is_active' => true,
        ]);

        $count = app(EscalationService::class)->run();

        $this->assertEquals(1, $count);
        $this->assertEquals('urgent', $ticket->fresh()->priority);
        $this->assertDatabaseHas('notifications', ['notifiable_id' => $agent->id, 'type' => 'ticket.escalated']);

        $this->assertEquals(0, app(EscalationService::class)->run());
    }

    public function test_automation_run_logged_with_new_triggers(): void
    {
        $owner = User::factory()->create();
        $ticket = $this->ticket($owner);

        AutomationRule::create([
            'name' => 'Tag urgent',
            'trigger_event' => 'ticket_created',
            'conditions' => ['priority' => 'urgent'],
            'actions' => [['type' => 'add_tag', 'value' => 'hot']],
            'is_active' => true,
            'sort_order' => 0,
        ]);

        app(TicketService::class)->createTicket([
            'user_id' => $owner->id,
            'subject' => 'Fire',
            'body' => 'Body',
            'department_id' => $ticket->department_id,
            'priority' => 'urgent',
        ]);

        $this->assertDatabaseHas('automation_runs', ['status' => 'success']);
    }

    public function test_realtime_events_broadcast(): void
    {
        Event::fake([TicketReplied::class, TicketStatusChanged::class, TicketAssigned::class]);

        $owner = User::factory()->create();
        $agent = $this->agent();
        $ticket = $this->ticket($owner);

        app(TicketService::class)->addReply($ticket, $agent->id, 'Working on it.', false);
        app(TicketService::class)->changeStatus($ticket, 'waiting');
        app(TicketService::class)->assignTicket($ticket, $agent->id);

        Event::assertDispatched(TicketReplied::class);
        Event::assertDispatched(TicketStatusChanged::class);
        Event::assertDispatched(TicketAssigned::class);
    }
}
