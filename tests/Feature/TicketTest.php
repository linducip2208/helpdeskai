<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Department;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\BypassesPairing;
use Tests\TestCase;

class TicketTest extends TestCase
{
    use RefreshDatabase, BypassesPairing;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bypassPairing();
    }

    private function makeDepartmentAndCategory(): array
    {
        $department = Department::create(['name' => 'Support', 'is_active' => true]);
        $category = Category::create([
            'name' => 'General',
            'slug' => 'general',
            'department_id' => $department->id,
            'is_active' => true,
        ]);

        return [$department, $category];
    }

    public function test_user_can_create_ticket(): void
    {
        [$department, $category] = $this->makeDepartmentAndCategory();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/tickets', [
            'subject' => 'My printer is broken',
            'body' => 'It will not turn on.',
            'department_id' => $department->id,
            'category_id' => $category->id,
            'priority' => 'high',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('tickets', [
            'subject' => 'My printer is broken',
            'user_id' => $user->id,
            'priority' => 'high',
        ]);
    }

    public function test_user_sees_only_their_own_tickets(): void
    {
        [$department, $category] = $this->makeDepartmentAndCategory();
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $ticket = Ticket::create([
            'uid' => Ticket::generateUid(),
            'user_id' => $owner->id,
            'department_id' => $department->id,
            'category_id' => $category->id,
            'subject' => 'Private ticket',
            'body' => 'Secret content',
            'priority' => 'medium',
            'status' => 'open',
            'source' => 'web',
        ]);

        $this->actingAs($owner)->get('/tickets/' . $ticket->id)->assertOk();

        $this->actingAs($other)->get('/tickets/' . $ticket->id)->assertForbidden();
    }

    public function test_guest_cannot_access_tickets(): void
    {
        $this->get('/tickets')->assertRedirect('/login');
    }
}
