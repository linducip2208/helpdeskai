<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $key
 * @property int $user_id
 * @property string $method
 * @property string $path
 * @property int|null $response_code
 * @property array|null $response_body
 * @property Carbon|null $expires_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 */
class IdempotencyKey extends Model
{
    protected $fillable = [
        'key',
        'user_id',
        'method',
        'path',
        'response_code',
        'response_body',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'response_body' => 'array',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isFresh(): bool
    {
        return $this->expires_at === null || $this->expires_at->isFuture();
    }
}
