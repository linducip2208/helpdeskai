<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $key
 * @property string $subject
 * @property string $body
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class EmailTemplate extends Model
{
    protected $fillable = [
        'name',
        'key',
        'subject',
        'body',
        'description',
    ];
}
