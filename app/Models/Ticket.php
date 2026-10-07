<?php

namespace App\Models;

use App\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $uid
 * @property int $user_id
 * @property int|null $assigned_to
 * @property int|null $department_id
 * @property int|null $category_id
 * @property string $subject
 * @property string $body
 * @property string $priority
 * @property TicketStatus $status
 * @property string $source
 * @property Carbon|null $sla_due_at
 * @property Carbon|null $sla_response_due_at
 * @property Carbon|null $sla_warned_at
 * @property bool $sla_breached
 * @property Carbon|null $closed_at
 * @property Carbon|null $first_response_at
 * @property Carbon|null $resolved_at
 * @property Carbon|null $sla_pause_started_at
 * @property int $sla_paused_seconds
 * @property string|null $language
 * @property int|null $satisfaction_rating
 * @property string|null $satisfaction_comment
 * @property bool $is_starred
 * @property array|null $custom_fields
 * @property array|null $ai_classification
 * @property string|null $ai_sentiment
 * @property Carbon|null $ai_classified_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property-read User|null $assignedTo
 * @property-read Department|null $department
 * @property-read Category|null $category
 * @property-read Collection<int, TicketReply> $replies
 * @property-read Collection<int, TicketAttachment> $attachments
 * @property-read Collection<int, TimeEntry> $timeEntries
 * @property-read string $status_color
 * @property-read string $priority_color
 */
class Ticket extends Model
{
    protected $guarded = ['id'];

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('dashboard:stats'));
        static::deleted(fn () => Cache::forget('dashboard:stats'));

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
            'sla_paused_seconds' => 'integer',
            'sla_pause_started_at' => 'datetime',
            'sla_breached' => 'boolean',
            'satisfaction_rating' => 'integer',
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

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'ticket_tag');
    }

    public function links(): HasMany
    {
        return $this->hasMany(TicketLink::class);
    }

    public function watchers(): HasMany
    {
        return $this->hasMany(Watcher::class);
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
        $prefix = (string) Setting::get('ticket_prefix', 'TKT-');
        $prefix = $prefix !== '' ? $prefix : 'TKT-';

        do {
            $uid = $prefix.strtoupper(Str::random(5));
        } while (static::where('uid', $uid)->exists());

        return $uid;
    }
}
