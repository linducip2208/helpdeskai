<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\AiUsageLog;
use App\Models\Notification;
use App\Models\Setting;
use App\Models\WebhookDelivery;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class MaintenanceCleanup extends Command
{
    protected $signature = 'maintenance:cleanup';

    protected $description = 'Prune expired retention data: AI logs, webhook deliveries, notifications, sessions, temp files';

    public function handle(): int
    {
        $this->prune(AiUsageLog::class, 'ai_data_retention_days', 365, 'AI usage logs');
        $this->prune(WebhookDelivery::class, 'webhook_retention_days', 90, 'webhook deliveries');

        $notifDays = (int) Setting::get('notification_retention_days', 180);
        if ($notifDays > 0) {
            $count = Notification::whereNotNull('read_at')
                ->where('created_at', '<=', now()->subDays($notifDays))
                ->delete();
            $this->info("Pruned {$count} read notification(s).");
        }

        $auditDays = (int) Setting::get('audit_retention_days', 0);
        if ($auditDays > 0) {
            $count = ActivityLog::where('created_at', '<=', now()->subDays($auditDays))->delete();
            $this->info("Pruned {$count} audit log(s).");
        }

        $sessions = 0;
        if (Schema::hasTable('sessions')) {
            $sessions = DB::table('sessions')->where('last_activity', '<=', now()->subDays(30)->getTimestamp())->delete();
        }
        $this->info("Pruned {$sessions} stale session(s).");

        $tmpFiles = 0;
        foreach (Storage::disk('local')->allFiles('tmp') as $file) {
            if (Storage::disk('local')->lastModified($file) < now()->subDay()->getTimestamp()) {
                Storage::disk('local')->delete($file);
                $tmpFiles++;
            }
        }
        $this->info("Pruned {$tmpFiles} temp file(s).");

        $this->info('Maintenance cleanup completed.');

        return self::SUCCESS;
    }

    private function prune(string $model, string $settingKey, int $default, string $label): void
    {
        $days = (int) Setting::get($settingKey, $default);

        if ($days <= 0) {
            $this->info("Retention for {$label} disabled, skipping.");

            return;
        }

        $count = $model::where('created_at', '<=', now()->subDays($days))->delete();
        $this->info("Pruned {$count} {$label}.");
    }
}
