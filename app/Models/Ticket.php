<?php

namespace App\Models;

use App\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Ticket extends Model
{
    protected $guarded = ['id'];

    protected static function booted(): void
    {
        static::saving(function (Ticket $ticket) {
            if (! $ticket->isDirty('status')) {
                return;
            }

            $status = $ticket->status instanceof TicketStatus
                ? $ticket->status->value
                : (string) $ticket->status;

            if (in_array($status, ['resolved', 'closed'], true) && $ticket->closed_at === null) {
                $ticket->closed_at = now();
            }

            if ($status === 'resolved' && $ticket->resolved_at === null) {
                $ticket->resolved_at = now();
            }

            if (in_array($status, ['open', 'in_progress', 'waiting', 'answered'], true)) {
                $ticket->resolved_at = null;
            }
        });
    }

    protected function casts(): array
    {
        return [
            'status' => TicketStatus::class,
            'custom_fields' => 'array',
            'is_starred' => 'boolean',
            'sla_due_at' => 'datetime',
            'sla_response_due_at' => 'datetime',
            'sla_warned_at' => 'datetime',
            'sla_breached' => 'boolean',
            'first_response_at' => 'datetime',
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
            'ai_classification' => 'array',
            'ai_classified_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(TicketReply::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class);
    }

    public function isOpen(): bool
    {
        return ! in_array($this->status, [TicketStatus::Resolved, TicketStatus::Closed]);
    }

    public function isResolved(): bool
    {
        return in_array($this->status, [TicketStatus::Resolved, TicketStatus::Closed]);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            TicketStatus::Open => '#10b981',
            TicketStatus::InProgress => '#3b82f6',
            TicketStatus::Waiting => '#f59e0b',
            TicketStatus::Resolved => '#8b5cf6',
            TicketStatus::Closed => '#6b7280',
            default => '#6b7280',
        };
    }

    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'urgent' => '#ef4444',
            'high' => '#f97316',
            'medium' => '#f59e0b',
            'low' => '#10b981',
            default => '#6b7280',
        };
    }

    public static function generateUid(): string
    {
        $prefix = 'TKT-';

        do {
            $uid = $prefix.strtoupper(Str::random(5));
        } while (static::where('uid', $uid)->exists());

        return $uid;
    }
}
