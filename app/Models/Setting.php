<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * @property int $id
 * @property string $key
 * @property string|null $value
 * @property string $type
 * @property string $group
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
    ];

    public $timestamps = false;

    public static function get(string $key, mixed $default = null): mixed
    {
        $all = Cache::rememberForever('settings:all', function () {
            $values = static::pluck('value', 'key')->all();
            $types = static::pluck('type', 'key')->all();
            foreach ($types as $k => $type) {
                $values[$k.':type'] = $type;
            }

            return $values;
        });

        if (! array_key_exists($key, $all)) {
            return $default;
        }

        $type = $all[$key.':type'] ?? 'string';

        return match ($type) {
            'boolean' => (bool) $all[$key],
            'integer' => (int) $all[$key],
            'json', 'array' => json_decode($all[$key], true),
            default => $all[$key],
        };
    }

    public static function set(string $key, mixed $value): self
    {
        $type = match (true) {
            is_bool($value) => 'boolean',
            is_int($value) => 'integer',
            is_array($value) => 'json',
            default => 'string',
        };

        $storedValue = is_array($value) ? json_encode($value) : (string) $value;

        $setting = static::updateOrCreate(
            ['key' => $key],
            ['value' => $storedValue, 'type' => $type],
        );

        Cache::forget('settings:all');

        return $setting;
    }

    public static function flushCache(): void
    {
        Cache::forget('settings:all');
    }
}
