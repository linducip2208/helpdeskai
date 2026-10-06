<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SeoMeta extends Model
{
    protected $table = 'seo_meta';

    protected $fillable = [
        'url_pattern',
        'target_type',
        'target_id',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_title',
        'og_description',
        'og_image',
        'twitter_title',
        'twitter_description',
        'twitter_image',
        'canonical_url',
        'schema_json',
        'noindex',
    ];

    protected function casts(): array
    {
        return [
            'noindex' => 'boolean',
        ];
    }

    public function target(): MorphTo
    {
        return $this->morphTo();
    }
}
