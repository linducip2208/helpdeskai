<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\BypassesPairing;
use Tests\TestCase;

class GranularRbacTest extends TestCase
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

    private function manager(): User
    {
        $manager = User::factory()->create();
        $manager->assignRole('manager');

        return $manager;
    }

    private function customer(): User
    {
        $customer = User::factory()->create();
        $customer->assignRole('customer');

        return $customer;
    }

    private function ticket(User $owner): Ticket
    {
        $department = Department::create(['name' => 'Support', 'is_active' => true]);

        return Ticket::create([
            'uid' => Ticket::generateUid(),
            'user_id' => $owner->id,
            'department_id' => $department->id,
            'subject' => 'RBAC ticket',
            'body' => 'Body',
            'priority' => 'medium',
            'status' => 'open',
            'source' => 'web',
        ]);
    }

    public function test_agent_can_view_tickets_but_cannot_delete(): void
    {
        $agent = $this->agent();
        $ticket = $this->ticket($this->customer());

        $this->actingAs($agent)->get('/admin/tickets')->assertOk();
        $this->actingAs($agent)->get('/admin/tickets/'.$ticket->id)->assertOk();
        $this->actingAs($agent)->delete('/admin/tickets/'.$ticket->id)->assertForbidden();
        $this->assertDatabaseHas('tickets', ['id' => $ticket->id]);
    }

    public function test_agent_cannot_bulk_delete(): void
    {
        $agent = $this->agent();
        $ticket = $this->ticket($this->customer());

        $this->actingAs($agent)->post('/admin/tickets/bulk', [
            'ids' => [$ticket->id],
            'action' => 'delete',
        ])->assertForbidden();
        $this->assertDatabaseHas('tickets', ['id' => $ticket->id]);
    }

    public function test_agent_cannot_open_settings_or_users_but_can_view_analytics(): void
    {
        $agent = $this->agent();

        $this->actingAs($agent)->get('/admin/settings')->assertForbidden();
        $this->actingAs($agent)->get('/admin/users')->assertForbidden();
        $this->actingAs($agent)->get('/admin/api-keys')->assertForbidden();
        $this->actingAs($agent)->get('/admin/analytics')->assertOk();
    }

    public function test_manager_can_view_reports_but_not_settings_or_impersonate(): void
    {
        $manager = $this->manager();
        $victim = $this->customer();

        $this->actingAs($manager)->get('/admin/analytics')->assertOk();
        $this->actingAs($manager)->get('/admin/activity-log')->assertOk();
        $this->actingAs($manager)->get('/admin/settings')->assertForbidden();
        $this->actingAs($manager)->get('/admin/api-keys')->assertForbidden();
        $this->actingAs($manager)->post('/admin/users/'.$victim->id.'/impersonate')->assertForbidden();
    }

    public function test_customer_is_blocked_from_admin_area(): void
    {
        $customer = $this->customer();

        $this->actingAs($customer)->get('/admin')->assertForbidden();
        $this->actingAs($customer)->get('/admin/tickets')->assertForbidden();
        $this->actingAs($customer)->get('/admin/analytics')->assertForbidden();
    }

    public function test_sidebar_hides_unauthorized_menus(): void
    {
        $agent = $this->agent();
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $agentPage = $this->actingAs($agent)->get('/admin')->assertOk();
        $agentPage->assertDontSee('API Keys');
        $agentPage->assertDontSee('General Settings');
        $agentPage->assertSee('Tickets');

        $adminPage = $this->actingAs($admin)->get('/admin')->assertOk();
        $adminPage->assertSee('API Keys');
    }
}
