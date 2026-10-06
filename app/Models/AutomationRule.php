<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutomationRule extends Model
{
    protected $fillable = [
        'name',
        'description',
        'trigger_event',
        'conditions',
        'actions',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'conditions' => 'array',
            'actions' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
