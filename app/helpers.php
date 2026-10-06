<?php

use Carbon\Carbon;

if (! function_exists('wib')) {
    /**
     * Format a date/time for display in Asia/Jakarta with locale-aware month names.
     *
     * Example (id locale): "06 Oktober 2026 19:00 WIB".
     */
    function wib(mixed $value, string $format = 'd F Y H:i', bool $suffix = true): string
    {
        if (blank($value)) {
            return '—';
        }

        try {
            $dt = $value instanceof DateTimeInterface
                ? Carbon::parse($value)
                : Carbon::parse((string) $value);
        } catch (Throwable) {
            return '—';
        }

        $out = $dt->translatedFormat($format);

        return $suffix ? $out.' WIB' : $out;
    }
}
