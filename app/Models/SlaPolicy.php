<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'use_business_hours' => 'boolean',
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
