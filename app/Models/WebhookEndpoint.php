<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

class WebhookEndpoint extends Model
{
    public const EVENTS = [
        'ticket.created',
        'ticket.replied',
        'ticket.status_changed',
        'ticket.assigned',
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
