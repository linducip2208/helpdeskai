<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Ticket;
use App\Services\Assignment\AiDepartmentLoadStrategy;
use App\Services\Assignment\AssignmentStrategyInterface;
use App\Services\Assignment\LeastLoadStrategy;
use App\Services\Assignment\ManualStrategy;
use App\Services\Assignment\RoundRobinStrategy;

class TicketAssignmentService
{
    /**
     * @return array<string, class-string<AssignmentStrategyInterface>>
     */
    public static function strategies(): array
    {
        return [
            'manual' => ManualStrategy::class,
            'round_robin' => RoundRobinStrategy::class,
            'least_load' => LeastLoadStrategy::class,
            'ai' => AiDepartmentLoadStrategy::class,
        ];
    }

    public function strategy(): AssignmentStrategyInterface
    {
        $key = (string) Setting::get('assignment.strategy', 'manual');
        $class = self::strategies()[$key] ?? ManualStrategy::class;

        return app($class);
    }

    public function autoAssign(Ticket $ticket): ?int
    {
        $agent = $this->strategy()->pick($ticket->fresh() ?? $ticket);

        if (! $agent) {
            return null;
        }

        app(TicketService::class)->assignTicket($ticket, $agent->id);

        return $agent->id;
    }
}
