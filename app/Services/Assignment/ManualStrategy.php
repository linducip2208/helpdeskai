<?php

namespace App\Services\Assignment;

use App\Models\Ticket;
use App\Models\User;

class ManualStrategy implements AssignmentStrategyInterface
{
    public function name(): string
    {
        return 'manual';
    }

    public function pick(Ticket $ticket): ?User
    {
        return null;
    }
}
