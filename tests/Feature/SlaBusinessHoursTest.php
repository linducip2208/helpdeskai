<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\SlaPolicy;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\BypassesPairing;
use Tests\TestCase;

class SlaBusinessHoursTest extends TestCase
{
    use RefreshDatabase, BypassesPairing;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bypassPairing();
        Role::create(['name' => 'agent']);
    }

    private function policy(Department $department, array $overrides = []): SlaPolicy
    {
        return SlaPolicy::create(array_merge([
            'name' => 'Standard',
            'department_id' => $department->id,
            'priority' => 'medium',
            'first_response_time' => 60,
            'resolution_time' => 480,
            'workdays' => [1, 2, 3, 4, 5],
            'work_start' => '08:00',
            'work_end' => '17:00',
            'timezone' => 'Asia/Jakarta',
            'use_business_hours' => true,
            'is_active' => true,
        ], $overrides));
    }

    public function test_response_and_resolution_dues_follow_business_hours(): void
    {
        $department = Department::create(['name' => 'Support', 'is_active' => true]);
        $this->policy($department);
        $user = User::factory()->create();

        $ticket = app(TicketService::class)->createTicket([
            'user_id' => $user->id,
            'subject' => 'SLA test',
            'body' => 'Body',
            'department_id' => $department->id,
            'priority' => 'medium',
        ]);

        $this->assertNotNull($ticket->sla_due_at);
        $this->assertNotNull($ticket->sla_response_due_at);
        $this->assertTrue($ticket->sla_response_due_at->lessThanOrEqualTo($ticket->sla_due_at));
    }

    public function test_response_breach_marks_ticket_when_no_first_response(): void
    {
        $department = Department::create(['name' => 'Support', 'is_active' => true]);
        $this->policy($department);
        $user = User::factory()->create();

        $ticket = Ticket::create([
            'uid' => Ticket::generateUid(),
            'user_id' => $user->id,
            'department_id' => $department->id,
            'subject' => 'Late',
            'body' => 'Body',
            'priority' => 'medium',
            'status' => 'open',
            'source' => 'web',
            'sla_response_due_at' => now()->subHour(),
            'sla_due_at' => now()->addDay(),
        ]);

        $breached = app(\App\Services\SlaService::class)->evaluateTicket($ticket);

        $this->assertTrue($breached);
        $this->assertTrue((bool) $ticket->fresh()->sla_breached);
    }

    public function test_warning_sent_once_before_breach(): void
    {
        $department = Department::create(['name' => 'Support', 'is_active' => true]);
        $this->policy($department);
        $agent = User::factory()->create();
        $agent->assignRole('agent');
        $ticket = Ticket::create([
            'uid' => Ticket::generateUid(),
            'user_id' => User::factory()->create()->id,
            'assigned_to' => $agent->id,
            'department_id' => $department->id,
            'subject' => 'At risk',
            'body' => 'Body',
            'priority' => 'medium',
            'status' => 'open',
            'source' => 'web',
            'created_at' => now()->subHours(3),
            'sla_due_at' => now()->addMinutes(30),
        ]);

        app(\App\Services\SlaService::class)->evaluateTicket($ticket);

        $this->assertNotNull($ticket->fresh()->sla_warned_at);
        $this->assertDatabaseHas('activity_logs', ['action' => 'sla_warning']);
        $this->assertDatabaseHas('notifications', ['notifiable_id' => $agent->id, 'type' => 'ticket.sla_warning']);

        // Second run does not duplicate the warning.
        app(\App\Services\SlaService::class)->evaluateTicket($ticket->fresh());
        $this->assertEquals(1, \App\Models\ActivityLog::where('action', 'sla_warning')->count());
    }
}
