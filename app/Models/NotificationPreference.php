<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    protected $guarded = ['id'];

    public const EVENTS = [
        'ticket.created',
        'ticket.assigned',
        'ticket.reply',
        'ticket.status_changed',
        'ticket.sla_warning',
        'ticket.sla_breached',
        'ticket_reminder',
        'automation.fired',
    ];

    public const CHANNELS = ['db', 'email', 'push'];

    protected function casts(): array
    {
        return [
            'channels' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
