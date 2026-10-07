<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $department_id
 * @property string $name
 * @property string $label
 * @property string $type
 * @property array|null $options
 * @property bool $is_required
 * @property bool $is_active
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Department|null $department
 */
class TicketCustomField extends Model
{
    public const TYPES = ['text', 'textarea', 'number', 'date', 'select', 'checkbox'];

    protected $fillable = [
        'department_id',
        'name',
        'label',
        'type',
        'options',
        'is_required',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'is_required' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * @return array<int, string>
     */
    public function optionList(): array
    {
        return array_values(array_filter(array_map('trim', (array) ($this->options ?? []))));
    }
}
