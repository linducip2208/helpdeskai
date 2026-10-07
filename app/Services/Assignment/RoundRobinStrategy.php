<?php

namespace App\Services\Assignment;

use App\Models\Ticket;
use App\Models\User;

class RoundRobinStrategy implements AssignmentStrategyInterface
{
    public function name(): string
    {
        return 'round_robin';
    }

    public function pick(Ticket $ticket): ?User
    {
        $agents = User::role('agent')->where('is_active', true)->orderBy('id')->get(['id']);

        if ($agents->isEmpty()) {
            return null;
        }

        $lastAssignedId = (int) Ticket::whereNotNull('assigned_to')->max('assigned_to');
        $ids = $agents->pluck('id')->all();

        foreach ($ids as $id) {
            if ($id > $lastAssignedId) {
                return User::find($id);
            }
        }

        return User::find($ids[0]);
    }
}
