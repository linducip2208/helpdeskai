<?php

namespace App\Services\Assignment;

use App\Models\Ticket;
use App\Models\User;

class LeastLoadStrategy implements AssignmentStrategyInterface
{
    public function name(): string
    {
        return 'least_load';
    }

    public function pick(Ticket $ticket): ?User
    {
        return User::role('agent')
            ->where('is_active', true)
            ->withCount(['assignedTickets as open_load' => fn ($q) => $q->whereIn('status', ['open', 'in_progress', 'waiting'])])
            ->orderBy('open_load')
            ->orderBy('id')
            ->first();
    }
}
