<?php

namespace App\Console\Commands;

use App\Services\EscalationService;
use App\Services\SlaService;
use Illuminate\Console\Command;

class CheckSla extends Command
{
    protected $signature = 'sla:check';

    protected $description = 'Evaluate all open tickets for SLA breaches and notify assignees';

    public function handle(SlaService $sla): int
    {
        $sla->checkAllTickets();
        $this->info('SLA check completed.');

        $escalated = app(EscalationService::class)->run();
        $this->info("Escalations applied: {$escalated}.");

        return self::SUCCESS;
    }
}
