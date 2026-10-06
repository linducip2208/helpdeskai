<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use App\Services\TicketService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\BypassesPairing;
use Tests\TestCase;

class MergeAndTimeTest extends TestCase
{
    use BypassesPairing, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bypassPairing();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function ticket(User $owner, Department $department, string $subject): Ticket
    {
        return Ticket::create([
            'uid' => Ticket::generateUid(),
            'user_id' => $owner->id,
            'department_id' => $department->id,
            'subject' => $subject,
            'body' => 'Body',
            'priority' => 'medium',
            'status' => 'open',
            'source' => 'web',
        ]);
    }

    public function test_merge_moves_replies_and_closes_secondary(): void
    {
        $department = Department::create(['name' => 'Support', 'is_active' => true]);
        $owner = User::factory()->create();
        $staff = User::factory()->create();
        $staff->assignRole('admin');

        $primary = $this->ticket($owner, $department, 'Primary issue');
        $secondary = $this->ticket($owner, $department, 'Duplicate issue');

        TicketReply::create([
            'ticket_id' => $secondary->id,
            'user_id' => $owner->id,
            'body' => 'Me too, same problem.',
            'is_internal' => false,
            'source' => 'web',
        ]);

        $this->actingAs($staff)->post('/admin/tickets/'.$primary->id.'/merge', [
            'target_uid' => $secondary->uid,
        ])->assertRedirect();

        $this->assertDatabaseHas('ticket_replies', ['ticket_id' => $primary->id, 'body' => 'Me too, same problem.']);
        $this->assertEquals('closed', $secondary->fresh()->status->value);
        $this->assertDatabaseHas('activity_logs', ['action' => 'ticket_merge']);
    }

    public function test_merge_requires_permission(): void
    {
        $department = Department::create(['name' => 'Support', 'is_active' => true]);
        $owner = User::factory()->create();
        $agent = User::factory()->create();
        $agent->assignRole('agent');

        $primary = $this->ticket($owner, $department, 'Primary issue');
        $secondary = $this->ticket($owner, $department, 'Duplicate issue');

        $this->actingAs($agent)->post('/admin/tickets/'.$primary->id.'/merge', [
            'target_uid' => $secondary->uid,
        ])->assertForbidden();

        $this->assertEquals('open', $secondary->fresh()->status->value);
    }

    public function test_log_time_records_entry_and_total(): void
    {
        $department = Department::create(['name' => 'Support', 'is_active' => true]);
        $owner = User::factory()->create();
        $agent = User::factory()->create();
        $agent->assignRole('agent');

        $ticket = $this->ticket($owner, $department, 'Timed work');

        $this->actingAs($agent)->post('/admin/tickets/'.$ticket->id.'/log-time', [
            'minutes' => 45,
            'note' => 'Investigated logs',
        ])->assertRedirect();

        $this->assertDatabaseHas('time_entries', [
            'ticket_id' => $ticket->id,
            'user_id' => $agent->id,
            'minutes' => 45,
        ]);

        $this->assertEquals(45, app(TicketService::class)->totalMinutes($ticket));

        $this->actingAs($agent)->get('/admin/tickets/'.$ticket->id)
            ->assertOk()
            ->assertSee('Investigated logs');
    }
}
