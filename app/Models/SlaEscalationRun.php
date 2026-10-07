<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $rule_id
 * @property int $ticket_id
 */
class SlaEscalationRun extends Model
{
    protected $guarded = ['id'];

    public function rule(): BelongsTo
    {
        return $this->belongsTo(SlaEscalationRule::class, 'rule_id');
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }
}
