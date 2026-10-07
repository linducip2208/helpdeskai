<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int|null $department_id
 * @property string $priority
 * @property int $first_response_time
 * @property int $resolution_time
 * @property array|null $workdays
 * @property string $work_start
 * @property string $work_end
 * @property string $timezone
 * @property bool $use_business_hours
 * @property bool $pause_on_waiting
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Department|null $department
 */
class SlaPolicy extends Model
{
    protected $fillable = [
        'name',
        'description',
        'department_id',
        'priority',
        'first_response_time',
        'resolution_time',
        'workdays',
        'work_start',
        'work_end',
        'timezone',
        'use_business_hours',
        'pause_on_waiting',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'use_business_hours' => 'boolean',
            'pause_on_waiting' => 'boolean',
            'first_response_time' => 'integer',
            'resolution_time' => 'integer',
            'workdays' => 'array',
        ];
    }

    /**
     * @return array<int>
     */
    public function workdayList(): array
    {
        $days = $this->workdays ?? [1, 2, 3, 4, 5];

        return array_values(array_map('intval', (array) $days));
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
