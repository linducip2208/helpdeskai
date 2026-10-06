<?php

namespace App\Services;

use Carbon\Carbon;

/**
 * Business-hours date math for SLA computation.
 *
 * Workdays use ISO day numbers (1 = Monday .. 7 = Sunday).
 * Holidays are 'Y-m-d' strings evaluated in the policy timezone.
 */
class BusinessHours
{
    /**
     * @param  array<int>  $workdays
     * @param  array<string>  $holidays  Y-m-d dates
     */
    public static function addMinutes(
        Carbon $start,
        int $minutes,
        array $workdays = [1, 2, 3, 4, 5],
        string $workStart = '08:00',
        string $workEnd = '17:00',
        string $timezone = 'Asia/Jakarta',
        array $holidays = []
    ): Carbon {
        if ($minutes <= 0) {
            return $start->copy();
        }

        $workdays = array_values(array_unique(array_map('intval', $workdays)));
        sort($workdays);

        if ($workdays === []) {
            return $start->copy()->addMinutes($minutes);
        }

        $cursor = $start->copy()->setTimezone($timezone);
        $remaining = $minutes;
        $guard = 0;

        while ($remaining > 0 && $guard++ < 20000) {
            if (! self::isWorkday($cursor, $workdays, $holidays)) {
                $cursor = self::nextWorkStart($cursor, $workdays, $workStart, $holidays);

                continue;
            }

            $dayStart = self::atTime($cursor, $workStart);
            $dayEnd = self::atTime($cursor, $workEnd);

            if ($cursor->lessThan($dayStart)) {
                $cursor = $dayStart;
            }

            if (! $cursor->lessThan($dayEnd)) {
                $cursor = self::nextWorkStart($cursor->addDay(), $workdays, $workStart, $holidays);

                continue;
            }

            $available = $cursor->diffInMinutes($dayEnd);

            if ($available >= $remaining) {
                $cursor = $cursor->copy()->addMinutes($remaining);
                $remaining = 0;
            } else {
                $remaining -= max(0, $available);
                $cursor = self::nextWorkStart($cursor->copy()->addDay(), $workdays, $workStart, $holidays);
            }
        }

        return $cursor;
    }

    /**
     * @param  array<int>  $workdays
     * @param  array<string>  $holidays
     */
    public static function isWorkday(Carbon $date, array $workdays, array $holidays = []): bool
    {
        if (! in_array($date->dayOfWeekIso, $workdays, true)) {
            return false;
        }

        return ! in_array($date->format('Y-m-d'), $holidays, true);
    }

    /**
     * @param  array<int>  $workdays
     * @param  array<string>  $holidays
     */
    protected static function nextWorkStart(Carbon $date, array $workdays, string $workStart, array $holidays): Carbon
    {
        $cursor = $date->copy();
        $guard = 0;

        while (! self::isWorkday($cursor, $workdays, $holidays) && $guard++ < 370) {
            $cursor->addDay();
        }

        return self::atTime($cursor, $workStart);
    }

    protected static function atTime(Carbon $date, string $time): Carbon
    {
        [$hour, $minute] = array_map('intval', explode(':', $time) + [0, 0]);

        return $date->copy()->setTime($hour, $minute, 0);
    }
}
