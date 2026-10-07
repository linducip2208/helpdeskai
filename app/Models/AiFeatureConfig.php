<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $feature_key
 * @property int|null $provider_id
 * @property int|null $model_id
 * @property array|null $options
 * @property bool $is_enabled
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read AiProvider|null $provider
 * @property-read AiProviderModel|null $model
 */
class AiFeatureConfig extends Model
{
    protected $fillable = [
        'feature_key',
        'provider_id',
        'model_id',
        'options',
        'is_enabled',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'is_enabled' => 'boolean',
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
