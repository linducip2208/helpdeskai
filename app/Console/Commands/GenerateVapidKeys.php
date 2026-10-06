<?php

namespace App\Console\Commands;

use App\Services\WebPushService;
use Illuminate\Console\Command;

class GenerateVapidKeys extends Command
{
    protected $signature = 'webpush:vapid-keys';

    protected $description = 'Generate VAPID keypair for web push notifications';

    public function handle(WebPushService $service): int
    {
        $keys = $service->generateVapidKeys();

        $this->info('VAPID keys generated. Add to your .env:');
        $this->line('');
        $this->line('VAPID_SUBJECT=mailto:admin@yourdomain.com');
        $this->line('VAPID_PUBLIC_KEY=' . $keys['public']);
        $this->line('');
        $this->line('VAPID_PRIVATE_KEY="' . str_replace("\n", '\n', trim($keys['private_pem'])) . '"');
        $this->line('');
        $this->warn('Store the private key securely. Do not commit it to git.');

        return self::SUCCESS;
    }
}
