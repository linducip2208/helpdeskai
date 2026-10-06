<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
