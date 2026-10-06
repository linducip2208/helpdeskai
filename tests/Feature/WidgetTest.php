<?php

namespace Tests\Feature;

use App\Models\Department;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\BypassesPairing;
use Tests\TestCase;

class WidgetTest extends TestCase
{
    use BypassesPairing, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bypassPairing();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_widget_creates_ticket_for_new_visitor(): void
    {
        Department::create(['name' => 'Support', 'is_active' => true]);

        $response = $this->postJson('/widget/tickets', [
            'name' => 'Visitor',
            'email' => 'visitor@example.com',
            'subject' => 'Pricing question',
            'message' => 'How much does it cost?',
        ]);

        $response->assertCreated()->assertJsonPath('success', true);

        $this->assertDatabaseHas('tickets', ['subject' => 'Pricing question', 'source' => 'chat']);
        $this->assertDatabaseHas('users', ['email' => 'visitor@example.com']);
    }

    public function test_widget_rejects_honeypot_and_bad_input(): void
    {
        Department::create(['name' => 'Support', 'is_active' => true]);

        $this->postJson('/widget/tickets', [
            'name' => 'Bot',
            'email' => 'bot@example.com',
            'subject' => 'Spam',
            'message' => 'Spam body',
            'website' => 'http://spam.example',
        ])->assertUnprocessable();

        $this->postJson('/widget/tickets', [
            'name' => 'Visitor',
            'email' => 'not-an-email',
            'subject' => 'Hi',
            'message' => 'Hello',
        ])->assertUnprocessable();

        $this->assertDatabaseCount('tickets', 0);
    }

    public function test_widget_is_rate_limited(): void
    {
        Department::create(['name' => 'Support', 'is_active' => true]);

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/widget/tickets', [
                'name' => 'Visitor',
                'email' => "visitor{$i}@example.com",
                'subject' => "Q{$i}",
                'message' => 'Hello',
            ])->assertCreated();
        }

        $this->postJson('/widget/tickets', [
            'name' => 'Visitor',
            'email' => 'visitor5@example.com',
            'subject' => 'Q5',
            'message' => 'Hello',
        ])->assertStatus(429);
    }
}
