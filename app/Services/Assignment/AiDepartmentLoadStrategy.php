<?php

namespace App\Services\Assignment;

use App\Models\Ticket;
use App\Models\User;

class AiDepartmentLoadStrategy implements AssignmentStrategyInterface
{
    public function name(): string
    {
        return 'ai';
    }

    public function pick(Ticket $ticket): ?User
    {
        $query = User::role('agent')->where('is_active', true);

        if ($ticket->department_id) {
            $inDept = (clone $query)->whereHas('assignedTickets', fn ($q) => $q->where('department_id', $ticket->department_id));

            if ($inDept->exists()) {
                $query->whereHas('assignedTickets', fn ($q) => $q->where('department_id', $ticket->department_id));
            }
        }

        return $query
            ->withCount(['assignedTickets as open_load' => fn ($q) => $q->whereIn('status', ['open', 'in_progress', 'waiting'])])
            ->orderBy('open_load')
            ->orderBy('id')
            ->first();
    }
}
