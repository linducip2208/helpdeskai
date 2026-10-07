<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property int|null $category_id
 * @property int $user_id
 * @property string $content
 * @property string|null $excerpt
 * @property string $status
 * @property bool $is_featured
 * @property int $view_count
 * @property int $helpful_count
 * @property int $not_helpful_count
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property array|null $tags
 * @property string $language
 * @property array|null $metadata
 * @property string $embedding_status
 * @property Carbon|null $published_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read KnowledgeCategory|null $category
 * @property-read User $author
 * @property-read User $user
 */
class KnowledgeArticle extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'view_count' => 'integer',
            'helpful_count' => 'integer',
            'not_helpful_count' => 'integer',
            'tags' => 'array',
            'metadata' => 'array',
            'published_at' => 'datetime',
        ];
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(KnowledgeCategory::class, 'category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
