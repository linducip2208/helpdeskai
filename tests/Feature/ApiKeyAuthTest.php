<?php

namespace Tests\Feature;

use App\Models\ApiKey;
use App\Models\Department;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\BypassesPairing;
use Tests\TestCase;

class ApiKeyAuthTest extends TestCase
{
    use BypassesPairing, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bypassPairing();
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'customer']);
    }

    private function makeKey(User $user, string $permissions = 'full', bool $active = true): string
    {
        $plain = ApiKey::generateKey();

        ApiKey::create([
            'user_id' => $user->id,
            'name' => 'Test key',
            'permissions' => $permissions,
            'key' => hash('sha256', $plain),
            'is_active' => $active,
        ]);

        return $plain;
    }

    public function test_api_key_authenticates_and_scopes_customer(): void
    {
        $department = Department::create(['name' => 'Support', 'is_active' => true]);
        $owner = User::factory()->create();
        $owner->assignRole('customer');

        $ticket = Ticket::create([
            'uid' => Ticket::generateUid(),
            'user_id' => $owner->id,
            'department_id' => $department->id,
            'subject' => 'Key ticket',
            'body' => 'Body',
            'priority' => 'medium',
            'status' => 'open',
            'source' => 'web',
        ]);

        $plain = $this->makeKey($owner, 'read');

        $response = $this->withHeader('X-API-Key', $plain)->getJson('/api/tickets');
        $response->assertOk();
        $uids = collect($response->json('data.data'))->pluck('uid');
        $this->assertContains($ticket->uid, $uids);
    }

    public function test_invalid_and_revoked_keys_rejected(): void
    {
        $user = User::factory()->create();

        $this->withHeader('X-API-Key', 'invalid-key')->getJson('/api/tickets')->assertUnauthorized();

        $plain = $this->makeKey($user, 'full', false);
        $this->withHeader('X-API-Key', $plain)->getJson('/api/tickets')->assertUnauthorized();
    }

    public function test_read_only_key_cannot_write(): void
    {
        $user = User::factory()->create();
        $user->assignRole('customer');
        $plain = $this->makeKey($user, 'read');

        $this->withHeader('X-API-Key', $plain)
            ->postJson('/api/tickets', ['subject' => 'x', 'body' => 'y', 'department_id' => 1])
            ->assertForbidden();
    }

    public function test_expired_key_rejected(): void
    {
        $user = User::factory()->create();
        $plain = ApiKey::generateKey();

        ApiKey::create([
            'user_id' => $user->id,
            'name' => 'Expired',
            'permissions' => 'full',
            'key' => hash('sha256', $plain),
            'expires_at' => now()->subDay(),
        ]);

        $this->withHeader('X-API-Key', $plain)->getJson('/api/tickets')->assertStatus(401);
    }
}
