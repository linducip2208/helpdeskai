<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string|null $url_pattern
 * @property string|null $target_type
 * @property int|null $target_id
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $meta_keywords
 * @property string|null $og_title
 * @property string|null $og_description
 * @property string|null $og_image
 * @property string|null $twitter_title
 * @property string|null $twitter_description
 * @property string|null $twitter_image
 * @property string|null $canonical_url
 * @property string|null $schema_json
 * @property bool $noindex
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Model|null $target
 */
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
