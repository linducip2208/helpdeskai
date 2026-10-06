<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\BypassesPairing;
use Tests\TestCase;

class AttachmentAuthorizationTest extends TestCase
{
    use BypassesPairing, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bypassPairing();
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'customer']);
        Storage::fake('local');
    }

    private function makeFixtures(): array
    {
        $department = Department::create(['name' => 'Support', 'is_active' => true]);
        $owner = User::factory()->create();
        $owner->assignRole('customer');
        $intruder = User::factory()->create();
        $intruder->assignRole('customer');
        $admin = User::factory()->create();
        $admin->assignRole('admin');

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

        Storage::disk('local')->put('attachments/'.$ticket->id.'/file.txt', 'hello');

        $attachment = TicketAttachment::create([
            'ticket_id' => $ticket->id,
            'reply_id' => null,
            'user_id' => $owner->id,
            'filename' => 'file.txt',
            'original_name' => 'notes.txt',
            'mime_type' => 'text/plain',
            'size' => 5,
            'path' => 'attachments/'.$ticket->id.'/file.txt',
            'is_internal' => false,
        ]);

        return [$owner, $intruder, $admin, $attachment];
    }

    public function test_owner_can_download_own_attachment(): void
    {
        [$owner, $intruder, $admin, $attachment] = $this->makeFixtures();

        $this->actingAs($owner)->get('/attachments/'.$attachment->id.'/download')->assertOk();
    }

    public function test_other_customer_cannot_download_attachment(): void
    {
        [$owner, $intruder, $admin, $attachment] = $this->makeFixtures();

        $this->actingAs($intruder)->get('/attachments/'.$attachment->id.'/download')->assertForbidden();
    }

    public function test_customer_cannot_download_internal_attachment(): void
    {
        [$owner, $intruder, $admin, $attachment] = $this->makeFixtures();
        $attachment->update(['is_internal' => true]);

        $this->actingAs($owner)->get('/attachments/'.$attachment->id.'/download')->assertForbidden();
    }

    public function test_guest_cannot_download_attachment(): void
    {
        [$owner, $intruder, $admin, $attachment] = $this->makeFixtures();

        $this->get('/attachments/'.$attachment->id.'/download')->assertRedirect('/login');
    }

    public function test_admin_can_download_via_admin_route(): void
    {
        [$owner, $intruder, $admin, $attachment] = $this->makeFixtures();
        $attachment->update(['is_internal' => true]);

        $this->actingAs($admin)->get('/admin/attachments/'.$attachment->id.'/download')->assertOk();
    }
}
