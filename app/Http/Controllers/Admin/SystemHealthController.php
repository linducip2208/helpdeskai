<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\View\View;

class SystemHealthController extends Controller
{
    public function index(): View
    {
        $checks = [
            $this->row('PHP', PHP_VERSION, version_compare(PHP_VERSION, '8.3.0', '>='), 'PHP 8.3+ required.'),
            $this->row('Laravel', app()->version(), true),
            $this->row(
                'Debug mode',
                config('app.debug') ? 'ON' : 'OFF',
                ! (config('app.debug') && app()->isProduction()),
                'APP_DEBUG should be false in production.'
            ),
            $this->row('Database', config('database.default').' — OK', $this->can(fn () => DB::select('select 1')), 'Cannot reach database.'),
            $this->row('Cache ('.config('cache.default').')', $this->cacheWorks() ? 'OK' : 'FAIL', $this->cacheWorks(), 'Cache store unreachable.'),
            $this->row('Queue', config('queue.default'), true, '', 'info'),
            $this->row(
                'Storage writable',
                is_writable(storage_path('app')) ? 'OK' : 'FAIL',
                is_writable(storage_path('app')),
                'storage/app is not writable.'
            ),
            $this->row(
                'Bootstrap cache writable',
                is_writable(base_path('bootstrap/cache')) ? 'OK' : 'FAIL',
                is_writable(base_path('bootstrap/cache')),
                'bootstrap/cache is not writable (config/route caching will fail).'
            ),
            $this->row('Mailer', config('mail.default', 'log'), true, '', 'info'),
            $this->row('Scheduler', 'cron: * * * * * php artisan schedule:run', $this->schedulerHint(), 'Ensure system cron runs the scheduler every minute.', 'info'),
            $this->row(
                'AI providers active',
                (string) AiProvider::where('is_active', true)->count(),
                true,
                '',
                'info'
            ),
            $this->row(
                'Disk free',
                $this->diskFree(),
                $this->diskFreePercent() > 10,
                'Disk space below 10%.'
            ),
        ];

        if (config('database.redis.client')) {
            $checks[] = $this->row('Redis', $this->redisWorks() ? 'OK' : 'FAIL', $this->redisWorks(), 'Redis configured but unreachable.');
        }

        return view('admin.system-health.index', ['checks' => $checks]);
    }

    /**
     * @return array{label: string, value: string, ok: bool, hint: string, level: string}
     */
    protected function row(string $label, string $value, bool $ok, string $hint = '', string $level = ''): array
    {
        return [
            'label' => $label,
            'value' => $value,
            'ok' => $ok,
            'hint' => $ok ? '' : $hint,
            'level' => $level ?: ($ok ? 'ok' : 'error'),
        ];
    }

    protected function can(callable $fn): bool
    {
        try {
            $fn();

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    protected function cacheWorks(): bool
    {
        return $this->can(function () {
            Cache::put('health:check', 'ok', 60);

            if (Cache::get('health:check') !== 'ok') {
                throw new \RuntimeException('Cache read mismatch.');
            }
        });
    }

    protected function schedulerHint(): bool
    {
        return true;
    }

    protected function diskFree(): string
    {
        $free = @disk_free_space(storage_path('app'));
        $total = @disk_total_space(storage_path('app'));

        if (! $free || ! $total) {
            return 'unknown';
        }

        return round($free / 1073741824, 1).' GB free of '.round($total / 1073741824, 1).' GB';
    }

    protected function diskFreePercent(): float
    {
        $free = @disk_free_space(storage_path('app'));
        $total = @disk_total_space(storage_path('app'));

        if (! $free || ! $total) {
            return 100.0;
        }

        return round($free / $total * 100, 1);
    }

    protected function redisWorks(): bool
    {
        return $this->can(fn () => Redis::ping());
    }
}
