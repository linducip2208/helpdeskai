<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\BypassesPairing;
use Tests\TestCase;

class IdempotencyAndWorkTest extends TestCase
{
    use BypassesPairing, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bypassPairing();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_repeated_api_ticket_create_returns_stored_response(): void
    {
        $department = Department::create(['name' => 'Support', 'is_active' => true]);
        $customer = User::factory()->create();
        $customer->assignRole('customer');

        $payload = [
            'subject' => 'Idempotent ticket',
            'body' => 'Body',
            'department_id' => $department->id,
            'priority' => 'medium',
        ];

        $first = $this->actingAs($customer, 'sanctum')
            ->withHeader('Idempotency-Key', 'test-key-12345')
            ->postJson('/api/tickets', $payload);
        $first->assertCreated();

        $second = $this->actingAs($customer, 'sanctum')
            ->withHeader('Idempotency-Key', 'test-key-12345')
            ->postJson('/api/tickets', $payload);
        $second->assertCreated()->assertJsonPath('idempotent_replay', true);

        $this->assertEquals(
            $first->json('data.uid'),
            $second->json('data.uid')
        );
        $this->assertEquals(1, Ticket::where('subject', 'Idempotent ticket')->count());
        $this->assertDatabaseHas('idempotency_keys', ['key' => 'test-key-12345', 'response_code' => 201]);
    }

    public function test_my_work_page_renders_for_agent(): void
    {
        $agent = User::factory()->create();
        $agent->assignRole('agent');

        $this->actingAs($agent)->get('/admin/my-work')
            ->assertOk()
            ->assertSee('My Tickets');
    }

    public function test_dashboard_shows_sla_sections(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)->get('/admin')
            ->assertOk()
            ->assertSee('SLA At Risk')
            ->assertSee('SLA Breached');
    }
}
