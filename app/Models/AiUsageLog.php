<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $provider_id
 * @property int $model_id
 * @property string $feature_key
 * @property int $input_tokens
 * @property int $output_tokens
 * @property float $cost_estimated
 * @property int|null $latency_ms
 * @property bool $success
 * @property string|null $error_message
 * @property int|null $fallback_from_provider_id
 * @property int $attempt_no
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read AiProvider $provider
 * @property-read AiProviderModel $model
 */
class AiUsageLog extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'success' => 'boolean',
            'cost_estimated' => 'float',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(AiProvider::class);
    }

    public function model(): BelongsTo
    {
        return $this->belongsTo(AiProviderModel::class, 'model_id');
    }
}
