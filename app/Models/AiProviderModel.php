<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $provider_id
 * @property string $model_id
 * @property string $display_name
 * @property string $capability
 * @property float|null $cost_input_per_1m
 * @property float|null $cost_output_per_1m
 * @property int|null $max_tokens
 * @property int|null $context_window
 * @property int|null $max_output_tokens
 * @property bool $is_active
 * @property int $priority
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read AiProvider $provider
 */
class AiProviderModel extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'cost_input_per_1m' => 'float',
            'cost_output_per_1m' => 'float',
            'max_tokens' => 'integer',
            'context_window' => 'integer',
            'max_output_tokens' => 'integer',
            'priority' => 'integer',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(AiProvider::class, 'provider_id');
    }
}
