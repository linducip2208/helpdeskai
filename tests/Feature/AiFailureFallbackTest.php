<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\BypassesPairing;
use Tests\TestCase;

class AiFailureFallbackTest extends TestCase
{
    use BypassesPairing, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bypassPairing();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_ticket_creation_works_without_ai_provider(): void
    {
        $department = Department::create(['name' => 'Support', 'is_active' => true]);
        $user = User::factory()->create();

        $ticket = app(TicketService::class)->createTicket([
            'user_id' => $user->id,
            'subject' => 'No AI here',
            'body' => 'Plain ticket',
            'department_id' => $department->id,
            'priority' => 'medium',
        ]);

        $this->assertNotNull($ticket->id);
        $this->assertDatabaseHas('tickets', ['id' => $ticket->id]);
    }

    public function test_suggest_reply_returns_null_without_provider(): void
    {
        $department = Department::create(['name' => 'Support', 'is_active' => true]);
        $user = User::factory()->create();

        $ticket = Ticket::create([
            'uid' => Ticket::generateUid(),
            'user_id' => $user->id,
            'department_id' => $department->id,
            'subject' => 'Help',
            'body' => 'Body',
            'priority' => 'medium',
            'status' => 'open',
            'source' => 'web',
        ]);

        $this->assertNull(app(TicketService::class)->suggestReply($ticket));
    }

    public function test_admin_suggest_endpoint_returns_422_without_provider(): void
    {
        $department = Department::create(['name' => 'Support', 'is_active' => true]);
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $ticket = Ticket::create([
            'uid' => Ticket::generateUid(),
            'user_id' => $admin->id,
            'department_id' => $department->id,
            'subject' => 'Help',
            'body' => 'Body',
            'priority' => 'medium',
            'status' => 'open',
            'source' => 'web',
        ]);

        $this->actingAs($admin)
            ->postJson('/admin/tickets/'.$ticket->id.'/suggest')
            ->assertStatus(422);
    }
}
