<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $ticket_id
 * @property int $linked_ticket_id
 * @property string $relation
 * @property int|null $user_id
 */
class TicketLink extends Model
{
    public const RELATIONS = ['parent', 'child', 'related', 'duplicate', 'blocked_by', 'follow_up'];

    protected $guarded = ['id'];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function linkedTicket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'linked_ticket_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
