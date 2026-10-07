<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\IncidentController;
use App\Http\Controllers\Admin\ProblemController;
use App\Models\Department;
use App\Models\Incident;
use App\Models\Problem;
use App\Models\Setting;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Tests\BypassesPairing;
use Tests\TestCase;

class SystemsTest extends TestCase
{
    use BypassesPairing, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bypassPairing();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->registerSystemsRoutes();
        $this->registerApiV1Routes();

        // Routes registered at runtime (after boot) need their fluent
        // ->name() calls indexed: RouteServiceProvider only refreshes
        // these lookups once, right after loading the route files.
        Route::getRoutes()->refreshNameLookups();
        Route::getRoutes()->refreshActionLookups();
    }

    private function registerSystemsRoutes(): void
    {
        // NOTE: 'web' must be included — routes/web.php gets the web group
        // automatically at boot (SubstituteBindings for implicit model
        // binding lives there). Runtime-registered routes need it explicit.
        Route::middleware(['web', 'auth', 'staff'])->prefix('admin')->name('admin.')->group(function () {
            Route::resource('incidents', IncidentController::class)->middleware('permission:incidents.manage');
            Route::post('/incidents/{incident}/tickets', [IncidentController::class, 'attachTicket'])
                ->name('incidents.tickets.attach')->middleware('permission:incidents.manage');
            Route::delete('/incidents/{incident}/tickets/{ticket}', [IncidentController::class, 'detachTicket'])
                ->name('incidents.tickets.detach')->middleware('permission:incidents.manage');
            Route::patch('/incidents/{incident}/status', [IncidentController::class, 'transition'])
                ->name('incidents.transition')->middleware('permission:incidents.manage');

            Route::resource('problems', ProblemController::class)->middleware('permission:problems.manage');
            Route::post('/problems/{problem}/tickets', [ProblemController::class, 'attachTicket'])
                ->name('problems.tickets.attach')->middleware('permission:problems.manage');
            Route::delete('/problems/{problem}/tickets/{ticket}', [ProblemController::class, 'detachTicket'])
                ->name('problems.tickets.detach')->middleware('permission:problems.manage');
            Route::post('/problems/{problem}/incidents', [ProblemController::class, 'attachIncident'])
                ->name('problems.incidents.attach')->middleware('permission:problems.manage');
            Route::delete('/problems/{problem}/incidents/{incident}', [ProblemController::class, 'detachIncident'])
                ->name('problems.incidents.detach')->middleware('permission:problems.manage');
            Route::patch('/problems/{problem}/status', [ProblemController::class, 'transition'])
                ->name('problems.transition')->middleware('permission:problems.manage');
        });
    }

    private function registerApiV1Routes(): void
    {
        if (! Route::has('api.v1.tickets.index')) {
            Route::prefix('api')->group(fn () => require base_path('routes/api_v1.php'));
        }
    }

    private function makeStaff(string $role = 'admin'): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function makeTicket(?User $owner = null): Ticket
    {
        $owner ??= User::factory()->create();
        $department = Department::create(['name' => 'Support', 'is_active' => true]);

        return Ticket::create([
            'uid' => Ticket::generateUid(),
            'user_id' => $owner->id,
            'department_id' => $department->id,
            'subject' => 'Systems fixture',
            'body' => 'Body content',
            'priority' => 'medium',
            'status' => 'open',
            'source' => 'web',
        ]);
    }

    public function test_incident_crud_attach_and_transition(): void
    {
        $admin = $this->makeStaff('admin');

        $this->actingAs($admin)->post(route('admin.incidents.store'), [
            'title' => 'DB outage',
            'description' => 'Primary database unreachable',
            'severity' => 'critical',
        ])->assertRedirect();

        $this->assertDatabaseHas('incidents', ['title' => 'DB outage', 'severity' => 'critical']);
        $incident = Incident::where('title', 'DB outage')->firstOrFail();

        $ticket = $this->makeTicket();

        $this->actingAs($admin)->post(route('admin.incidents.tickets.attach', $incident), [
            'ticket_id' => $ticket->id,
        ])->assertRedirect();
        $this->assertDatabaseHas('incident_ticket', ['incident_id' => $incident->id, 'ticket_id' => $ticket->id]);

        // Duplicate attach must not create a second row.
        $this->actingAs($admin)->post(route('admin.incidents.tickets.attach', $incident), [
            'ticket_id' => $ticket->id,
        ])->assertRedirect();
        $this->assertEquals(1, $incident->tickets()->count());

        // Invalid ticket rejected.
        $this->actingAs($admin)->post(route('admin.incidents.tickets.attach', $incident), [
            'ticket_id' => 999999,
        ])->assertSessionHasErrors('ticket_id');

        $this->actingAs($admin)->patch(route('admin.incidents.transition', $incident), [
            'status' => 'resolved',
        ])->assertRedirect();
        $incident->refresh();
        $this->assertSame('resolved', $incident->status);
        $this->assertNotNull($incident->resolved_at);

        $this->actingAs($admin)->patch(route('admin.incidents.transition', $incident), [
            'status' => 'investigating_reopen',
        ])->assertRedirect();
        $incident->refresh();
        $this->assertSame('investigating', $incident->status);

        $this->actingAs($admin)->get(route('admin.incidents.show', $incident))->assertOk();
        $this->assertDatabaseHas('activity_logs', ['target_type' => Incident::class, 'target_id' => $incident->id]);
    }

    public function test_problem_crud_attach_incident_and_transition(): void
    {
        $admin = $this->makeStaff('admin');

        $this->actingAs($admin)->post(route('admin.problems.store'), [
            'title' => 'Recurring login failure',
            'symptoms' => 'Users cannot log in intermittently',
        ])->assertRedirect();

        $problem = Problem::where('title', 'Recurring login failure')->firstOrFail();

        $ticket = $this->makeTicket();
        $incident = Incident::create([
            'title' => 'Auth incident', 'description' => 'Auth down', 'severity' => 'high',
        ]);

        $this->actingAs($admin)->post(route('admin.problems.tickets.attach', $problem), [
            'ticket_id' => $ticket->id,
        ])->assertRedirect();
        $this->assertDatabaseHas('problem_ticket', ['problem_id' => $problem->id, 'ticket_id' => $ticket->id]);

        $this->actingAs($admin)->post(route('admin.problems.tickets.attach', $problem), [
            'ticket_id' => 999999,
        ])->assertSessionHasErrors('ticket_id');

        $this->actingAs($admin)->post(route('admin.problems.incidents.attach', $problem), [
            'incident_id' => $incident->id,
        ])->assertRedirect();
        $this->assertDatabaseHas('problem_incident', ['problem_id' => $problem->id, 'incident_id' => $incident->id]);

        $this->actingAs($admin)->post(route('admin.problems.incidents.attach', $problem), [
            'incident_id' => $incident->id,
        ])->assertRedirect();
        $this->assertEquals(1, $problem->incidents()->count());

        $this->actingAs($admin)->patch(route('admin.problems.transition', $problem), [
            'status' => 'resolved',
        ])->assertRedirect();
        $this->assertSame('resolved', $problem->fresh()->status);

        $this->actingAs($admin)->get(route('admin.problems.show', $problem))->assertOk();
    }

    public function test_agent_without_permission_gets_403(): void
    {
        $agent = $this->makeStaff('agent');

        $this->actingAs($agent)->get(route('admin.incidents.index'))->assertForbidden();
        $this->actingAs($agent)->get(route('admin.problems.index'))->assertForbidden();
    }

    public function test_api_v1_customer_isolation(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $ticket = $this->makeTicket($owner);
        $this->makeTicket($other);

        $response = $this->actingAs($owner, 'sanctum')->getJson('/api/v1/tickets');
        $response->assertOk();

        $uids = collect($response->json('data.data'))->pluck('uid');
        $this->assertContains($ticket->uid, $uids);
        $this->assertCount(1, $uids);
    }

    public function test_registration_closed_mode_returns_403(): void
    {
        Setting::set('registration.mode', 'closed');

        $this->get('/register')->assertForbidden();
        $this->post('/register', [
            'name' => 'Blocked',
            'email' => 'blocked@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertForbidden();
    }

    public function test_registration_approval_creates_inactive_user_and_login_blocked(): void
    {
        Setting::set('registration.mode', 'approval');
        $admin = $this->makeStaff('admin');

        $this->post('/register', [
            'name' => 'Pending User',
            'email' => 'pending@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect('/login');

        $this->assertDatabaseHas('users', ['email' => 'pending@example.test', 'is_active' => false]);
        $this->assertDatabaseHas('notifications', ['notifiable_id' => $admin->id]);
        $this->assertGuest();

        $this->post('/login', [
            'email' => 'pending@example.test',
            'password' => 'password123',
        ])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_setup_check_exits_zero(): void
    {
        $this->artisan('setup:check')->assertExitCode(0);
    }

    public function test_indonesian_validation_messages_loadable(): void
    {
        app()->setLocale('id');

        $messages = Validator::make(['name' => null], ['name' => 'required'])->messages()->toArray();

        $this->assertStringContainsString('wajib diisi', $messages['name'][0]);
    }
}
