<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Ticket;
use App\Models\User;
use App\Services\EmailPipingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\BypassesPairing;
use Tests\TestCase;

class EmailPipingTest extends TestCase
{
    use BypassesPairing, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bypassPairing();
        Role::create(['name' => 'customer']);
    }

    public function test_inbound_email_creates_customer_and_ticket(): void
    {
        Department::create(['name' => 'Support', 'is_active' => true]);

        $ticket = app(EmailPipingService::class)->processEmail([
            'from' => 'newuser@example.com',
            'from_name' => 'New User',
            'subject' => 'Cannot login',
            'text' => 'Please help me login.',
            'message_id' => 'msg-1',
        ]);

        $this->assertNotNull($ticket);
        $this->assertDatabaseHas('users', ['email' => 'newuser@example.com']);

        $user = User::where('email', 'newuser@example.com')->first();
        $this->assertTrue($user->hasRole('customer'));

        $this->assertDatabaseHas('tickets', ['id' => $ticket->id, 'source' => 'email']);
        $this->assertDatabaseHas('email_logs', ['message_id' => 'msg-1', 'status' => 'parsed']);
    }

    public function test_reply_spoof_from_non_participant_does_not_attach(): void
    {
        $department = Department::create(['name' => 'Support', 'is_active' => true]);
        $owner = User::factory()->create();

        $ticket = Ticket::create([
            'uid' => Ticket::generateUid(),
            'user_id' => $owner->id,
            'department_id' => $department->id,
            'subject' => 'Owner ticket',
            'body' => 'Body',
            'priority' => 'medium',
            'status' => 'open',
            'source' => 'web',
        ]);

        $result = app(EmailPipingService::class)->processEmail([
            'from' => 'attacker@example.com',
            'subject' => "Re: [{$ticket->uid}] hacked",
            'text' => 'I am not the owner.',
            'message_id' => 'msg-spoof',
        ]);

        $this->assertNotNull($result);
        $this->assertNotEquals($ticket->id, $result->id);
        $this->assertDatabaseMissing('ticket_replies', ['ticket_id' => $ticket->id, 'body' => 'I am not the owner.']);
    }

    public function test_invalid_sender_is_rejected_and_logged(): void
    {
        $result = app(EmailPipingService::class)->processEmail([
            'from' => 'not-an-email',
            'subject' => 'Hi',
            'text' => 'Body',
        ]);

        $this->assertNull($result);
        $this->assertDatabaseHas('email_logs', ['from_email' => 'not-an-email', 'status' => 'failed']);
    }
}
