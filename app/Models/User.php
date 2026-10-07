<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property string $role
 * @property string|null $avatar
 * @property string|null $phone
 * @property string|null $timezone
 * @property Carbon|null $last_active_at
 * @property bool $is_active
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $assigned_tickets
 * @property int $resolved_tickets
 * @property int $open_tickets
 * @property int $assigned
 * @property int $resolved
 * @property int $open
 * @property-read Collection<int, Ticket> $tickets
 * @property-read Collection<int, Ticket> $assignedTickets
 * @property-read Collection<int, TicketReply> $ticketReplies
 * @property-read Collection<int, TimeEntry> $timeEntries
 * @property-read Collection<int, Conversation> $conversations
 * @property-read Collection<int, ApiKey> $apiKeys
 * @property-read Collection<int, ActivityLog> $activityLogs
 * @property-read int $tickets_count
 * @property-read string $avatar_url
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'avatar', 'phone',
        'timezone', 'role', 'is_active', 'last_active_at',
        'organization_id', 'vip', 'internal_notes',
        'telegram_id', 'whatsapp_id',
    ];

    protected $hidden = [
        'password', 'remember_token',
        'two_factor_secret', 'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_active_at' => 'datetime',
            'is_active' => 'boolean',
            'vip' => 'boolean',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function assignedTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'assigned_to');
    }

    public function ticketReplies(): HasMany
    {
        return $this->hasMany(TicketReply::class);
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function notificationPreferences(): HasMany
    {
        return $this->hasMany(NotificationPreference::class);
    }

    public function savedViews(): HasMany
    {
        return $this->hasMany(SavedView::class);
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_user');
    }

    public function apiKeys(): HasMany
    {
        return $this->hasMany(ApiKey::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isAgent(): bool
    {
        return $this->hasRole('agent');
    }

    public function isCustomer(): bool
    {
        return ! $this->isAdmin() && ! $this->isAgent();
    }

    public function getAvatarUrlAttribute(): string
    {
        $avatar = $this->attributes['avatar'] ?? null;

        if ($avatar) {
            return Storage::disk('public')->url($avatar);
        }

        return 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&background=random';
    }
}
