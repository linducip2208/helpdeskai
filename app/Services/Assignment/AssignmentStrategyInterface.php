<?php

namespace App\Services\Assignment;

use App\Models\Ticket;
use App\Models\User;

interface AssignmentStrategyInterface
{
    public function name(): string;

    public function pick(Ticket $ticket): ?User;
}
