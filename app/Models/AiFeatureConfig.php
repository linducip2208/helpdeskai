<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
