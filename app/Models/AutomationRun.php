<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int|null $rule_id
 * @property int|null $ticket_id
 * @property string|null $trigger
 * @property string $status
 * @property array|null $actions_executed
 * @property string|null $error
 * @property int|null $duration_ms
 */
class AutomationRun extends Model
{
    public const STATUSES = ['pending', 'running', 'success', 'failed', 'skipped'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'actions_executed' => 'array',
            'duration_ms' => 'integer',
        ];
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(AutomationRule::class, 'rule_id');
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }
}
