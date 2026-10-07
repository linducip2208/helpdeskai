<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\NotificationPreferenceController;
use App\Http\Controllers\Admin\OrganizationController;
use App\Http\Controllers\Admin\SavedViewController;
use App\Http\Controllers\Admin\SupportInboxController;
use App\Http\Controllers\Admin\TeamController;
use App\Models\Category;
use App\Models\Department;
use App\Models\NotificationPreference;
use App\Models\Organization;
use App\Models\SavedView;
use App\Models\Setting;
use App\Models\Team;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\BypassesPairing;
use Tests\TestCase;

class WorkspaceTest extends TestCase
{
    use BypassesPairing, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bypassPairing();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->registerWorkspaceTestRoutes();
    }

    private function registerWorkspaceTestRoutes(): void
    {
        Route::middleware(['web', 'auth'])->group(function () {
            Route::get('/_ws/inbox', [SupportInboxController::class, 'index'])->name('admin.support.inbox');
            Route::get('/_ws/customers/{customer}', [CustomerController::class, 'show'])->name('admin.customers.show');
            Route::put('/_ws/customers/{customer}', [CustomerController::class, 'update'])->name('admin.customers.update');

            Route::get('/_ws/teams', [TeamController::class, 'index'])->name('admin.teams.index');
            Route::get('/_ws/teams/create', [TeamController::class, 'create'])->name('admin.teams.create');
            Route::post('/_ws/teams', [TeamController::class, 'store'])->name('admin.teams.store');
            Route::get('/_ws/teams/{team}/edit', [TeamController::class, 'edit'])->name('admin.teams.edit');
            Route::put('/_ws/teams/{team}', [TeamController::class, 'update'])->name('admin.teams.update');
            Route::delete('/_ws/teams/{team}', [TeamController::class, 'destroy'])->name('admin.teams.destroy');

            Route::get('/_ws/organizations', [OrganizationController::class, 'index'])->name('admin.organizations.index');
            Route::get('/_ws/organizations/create', [OrganizationController::class, 'create'])->name('admin.organizations.create');
            Route::post('/_ws/organizations', [OrganizationController::class, 'store'])->name('admin.organizations.store');
            Route::get('/_ws/organizations/{organization}', [OrganizationController::class, 'show'])->name('admin.organizations.show');
            Route::get('/_ws/organizations/{organization}/edit', [OrganizationController::class, 'edit'])->name('admin.organizations.edit');
            Route::put('/_ws/organizations/{organization}', [OrganizationController::class, 'update'])->name('admin.organizations.update');
            Route::delete('/_ws/organizations/{organization}', [OrganizationController::class, 'destroy'])->name('admin.organizations.destroy');

            Route::get('/_ws/notifications', [NotificationPreferenceController::class, 'index'])->name('profile.notifications.index');
            Route::put('/_ws/notifications', [NotificationPreferenceController::class, 'update'])->name('profile.notifications.update');

            Route::get('/_ws/saved-views', [SavedViewController::class, 'index'])->name('admin.saved-views.index');
            Route::post('/_ws/saved-views', [SavedViewController::class, 'store'])->name('admin.saved-views.store');
            Route::delete('/_ws/saved-views/{savedView}', [SavedViewController::class, 'destroy'])->name('admin.saved-views.destroy');

            Route::get('/_ws/tickets/create', fn () => 'ok')->name('admin.tickets.create');
            Route::get('/_ws/tickets/{ticket}', fn (Ticket $ticket) => 'ok')->name('admin.tickets.show');
            Route::post('/_ws/tickets/bulk', fn () => 'ok')->name('admin.tickets.bulk');
            Route::get('/_ws/users', fn () => 'ok')->name('admin.users.index');
        });

        Route::get('/profile/notifications', [NotificationPreferenceController::class, 'index'])
            ->middleware(['web', 'auth'])->name('profile.notifications');

        Route::getRoutes()->refreshNameLookups();
        Route::getRoutes()->refreshActionLookups();
    }

    private function makeUser(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);
        $user->forceFill(['role' => $role])->saveQuietly();

        return $user->fresh();
    }

    private function makeDepartmentAndCategory(): array
    {
        $suffix = uniqid();
        $department = Department::create(['name' => 'Support '.$suffix, 'is_active' => true]);
        $category = Category::create([
            'name' => 'General '.$suffix,
            'slug' => 'general-'.$suffix,
            'department_id' => $department->id,
            'is_active' => true,
        ]);

        return [$department, $category];
    }

    private function makeTicket(User $customer, array $overrides = []): Ticket
    {
        [$department, $category] = $this->makeDepartmentAndCategory();

        return Ticket::create(array_merge([
            'uid' => Ticket::generateUid(),
            'user_id' => $customer->id,
            'department_id' => $department->id,
            'category_id' => $category->id,
            'subject' => 'Test ticket',
            'body' => 'Test body',
            'priority' => 'medium',
            'status' => 'open',
            'source' => 'web',
        ], $overrides));
    }

    public function test_support_inbox_returns_200_with_queue_counts(): void
    {
        $agent = $this->makeUser('agent');
        $customer = $this->makeUser('customer');

        $this->makeTicket($customer, ['assigned_to' => $agent->id, 'status' => 'open']);
        $this->makeTicket($customer, ['assigned_to' => null, 'status' => 'open']);
        $this->makeTicket($customer, ['assigned_to' => null, 'status' => 'open', 'priority' => 'urgent']);

        $response = $this->actingAs($agent)->get('/_ws/inbox?queue=open');

        $response->assertOk();
        $queues = $response->viewData('queues');
        $this->assertNotEmpty($queues);
        $this->assertSame(1, $queues['my']['count']);
        $this->assertSame(3, $queues['open']['count']);
        $this->assertSame(2, $queues['unassigned']['count']);
        $this->assertSame(1, $queues['urgent']['count']);
    }

    public function test_support_inbox_forbidden_without_permission(): void
    {
        $customer = $this->makeUser('customer');

        $this->actingAs($customer)->get('/_ws/inbox')->assertForbidden();
    }

    public function test_customer_360_visible_to_staff_but_forbidden_for_customers(): void
    {
        $agent = $this->makeUser('agent');
        $customer = $this->makeUser('customer');
        $otherCustomer = $this->makeUser('customer');

        $this->makeTicket($customer, ['status' => 'resolved']);

        $this->actingAs($agent)->get('/_ws/customers/'.$customer->id)
            ->assertOk()
            ->assertSee($customer->name);

        $this->actingAs($otherCustomer)->get('/_ws/customers/'.$customer->id)->assertForbidden();
        $this->actingAs($customer)->get('/_ws/customers/'.$customer->id)->assertForbidden();
    }

    public function test_customer_internal_notes_update_requires_manage_users(): void
    {
        $admin = $this->makeUser('admin');
        $agent = $this->makeUser('agent');
        $customer = $this->makeUser('customer');

        $this->actingAs($admin)->put('/_ws/customers/'.$customer->id, [
            'internal_notes' => 'VIP handling required',
            'vip' => true,
        ])->assertRedirect();

        $this->assertSame('VIP handling required', $customer->fresh()->internal_notes);
        $this->assertTrue((bool) $customer->fresh()->vip);

        $this->actingAs($agent)->put('/_ws/customers/'.$customer->id, [
            'internal_notes' => 'should not save',
        ])->assertForbidden();
    }

    public function test_team_crud(): void
    {
        $admin = $this->makeUser('admin');
        $agent = $this->makeUser('agent');

        $this->actingAs($agent)->get('/_ws/teams')->assertForbidden();

        $this->actingAs($admin)->post('/_ws/teams', [
            'name' => 'Support L1',
            'description' => 'First line',
            'is_active' => true,
            'members' => [$agent->id],
        ])->assertRedirect();

        $team = Team::where('name', 'Support L1')->firstOrFail();
        $this->assertTrue($team->members()->where('user_id', $agent->id)->exists());

        $this->actingAs($admin)->get('/_ws/teams')->assertOk()->assertSee('Support L1');

        $this->actingAs($admin)->put('/_ws/teams/'.$team->id, [
            'name' => 'Support L1 Renamed',
            'members' => [],
        ])->assertRedirect();
        $this->assertSame('Support L1 Renamed', $team->fresh()->name);
        $this->assertSame(0, $team->fresh()->members()->count());

        $this->actingAs($admin)->delete('/_ws/teams/'.$team->id)->assertRedirect();
        $this->assertDatabaseMissing('teams', ['id' => $team->id]);
    }

    public function test_organization_crud(): void
    {
        $admin = $this->makeUser('admin');
        $agent = $this->makeUser('agent');

        $this->actingAs($agent)->get('/_ws/organizations')->assertForbidden();

        $this->actingAs($admin)->post('/_ws/organizations', [
            'name' => 'Acme Corp',
            'domain' => 'acme.test',
            'email' => 'info@acme.test',
        ])->assertRedirect();

        $org = Organization::where('name', 'Acme Corp')->firstOrFail();

        $member = $this->makeUser('customer');
        $member->forceFill(['organization_id' => $org->id])->save();

        $this->actingAs($admin)->get('/_ws/organizations/'.$org->id)
            ->assertOk()
            ->assertSee('Acme Corp')
            ->assertSee($member->name);

        $this->actingAs($admin)->put('/_ws/organizations/'.$org->id, [
            'name' => 'Acme Corp Updated',
        ])->assertRedirect();

        $this->actingAs($admin)->delete('/_ws/organizations/'.$org->id)->assertRedirect();
        $this->assertDatabaseMissing('organizations', ['id' => $org->id]);
        $this->assertNull($member->fresh()->organization_id);
    }

    public function test_notification_preferences_update_is_scoped_to_self(): void
    {
        $userA = $this->makeUser('customer');
        $userB = $this->makeUser('customer');

        $response = $this->actingAs($userA)->put('/_ws/notifications', [
            'preferences' => [
                'ticket.reply' => ['db', 'email'],
                'ticket.assigned' => ['push'],
            ],
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('notification_preferences', [
            'user_id' => $userA->id,
            'event' => 'ticket.reply',
        ]);
        $this->assertDatabaseMissing('notification_preferences', ['user_id' => $userB->id]);

        $pref = NotificationPreference::where('user_id', $userA->id)->where('event', 'ticket.reply')->firstOrFail();
        $this->assertSame(['db', 'email'], $pref->channels);

        $this->actingAs($userA)->get('/_ws/notifications')->assertOk();

        $this->assertContains('ticket.assigned', NotificationPreference::EVENTS);
        $this->assertContains('ticket.reply', NotificationPreference::EVENTS);
        $this->assertContains('ticket.status_changed', NotificationPreference::EVENTS);
        $this->assertContains('ticket.sla_warning', NotificationPreference::EVENTS);
        $this->assertContains('ticket.sla_breached', NotificationPreference::EVENTS);
        $this->assertContains('automation.fired', NotificationPreference::EVENTS);
    }

    public function test_saved_views_crud_and_sharing_rules(): void
    {
        $agent = $this->makeUser('agent');
        $admin = $this->makeUser('admin');

        $response = $this->actingAs($agent)->postJson('/_ws/saved-views', [
            'name' => 'Agent view',
            'filters' => ['status' => 'open'],
            'is_shared' => true,
        ]);
        $response->assertCreated();
        $this->assertFalse((bool) SavedView::findOrFail($response->json('id'))->is_shared);

        $shared = $this->actingAs($admin)->postJson('/_ws/saved-views', [
            'name' => 'Shared admin view',
            'filters' => ['priority' => 'urgent'],
            'is_shared' => true,
        ])->assertCreated()->json();

        $index = $this->actingAs($agent)->getJson('/_ws/saved-views')->assertOk()->json();
        $ids = array_column($index, 'id');
        $this->assertContains($shared['id'], $ids);

        $this->actingAs($agent)->deleteJson('/_ws/saved-views/'.$shared['id'])->assertForbidden();

        $this->actingAs($admin)->deleteJson('/_ws/saved-views/'.$shared['id'])->assertOk();
        $this->assertDatabaseMissing('saved_views', ['id' => $shared['id']]);
    }

    public function test_registration_closed_mode_returns_403(): void
    {
        Setting::set('registration.mode', 'closed');

        $this->post('/register', [
            'name' => 'Blocked User',
            'email' => 'blocked@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertForbidden();

        $this->assertGuest();
    }

    public function test_registration_approval_mode_creates_inactive_user(): void
    {
        Setting::set('registration.mode', 'approval');

        $response = $this->post('/register', [
            'name' => 'Pending User',
            'email' => 'pending@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('login', absolute: false));
        $this->assertGuest();
        $this->assertDatabaseHas('users', [
            'email' => 'pending@example.com',
            'is_active' => false,
        ]);
    }

    public function test_login_rejects_inactive_user(): void
    {
        $user = User::factory()->create();
        $user->forceFill(['is_active' => false])->save();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
