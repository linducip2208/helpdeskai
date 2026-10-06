<?php

namespace Tests\Unit;

use App\Services\BusinessHours;
use Carbon\Carbon;
use Tests\TestCase;

class BusinessHoursTest extends TestCase
{
    public function test_adds_minutes_within_workday(): void
    {
        $start = Carbon::parse('2026-10-06 09:00', 'Asia/Jakarta'); // Tuesday
        $due = BusinessHours::addMinutes($start, 60);

        $this->assertEquals('2026-10-06 10:00', $due->format('Y-m-d H:i'));
    }

    public function test_skips_weekend(): void
    {
        $start = Carbon::parse('2026-10-09 16:00', 'Asia/Jakarta'); // Friday
        $due = BusinessHours::addMinutes($start, 120);

        // 60 min Friday + 60 min Monday from 08:00
        $this->assertEquals('2026-10-12 09:00', $due->format('Y-m-d H:i'));
    }

    public function test_rolls_after_hours_to_next_morning(): void
    {
        $start = Carbon::parse('2026-10-06 19:00', 'Asia/Jakarta'); // Tuesday evening
        $due = BusinessHours::addMinutes($start, 30);

        $this->assertEquals('2026-10-07 08:30', $due->format('Y-m-d H:i'));
    }

    public function test_skips_holidays(): void
    {
        $start = Carbon::parse('2026-10-06 16:30', 'Asia/Jakarta'); // Tuesday
        $due = BusinessHours::addMinutes($start, 60, [1, 2, 3, 4, 5], '08:00', '17:00', 'Asia/Jakarta', ['2026-10-07']);

        // 30 min Tue + skip Wed holiday + 30 min Thu from 08:00
        $this->assertEquals('2026-10-08 08:30', $due->format('Y-m-d H:i'));
    }

    public function test_custom_workdays(): void
    {
        $start = Carbon::parse('2026-10-10 10:00', 'Asia/Jakarta'); // Saturday
        $due = BusinessHours::addMinutes($start, 60, [6, 7]);

        $this->assertEquals('2026-10-10 11:00', $due->format('Y-m-d H:i'));
    }
}
