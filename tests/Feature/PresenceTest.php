<?php

namespace Tests\Feature;

use App\Events\TicketViewing;
use App\Models\Department;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\BypassesPairing;
use Tests\TestCase;

class PresenceTest extends TestCase
{
    use BypassesPairing, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bypassPairing();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_viewing_ticket_broadcasts_presence(): void
    {
        Event::fake([TicketViewing::class]);

        $department = Department::create(['name' => 'Support', 'is_active' => true]);
        $owner = User::factory()->create();
        $staff = User::factory()->create();
        $staff->assignRole('admin');

        $ticket = Ticket::create([
            'uid' => Ticket::generateUid(),
            'user_id' => $owner->id,
            'department_id' => $department->id,
            'subject' => 'Presence',
            'body' => 'Body',
            'priority' => 'medium',
            'status' => 'open',
            'source' => 'web',
        ]);

        $this->actingAs($staff)->get('/admin/tickets/'.$ticket->id)->assertOk();

        Event::assertDispatched(TicketViewing::class, fn ($e) => $e->ticket->id === $ticket->id && $e->viewer->id === $staff->id);
    }

    public function test_presence_event_targets_private_ticket_channel(): void
    {
        $ticket = new Ticket;
        $ticket->forceFill(['id' => 42]);
        $viewer = new User;
        $viewer->forceFill(['id' => 7, 'name' => 'Agent']);

        $event = new TicketViewing($ticket, $viewer);

        $this->assertEquals('private-ticket.42', $event->broadcastOn()->name);
        $this->assertEquals('ticket.viewing', $event->broadcastAs());
        $this->assertEquals(['id' => 7, 'name' => 'Agent'], $event->broadcastWith());
    }
}
