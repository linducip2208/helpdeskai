<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Department;
use App\Models\Ticket;
use App\Models\User;
use App\Services\SlaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SlaServiceTest extends TestCase
{
    use RefreshDatabase;

    private function makeTicket(array $overrides = []): Ticket
    {
        $user = User::factory()->create();
        $department = Department::create(['name' => 'Support', 'is_active' => true]);
        $category = Category::create([
            'name' => 'General',
            'slug' => 'general',
            'department_id' => $department->id,
            'is_active' => true,
        ]);

        return Ticket::create(array_merge([
            'uid' => Ticket::generateUid(),
            'user_id' => $user->id,
            'department_id' => $department->id,
            'category_id' => $category->id,
            'subject' => 'Test',
            'body' => 'Body',
            'priority' => 'medium',
            'status' => 'open',
            'source' => 'web',
        ], $overrides));
    }

    public function test_overdue_open_ticket_is_marked_breached(): void
    {
        $ticket = $this->makeTicket(['sla_due_at' => now()->subHour()]);

        $breached = (new SlaService())->evaluateTicket($ticket);

        $this->assertTrue($breached);
        $this->assertTrue((bool) $ticket->fresh()->sla_breached);
    }

    public function test_future_deadline_is_not_breached(): void
    {
        $ticket = $this->makeTicket(['sla_due_at' => now()->addDay()]);

        $breached = (new SlaService())->evaluateTicket($ticket);

        $this->assertFalse($breached);
        $this->assertFalse((bool) $ticket->fresh()->sla_breached);
    }

    public function test_ticket_without_due_date_is_ignored(): void
    {
        $ticket = $this->makeTicket(['sla_due_at' => null]);

        $this->assertFalse((new SlaService())->evaluateTicket($ticket));
    }
}
