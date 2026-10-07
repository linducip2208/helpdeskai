<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportLog extends Model
{
    protected $guarded = ['id'];

    public $timestamps = false;

    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_SUCCESS = 'success';

    public const STATUS_FAILED = 'failed';

    public const STATUS_PARTIAL = 'partial';

    protected function casts(): array
    {
        return [
            'total_rows' => 'integer',
            'imported' => 'integer',
            'failed' => 'integer',
            'errors' => 'array',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ImportLog $log) {
            $log->created_at ??= now();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isRerunnable(): bool
    {
        return $this->status !== self::STATUS_SUCCESS;
    }
}
