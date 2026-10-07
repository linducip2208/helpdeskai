<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;

/**
 * @property int $id
 * @property string $name
 * @property string $url
 * @property string|null $secret_encrypted
 * @property array|null $events
 * @property bool $is_active
 * @property int $timeout_seconds
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, WebhookDelivery> $deliveries
 * @property-read int $failed_deliveries
 */
class WebhookEndpoint extends Model
{
    public const EVENTS = [
        'ticket.created',
        'ticket.updated',
        'ticket.replied',
        'ticket.assigned',
        'ticket.status_changed',
        'ticket.closed',
        'ticket.reopened',
        'ticket.sla_warning',
        'ticket.sla_breached',
        'customer.created',
        'csat.created',
        'ai.completed',
    ];

    protected $fillable = [
        'name',
        'url',
        'events',
        'is_active',
        'timeout_seconds',
    ];

    protected function casts(): array
    {
        return [
            'events' => 'array',
            'is_active' => 'boolean',
            'timeout_seconds' => 'integer',
        ];
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(WebhookDelivery::class);
    }

    public function setSecret(string $secret): void
    {
        $this->forceFill(['secret_encrypted' => Crypt::encryptString($secret)])->save();
    }

    public function decryptedSecret(): ?string
    {
        if (empty($this->secret_encrypted)) {
            return null;
        }

        return Crypt::decryptString($this->secret_encrypted);
    }

    /**
     * @return array<int, string>
     */
    public function eventList(): array
    {
        return array_values(array_intersect($this->events ?? [], self::EVENTS));
    }
}
