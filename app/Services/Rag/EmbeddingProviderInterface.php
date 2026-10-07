<?php

namespace App\Services\Rag;

interface EmbeddingProviderInterface
{
    /**
     * @return array<int, float>|null Null when embeddings are unavailable.
     */
    public function embed(string $text): ?array;

    public function dimensions(): ?int;
}
