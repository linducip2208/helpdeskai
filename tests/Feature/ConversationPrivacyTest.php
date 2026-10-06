<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Department;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\BypassesPairing;
use Tests\TestCase;

class ConversationPrivacyTest extends TestCase
{
    use BypassesPairing, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bypassPairing();
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'customer']);
    }

    public function test_user_cannot_view_or_message_other_conversation(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $conversation = Conversation::create([
            'user_id' => $owner->id,
            'status' => 'open',
            'last_message_at' => now(),
        ]);

        $this->actingAs($intruder)->get('/conversations/'.$conversation->id)->assertForbidden();
        $this->actingAs($intruder)
            ->post('/conversations/'.$conversation->id.'/message', ['body' => 'Hi'])
            ->assertForbidden();

        $this->assertDatabaseCount('conversation_messages', 0);
    }

    public function test_api_conversation_index_does_not_leak_others(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        Conversation::create(['user_id' => $owner->id, 'status' => 'open', 'last_message_at' => now()]);

        $response = $this->actingAs($intruder, 'sanctum')->getJson('/api/conversations');
        $response->assertOk();
        $this->assertCount(0, $response->json('data.data'));
    }

    public function test_api_conversation_show_requires_participation(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $conversation = Conversation::create(['user_id' => $owner->id, 'status' => 'open', 'last_message_at' => now()]);

        $this->actingAs($intruder, 'sanctum')->getJson('/api/conversations/'.$conversation->id)->assertForbidden();
        $this->actingAs($owner, 'sanctum')->getJson('/api/conversations/'.$conversation->id)->assertOk();
    }

    public function test_api_ticket_show_hides_internal_replies_from_customer(): void
    {
        $department = Department::create(['name' => 'Support', 'is_active' => true]);
        $owner = User::factory()->create();
        $owner->assignRole('customer');
        $staff = User::factory()->create();
        $staff->assignRole('admin');

        $ticket = Ticket::create([
            'uid' => Ticket::generateUid(),
            'user_id' => $owner->id,
            'department_id' => $department->id,
            'subject' => 'Help',
            'body' => 'Body',
            'priority' => 'medium',
            'status' => 'open',
            'source' => 'web',
        ]);

        TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => $staff->id,
            'body' => 'SECRET-INTERNAL-NOTE-XYZ',
            'is_internal' => true,
            'source' => 'web',
        ]);

        $customerResponse = $this->actingAs($owner, 'sanctum')->getJson('/api/tickets/'.$ticket->id);
        $customerResponse->assertOk();
        $bodies = collect($customerResponse->json('data.replies'))->pluck('body');
        $this->assertNotContains('SECRET-INTERNAL-NOTE-XYZ', $bodies);

        $staffResponse = $this->actingAs($staff, 'sanctum')->getJson('/api/tickets/'.$ticket->id);
        $staffResponse->assertOk();
        $staffBodies = collect($staffResponse->json('data.replies'))->pluck('body');
        $this->assertContains('SECRET-INTERNAL-NOTE-XYZ', $staffBodies);
    }

    public function test_web_ticket_page_hides_internal_replies_from_customer(): void
    {
        $department = Department::create(['name' => 'Support', 'is_active' => true]);
        $owner = User::factory()->create();
        $staff = User::factory()->create();

        $ticket = Ticket::create([
            'uid' => Ticket::generateUid(),
            'user_id' => $owner->id,
            'department_id' => $department->id,
            'subject' => 'Help',
            'body' => 'Body',
            'priority' => 'medium',
            'status' => 'open',
            'source' => 'web',
        ]);

        TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => $staff->id,
            'body' => 'SECRET-WEB-INTERNAL-ABC',
            'is_internal' => true,
            'source' => 'web',
        ]);

        $this->actingAs($owner)->get('/tickets/'.$ticket->id)
            ->assertOk()
            ->assertDontSee('SECRET-WEB-INTERNAL-ABC');
    }
}
