<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Tests\BypassesPairing;
use Tests\TestCase;

class SocialLoginTest extends TestCase
{
    use BypassesPairing, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bypassPairing();
        $this->seed(RolesAndPermissionsSeeder::class);

        config([
            'services.google.client_id' => 'test-id',
            'services.google.client_secret' => 'test-secret',
            'services.google.redirect' => 'http://localhost/login/google/callback',
        ]);
    }

    private function mockGoogle(string $email, string $name = 'Google User'): void
    {
        $socialUser = \Mockery::mock(SocialiteUser::class);
        $socialUser->shouldReceive('getEmail')->andReturn($email);
        $socialUser->shouldReceive('getName')->andReturn($name);
        $socialUser->shouldReceive('getAvatar')->andReturn(null);

        $provider = \Mockery::mock(Provider::class);
        $provider->shouldReceive('stateless')->andReturnSelf();
        $provider->shouldReceive('user')->andReturn($socialUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }

    public function test_disabled_without_config(): void
    {
        config(['services.google.client_id' => null]);

        $this->get('/login/google')->assertNotFound();
        $this->get('/login/google/callback')->assertNotFound();
    }

    public function test_new_google_user_registered_as_customer(): void
    {
        $this->mockGoogle('newg@example.com');

        $this->get('/login/google/callback')->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        $user = User::where('email', 'newg@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('customer'));
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_existing_google_user_logged_in(): void
    {
        $user = User::factory()->create(['email' => 'oldg@example.com']);
        $this->mockGoogle('oldg@example.com', 'Old G');

        $this->get('/login/google/callback')->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
        $this->assertEquals(1, User::where('email', 'oldg@example.com')->count());
    }

    public function test_closed_registration_rejects_new_google_user(): void
    {
        Setting::set('registration.mode', 'closed');
        $this->mockGoogle('closedg@example.com');

        $response = $this->get('/login/google/callback');
        $response->assertRedirect('/login');
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'closedg@example.com']);
    }
}
