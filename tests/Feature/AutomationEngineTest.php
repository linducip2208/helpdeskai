<?php

namespace Tests\Feature;

use App\Models\AutomationRule;
use App\Models\Department;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\BypassesPairing;
use Tests\TestCase;

class AutomationEngineTest extends TestCase
{
    use BypassesPairing, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bypassPairing();
    }

    private function department(): Department
    {
        return Department::create(['name' => 'Support', 'is_active' => true]);
    }

    public function test_rule_fires_on_ticket_created(): void
    {
        $department = $this->department();
        $other = Department::create(['name' => 'Billing', 'is_active' => true]);

        AutomationRule::create([
            'name' => 'Urgent to billing',
            'trigger_event' => 'ticket_created',
            'conditions' => ['priority' => 'urgent'],
            'actions' => [
                ['type' => 'assign_department', 'value' => $other->id],
            ],
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $user = User::factory()->create();

        $ticket = app(TicketService::class)->createTicket([
            'user_id' => $user->id,
            'subject' => 'Down',
            'body' => 'Everything is down',
            'department_id' => $department->id,
            'priority' => 'urgent',
        ]);

        $this->assertEquals($other->id, $ticket->fresh()->department_id);
        $this->assertDatabaseHas('activity_logs', ['action' => 'automation_fired']);
    }

    public function test_rule_does_not_fire_when_conditions_mismatch(): void
    {
        $department = $this->department();

        AutomationRule::create([
            'name' => 'Urgent only',
            'trigger_event' => 'ticket_created',
            'conditions' => ['priority' => 'urgent'],
            'actions' => [['type' => 'set_priority', 'value' => 'low']],
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $user = User::factory()->create();

        $ticket = app(TicketService::class)->createTicket([
            'user_id' => $user->id,
            'subject' => 'Question',
            'body' => 'Just asking',
            'department_id' => $department->id,
            'priority' => 'medium',
        ]);

        $this->assertEquals('medium', $ticket->fresh()->priority);
        $this->assertDatabaseMissing('activity_logs', ['action' => 'automation_fired']);
    }

    public function test_recursive_status_rule_terminates(): void
    {
        $department = $this->department();

        AutomationRule::create([
            'name' => 'Loop',
            'trigger_event' => 'ticket_status_changed',
            'conditions' => ['status' => 'open'],
            'actions' => [['type' => 'set_status', 'value' => 'open']],
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $user = User::factory()->create();

        $ticket = Ticket::create([
            'uid' => Ticket::generateUid(),
            'user_id' => $user->id,
            'department_id' => $department->id,
            'subject' => 'Loop',
            'body' => 'Body',
            'priority' => 'medium',
            'status' => 'open',
            'source' => 'web',
        ]);

        app(TicketService::class)->changeStatus($ticket, 'open');

        $this->assertEquals('open', $ticket->fresh()->status->value);
        $this->assertTrue(true);
    }
}
