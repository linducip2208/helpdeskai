<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Console\Command;

class AutoCloseTickets extends Command
{
    protected $signature = 'tickets:autoclose';

    protected $description = 'Close resolved tickets older than the configured auto-close threshold';

    public function handle(TicketService $tickets): int
    {
        $days = (int) Setting::get('auto_close_days', 7);

        if ($days <= 0) {
            $this->info('Auto-close disabled (auto_close_days <= 0).');

            return self::SUCCESS;
        }

        $count = 0;
        Ticket::where('status', 'resolved')
            ->where('resolved_at', '<=', now()->subDays($days))
            ->chunkById(200, function ($resolved) use ($tickets, &$count) {
                foreach ($resolved as $ticket) {
                    $tickets->closeTicket($ticket);
                    $count++;
                }
            });

        $this->info("Auto-closed {$count} ticket(s).");

        return self::SUCCESS;
    }
}
