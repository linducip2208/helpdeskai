<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $name
 * @property string $visibility
 * @property int|null $user_id
 * @property array|null $actions
 * @property bool $is_active
 */
class Macro extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'actions' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function visibleTo(User $user): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->visibility === 'shared') {
            return true;
        }

        return (int) $this->user_id === (int) $user->id;
    }
}
