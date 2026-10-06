<?php

namespace App\Services\Seo;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IndexNowService
{
    private const ENDPOINT = 'https://api.indexnow.org/indexnow';

    private const CACHE_PREFIX = 'indexnow:submitted:';

    private const CACHE_TTL_DAYS = 30;

    public function key(): ?string
    {
        return config('services.indexnow.key') ?: null;
    }

    public function keyLocation(): string
    {
        return url('/' . $this->key() . '.txt');
    }

    /**
     * Submit one or more URLs to IndexNow. Already-submitted URLs are skipped.
     *
     * @param  string|array<int,string>  $urls
     * @return int number of URLs actually submitted
     */
    public function submit(string|array $urls, bool $force = false): int
    {
        $key = $this->key();
        if (! $key) {
            Log::warning('IndexNow: no key configured, skipping submission.');

            return 0;
        }

        $urls = collect(is_array($urls) ? $urls : [$urls])
            ->filter()
            ->unique()
            ->values();

        if (! $force) {
            $urls = $urls->reject(fn ($url) => Cache::has(self::CACHE_PREFIX . md5($url)))->values();
        }

        if ($urls->isEmpty()) {
            return 0;
        }

        $host = parse_url(config('app.url'), PHP_URL_HOST);

        try {
            $response = Http::acceptJson()->post(self::ENDPOINT, [
                'host' => $host,
                'key' => $key,
                'keyLocation' => $this->keyLocation(),
                'urlList' => $urls->all(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('IndexNow submission failed: ' . $e->getMessage());

            return 0;
        }

        if ($response->successful() || $response->status() === 202) {
            foreach ($urls as $url) {
                Cache::put(self::CACHE_PREFIX . md5($url), true, now()->addDays(self::CACHE_TTL_DAYS));
            }

            return $urls->count();
        }

        Log::warning('IndexNow returned status ' . $response->status() . ': ' . $response->body());

        return 0;
    }
}
