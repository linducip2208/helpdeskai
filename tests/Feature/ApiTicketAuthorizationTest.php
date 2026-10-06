<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\BypassesPairing;
use Tests\TestCase;

class ApiTicketAuthorizationTest extends TestCase
{
    use BypassesPairing, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bypassPairing();
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'customer']);
    }

    private function makeFixtures(): array
    {
        $department = Department::create(['name' => 'Support', 'is_active' => true]);

        $owner = User::factory()->create();
        $owner->assignRole('customer');

        $other = User::factory()->create();
        $other->assignRole('customer');

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $ticket = Ticket::create([
            'uid' => Ticket::generateUid(),
            'user_id' => $owner->id,
            'department_id' => $department->id,
            'subject' => 'Owner private ticket',
            'body' => 'Secret content',
            'priority' => 'medium',
            'status' => 'open',
            'source' => 'web',
        ]);

        return [$owner, $other, $admin, $ticket];
    }

    public function test_guest_api_is_unauthenticated(): void
    {
        $this->getJson('/api/tickets')->assertUnauthorized();
    }

    public function test_customer_index_only_lists_own_tickets(): void
    {
        [$owner, $other, $admin, $ticket] = $this->makeFixtures();

        Ticket::create([
            'uid' => Ticket::generateUid(),
            'user_id' => $other->id,
            'department_id' => $ticket->department_id,
            'subject' => 'Other ticket',
            'body' => 'Other content',
            'priority' => 'low',
            'status' => 'open',
            'source' => 'web',
        ]);

        $response = $this->actingAs($owner, 'sanctum')->getJson('/api/tickets');
        $response->assertOk();

        $uids = collect($response->json('data.data'))->pluck('uid');
        $this->assertContains($ticket->uid, $uids);
        $this->assertCount(1, $uids);
    }

    public function test_customer_cannot_show_update_or_delete_other_ticket(): void
    {
        [$owner, $other, $admin, $ticket] = $this->makeFixtures();

        $this->actingAs($other, 'sanctum')->getJson('/api/tickets/'.$ticket->id)->assertForbidden();
        $this->actingAs($other, 'sanctum')->putJson('/api/tickets/'.$ticket->id, ['subject' => 'Hacked'])->assertForbidden();
        $this->actingAs($other, 'sanctum')->deleteJson('/api/tickets/'.$ticket->id)->assertForbidden();

        $this->assertDatabaseHas('tickets', ['id' => $ticket->id, 'subject' => 'Owner private ticket']);
    }

    public function test_customer_cannot_delete_own_ticket(): void
    {
        [$owner, $other, $admin, $ticket] = $this->makeFixtures();

        $this->actingAs($owner, 'sanctum')->deleteJson('/api/tickets/'.$ticket->id)->assertForbidden();
        $this->assertDatabaseHas('tickets', ['id' => $ticket->id]);
    }

    public function test_staff_can_manage_and_admin_can_delete(): void
    {
        [$owner, $other, $admin, $ticket] = $this->makeFixtures();

        $this->actingAs($admin, 'sanctum')->getJson('/api/tickets/'.$ticket->id)->assertOk();

        $this->actingAs($admin, 'sanctum')
            ->putJson('/api/tickets/'.$ticket->id, ['priority' => 'urgent'])
            ->assertOk();
        $this->assertDatabaseHas('tickets', ['id' => $ticket->id, 'priority' => 'urgent']);

        $this->actingAs($admin, 'sanctum')->deleteJson('/api/tickets/'.$ticket->id)->assertOk();
        $this->assertDatabaseMissing('tickets', ['id' => $ticket->id]);
    }

    public function test_api_rejects_invalid_status_and_priority(): void
    {
        [$owner, $other, $admin, $ticket] = $this->makeFixtures();

        $this->actingAs($admin, 'sanctum')
            ->putJson('/api/tickets/'.$ticket->id, ['status' => 'hacked'])
            ->assertUnprocessable();

        $this->actingAs($admin, 'sanctum')
            ->putJson('/api/tickets/'.$ticket->id, ['priority' => 'critical'])
            ->assertUnprocessable();
    }
}
