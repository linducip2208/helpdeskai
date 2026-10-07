<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $ticket_id
 * @property int $user_id
 * @property int $minutes
 * @property string|null $note
 * @property Carbon $worked_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Ticket $ticket
 * @property-read User $user
 */
class TimeEntry extends Model
{
    protected $fillable = [
        'ticket_id',
        'user_id',
        'minutes',
        'note',
        'worked_at',
    ];

    protected function casts(): array
    {
        return [
            'minutes' => 'integer',
            'worked_at' => 'date',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
