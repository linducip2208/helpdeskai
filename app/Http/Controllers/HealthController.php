<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class HealthController extends Controller
{
    public function health(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'app' => config('app.name'),
            'version' => $this->version(),
            'time' => now()->toIso8601String(),
        ]);
    }

    public function ready(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
        ];

        $ready = ! in_array(false, $checks, true);

        return response()->json([
            'ready' => $ready,
            'checks' => $checks,
        ], $ready ? 200 : 503);
    }

    protected function version(): string
    {
        $file = base_path('VERSION');

        return is_file($file) ? trim((string) file_get_contents($file)) : 'dev';
    }

    protected function checkDatabase(): bool
    {
        try {
            DB::select('select 1');

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    protected function checkCache(): bool
    {
        try {
            Cache::put('health:ping', 'pong', 60);

            return Cache::get('health:ping') === 'pong';
        } catch (\Throwable) {
            return false;
        }
    }

    protected function checkStorage(): bool
    {
        try {
            return Storage::disk('local')->exists('.') || is_writable(storage_path('app'));
        } catch (\Throwable) {
            return false;
        }
    }
}
