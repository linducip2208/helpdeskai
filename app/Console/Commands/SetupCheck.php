<?php

namespace App\Console\Commands;

use App\Models\AiProvider;
use App\Services\LicenseClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SetupCheck extends Command
{
    protected $signature = 'setup:check';

    protected $description = 'Verify application setup: keys, database, storage, cache, queue, mail, broadcast, AI and license';

    public function handle(LicenseClient $license): int
    {
        $rows = [];
        $fail = 0;

        $check = function (string $label, callable $fn) use (&$rows, &$fail) {
            try {
                $result = $fn();
                $status = $result['status'] ?? 'OK';
                $detail = $result['detail'] ?? '';
            } catch (\Throwable $e) {
                $status = 'FAIL';
                $detail = $e->getMessage();
            }
            if ($status === 'FAIL') {
                $fail++;
            }
            $rows[] = [$label, $status, $detail];
        };

        $check('APP_KEY', fn () => config('app.key')
            ? ['status' => 'OK', 'detail' => 'set']
            : ['status' => 'FAIL', 'detail' => 'APP_KEY is empty']);

        $check('Database', function () {
            DB::select('select 1 as ok');

            return ['status' => 'OK', 'detail' => config('database.default')];
        });

        $check('Storage writable', function () {
            // Probe with a real write: is_writable() is unreliable on
            // Windows ACLs, so verify we can actually create a file.
            $probe = storage_path('app/.setup-check-probe');

            try {
                if (@file_put_contents($probe, 'ok') === false) {
                    return ['status' => 'FAIL', 'detail' => storage_path().' not writable'];
                }
                @unlink($probe);
            } catch (\Throwable $e) {
                return ['status' => 'FAIL', 'detail' => $e->getMessage()];
            }

            return ['status' => 'OK', 'detail' => storage_path()];
        });

        $check('Cache writable', function () {
            Cache::put('setup-check-ping', 'ok', 60);

            return Cache::get('setup-check-ping') === 'ok'
                ? ['status' => 'OK', 'detail' => config('cache.default')]
                : ['status' => 'FAIL', 'detail' => 'cache write/read mismatch'];
        });

        $check('Queue connection', function () {
            $conn = config('queue.default');
            $valid = array_keys(config('queue.connections', []));

            return in_array($conn, $valid, true)
                ? ['status' => 'OK', 'detail' => $conn]
                : ['status' => 'FAIL', 'detail' => "unknown connection: {$conn}"];
        });

        $check('Mailer', function () {
            $mailer = config('mail.default');

            if ($mailer === 'log') {
                return ['status' => 'WARN', 'detail' => 'mailer=log (emails not actually sent)'];
            }

            return ['status' => 'OK', 'detail' => $mailer];
        });

        $check('Broadcast/Reverb', function () {
            if (config('broadcasting.default') !== 'reverb') {
                return ['status' => 'OK', 'detail' => 'driver='.config('broadcasting.default')];
            }
            $missing = [];
            $reverb = config('reverb', []);
            foreach (['key' => 'REVERB_APP_KEY', 'secret' => 'REVERB_APP_SECRET', 'app_id' => 'REVERB_APP_ID', 'host' => 'REVERB_HOST'] as $key => $var) {
                if (empty($reverb['apps']['reverb'][$key] ?? null) && empty($_SERVER[$var] ?? $_ENV[$var] ?? null)) {
                    $missing[] = $var;
                }
            }

            return $missing === []
                ? ['status' => 'OK', 'detail' => 'reverb configured']
                : ['status' => 'FAIL', 'detail' => 'missing: '.implode(', ', $missing)];
        });

        $check('AI providers', function () {
            try {
                $count = AiProvider::where('is_active', true)->count();
            } catch (\Throwable) {
                return ['status' => 'WARN', 'detail' => 'ai_providers table unavailable'];
            }

            return $count > 0
                ? ['status' => 'OK', 'detail' => $count.' active']
                : ['status' => 'WARN', 'detail' => 'no active AI provider'];
        });

        $check('License paired', function () use ($license) {
            try {
                $domain = parse_url(config('app.url', ''), PHP_URL_HOST) ?: request()->getHost();
                $paired = $license->isPaired((string) $domain);
            } catch (\Throwable $e) {
                return ['status' => 'WARN', 'detail' => 'check skipped: '.$e->getMessage()];
            }

            return $paired
                ? ['status' => 'OK', 'detail' => 'paired']
                : ['status' => 'WARN', 'detail' => 'not paired'];
        });

        $this->table(['Check', 'Status', 'Detail'], $rows);

        return $fail > 0 ? self::FAILURE : self::SUCCESS;
    }
}
