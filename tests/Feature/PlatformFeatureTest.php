<?php

namespace Tests\Feature;

use App\Mail\TicketReplyMail;
use App\Models\Department;
use App\Models\Setting;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\BypassesPairing;
use Tests\TestCase;

class PlatformFeatureTest extends TestCase
{
    use BypassesPairing, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bypassPairing();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_health_and_ready_endpoints(): void
    {
        $this->get('/health')->assertOk()->assertJsonPath('status', 'ok');
        $this->get('/ready')->assertOk()->assertJsonPath('ready', true);
        $this->get('/support')->assertOk()->assertSee('081296052010');
    }

    public function test_security_headers_present(): void
    {
        $response = $this->get('/');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
    }

    public function test_system_health_restricted_to_settings_managers(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $agent = User::factory()->create();
        $agent->assignRole('agent');

        $this->actingAs($admin)->get('/admin/system-health')->assertOk()->assertSee('Database');
        $this->actingAs($agent)->get('/admin/system-health')->assertForbidden();
    }

    public function test_admin_search_finds_ticket_and_respects_permissions(): void
    {
        $department = Department::create(['name' => 'Support', 'is_active' => true]);
        $owner = User::factory()->create();
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $customer = User::factory()->create();
        $customer->assignRole('customer');

        $ticket = Ticket::create([
            'uid' => Ticket::generateUid(),
            'user_id' => $owner->id,
            'department_id' => $department->id,
            'subject' => 'Unicorn printer jam ZZZ9',
            'body' => 'Body',
            'priority' => 'medium',
            'status' => 'open',
            'source' => 'web',
        ]);

        $this->actingAs($admin)->get('/admin/search?q=Unicorn')
            ->assertOk()
            ->assertSee($ticket->uid);

        $this->actingAs($customer)->get('/admin/search?q=Unicorn')->assertForbidden();
    }

    public function test_agent_reply_queues_customer_email(): void
    {
        Mail::fake();

        $department = Department::create(['name' => 'Support', 'is_active' => true]);
        $owner = User::factory()->create();
        $agent = User::factory()->create();
        $agent->assignRole('agent');

        $ticket = Ticket::create([
            'uid' => Ticket::generateUid(),
            'user_id' => $owner->id,
            'department_id' => $department->id,
            'subject' => 'Mail me',
            'body' => 'Body',
            'priority' => 'medium',
            'status' => 'open',
            'source' => 'web',
        ]);

        app(TicketService::class)->addReply($ticket, $agent->id, 'Here is the fix.', false);

        Mail::assertQueued(TicketReplyMail::class, fn ($mail) => $mail->hasTo($owner->email));
    }

    public function test_autoclose_closes_old_resolved_tickets(): void
    {
        $department = Department::create(['name' => 'Support', 'is_active' => true]);
        $owner = User::factory()->create();
        Setting::set('auto_close_days', 7);

        $old = Ticket::create([
            'uid' => Ticket::generateUid(),
            'user_id' => $owner->id,
            'department_id' => $department->id,
            'subject' => 'Old resolved',
            'body' => 'Body',
            'priority' => 'medium',
            'status' => 'resolved',
            'resolved_at' => now()->subDays(10),
            'source' => 'web',
        ]);

        $this->artisan('tickets:autoclose')->assertSuccessful();
        $this->assertEquals('closed', $old->fresh()->status->value);
    }

    public function test_impersonation_blocked_for_equal_or_higher_rank(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $peer = User::factory()->create();
        $peer->assignRole('admin');
        $super = User::factory()->create();
        $super->assignRole('super-admin');

        $this->actingAs($admin)->post('/admin/users/'.$peer->id.'/impersonate')->assertForbidden();
        $this->actingAs($super)->post('/admin/users/'.$peer->id.'/impersonate')->assertRedirect();
    }
}
