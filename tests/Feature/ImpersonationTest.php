<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\BypassesPairing;
use Tests\TestCase;

class ImpersonationTest extends TestCase
{
    use BypassesPairing, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bypassPairing();
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'customer']);
    }

    public function test_admin_can_impersonate_and_stop(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $customer = User::factory()->create();
        $customer->assignRole('customer');

        $this->actingAs($admin)
            ->post('/admin/users/'.$customer->id.'/impersonate')
            ->assertRedirect('/dashboard');

        $this->assertEquals($admin->id, session('impersonator_id'));
        $this->assertEquals($customer->id, auth()->id());

        $this->post('/impersonation/stop')->assertRedirect('/admin/users');

        $this->assertFalse(session()->has('impersonator_id'));
        $this->assertEquals($admin->id, auth()->id());
    }

    public function test_cannot_start_nested_impersonation(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $customer = User::factory()->create();
        $customer->assignRole('customer');
        $other = User::factory()->create();
        $other->assignRole('customer');

        $this->actingAs($admin)->post('/admin/users/'.$customer->id.'/impersonate')->assertRedirect();

        $this->post('/admin/users/'.$other->id.'/impersonate')->assertForbidden();
    }

    public function test_non_admin_cannot_impersonate(): void
    {
        $customer = User::factory()->create();
        $customer->assignRole('customer');
        $victim = User::factory()->create();
        $victim->assignRole('customer');

        $this->actingAs($customer)
            ->post('/admin/users/'.$victim->id.'/impersonate')
            ->assertForbidden();
    }

    public function test_locale_switch_sets_session(): void
    {
        $this->get('/locale/id')->assertRedirect();
        $this->assertEquals('id', session('locale'));

        $this->get('/locale/xx')->assertNotFound();
    }
}
