<?php

namespace App\Services\Rag;

use App\Models\KnowledgeArticle;

interface VectorSearchInterface
{
    /**
     * @return array<int, array{article: KnowledgeArticle, score: float}>
     */
    public function search(string $query, int $limit = 5, ?string $language = null): array;
}
