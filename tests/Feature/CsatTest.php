<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\BypassesPairing;
use Tests\TestCase;

class CsatTest extends TestCase
{
    use BypassesPairing, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bypassPairing();
    }

    private function ticket(User $owner, string $status = 'resolved'): Ticket
    {
        $department = Department::create(['name' => 'Support', 'is_active' => true]);

        return Ticket::create([
            'uid' => Ticket::generateUid(),
            'user_id' => $owner->id,
            'department_id' => $department->id,
            'subject' => 'CSAT ticket',
            'body' => 'Body',
            'priority' => 'medium',
            'status' => $status,
            'source' => 'web',
        ]);
    }

    public function test_owner_can_rate_resolved_ticket_once(): void
    {
        $owner = User::factory()->create();
        $ticket = $this->ticket($owner);

        $this->actingAs($owner)->post('/tickets/'.$ticket->id.'/rate', [
            'satisfaction_rating' => 5,
            'satisfaction_comment' => 'Great!',
        ])->assertRedirect();

        $this->assertEquals(5, $ticket->fresh()->satisfaction_rating);

        $this->actingAs($owner)->post('/tickets/'.$ticket->id.'/rate', [
            'satisfaction_rating' => 1,
        ])->assertStatus(422);
        $this->assertEquals(5, $ticket->fresh()->satisfaction_rating);
    }

    public function test_open_ticket_and_strangers_rejected(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $open = $this->ticket($owner, 'open');
        $resolved = $this->ticket($owner);

        $this->actingAs($owner)->post('/tickets/'.$open->id.'/rate', [
            'satisfaction_rating' => 5,
        ])->assertStatus(422);

        $this->actingAs($stranger)->post('/tickets/'.$resolved->id.'/rate', [
            'satisfaction_rating' => 5,
        ])->assertForbidden();
    }
}
