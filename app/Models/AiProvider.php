<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

class AiProvider extends Model
{
    protected $fillable = [
        'name',
        'api_format',
        'base_url',
        'api_key_encrypted',
        'extra_headers',
        'is_active',
        'last_tested_at',
        'last_test_status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'extra_headers' => 'array',
            'is_active' => 'boolean',
            'last_tested_at' => 'datetime',
        ];
    }

    public function models(): HasMany
    {
        return $this->hasMany(AiProviderModel::class, 'provider_id');
    }

    public function getDecryptedApiKeyAttribute(): ?string
    {
        if (empty($this->api_key_encrypted)) {
            return null;
        }

        return Crypt::decryptString($this->api_key_encrypted);
    }
}
