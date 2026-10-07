<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $trigger
 * @property int $after_minutes
 * @property string|null $action_priority
 * @property string|null $action_assign_role
 * @property bool $notify_assignee
 * @property bool $is_active
 */
class SlaEscalationRule extends Model
{
    public const TRIGGERS = ['response_warning', 'response_breach', 'resolution_warning', 'resolution_breach'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'after_minutes' => 'integer',
            'notify_assignee' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
