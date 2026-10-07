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
 * @property string $api_format
 * @property string $base_url
 * @property string $api_key_encrypted
 * @property array|null $extra_headers
 * @property bool $is_active
 * @property int $priority
 * @property int $timeout_seconds
 * @property int $max_retries
 * @property string|null $organization
 * @property Carbon|null $last_tested_at
 * @property string|null $last_test_status
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, AiProviderModel> $models
 * @property-read int $models_count
 * @property-read string|null $decrypted_api_key
 */
class AiProvider extends Model
{
    protected $fillable = [
        'name',
        'api_format',
        'base_url',
        'api_key_encrypted',
        'extra_headers',
        'is_active',
        'priority',
        'timeout_seconds',
        'max_retries',
        'organization',
        'last_tested_at',
        'last_test_status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'extra_headers' => 'array',
            'is_active' => 'boolean',
            'priority' => 'integer',
            'timeout_seconds' => 'integer',
            'max_retries' => 'integer',
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
