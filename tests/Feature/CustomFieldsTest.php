<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Ticket;
use App\Models\TicketCustomField;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\BypassesPairing;
use Tests\TestCase;

class CustomFieldsTest extends TestCase
{
    use BypassesPairing, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bypassPairing();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_required_field_enforced_and_stored(): void
    {
        $department = Department::create(['name' => 'Support', 'is_active' => true]);
        $user = User::factory()->create();
        $user->assignRole('customer');

        TicketCustomField::create([
            'department_id' => $department->id,
            'name' => 'order_id',
            'label' => 'Order ID',
            'type' => 'text',
            'is_required' => true,
            'is_active' => true,
        ]);

        // Missing required custom field -> 422.
        $this->actingAs($user)->post('/tickets', [
            'subject' => 'Where is my order',
            'body' => 'Please check.',
            'department_id' => $department->id,
            'priority' => 'medium',
        ])->assertSessionHasErrors('custom_fields.order_id');

        $this->actingAs($user)->post('/tickets', [
            'subject' => 'Where is my order',
            'body' => 'Please check.',
            'department_id' => $department->id,
            'priority' => 'medium',
            'custom_fields' => ['order_id' => 'ORD-123'],
        ])->assertRedirect();

        $ticket = Ticket::where('subject', 'Where is my order')->first();
        $this->assertEquals(['order_id' => 'ORD-123'], $ticket->custom_fields);

        $this->actingAs($user)->get('/tickets/'.$ticket->id)
            ->assertOk()
            ->assertSee('Order ID')
            ->assertSee('ORD-123');
    }

    public function test_fields_endpoint_returns_department_fields(): void
    {
        $department = Department::create(['name' => 'Support', 'is_active' => true]);
        $user = User::factory()->create();

        TicketCustomField::create([
            'department_id' => $department->id,
            'name' => 'plan',
            'label' => 'Plan',
            'type' => 'select',
            'options' => ['basic', 'pro'],
            'is_active' => true,
        ]);
        TicketCustomField::create([
            'name' => 'global_note',
            'label' => 'Global Note',
            'type' => 'text',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->getJson('/tickets-fields?department_id='.$department->id);
        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name');
        $this->assertContains('plan', $names);
        $this->assertNotContains('global_note', $names);
    }

    public function test_admin_can_manage_custom_fields(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)->get('/admin/custom-fields')->assertOk();

        $this->actingAs($admin)->post('/admin/custom-fields', [
            'name' => 'Serial Number',
            'label' => 'Serial Number',
            'type' => 'text',
        ])->assertRedirect();

        $this->assertDatabaseHas('ticket_custom_fields', ['name' => 'serial_number']);
    }
}
