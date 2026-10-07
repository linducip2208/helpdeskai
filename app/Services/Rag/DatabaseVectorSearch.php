<?php

namespace App\Services\Rag;

use App\Models\KnowledgeArticle;

/**
 * Database TF-IDF vector-space retrieval implementing VectorSearchInterface.
 *
 * Each article and the query are represented as TF-IDF weighted term vectors
 * over the corpus vocabulary and ranked by cosine similarity — genuine
 * vector-space retrieval with zero external dependencies.
 *
 * Drop-in replacement point: bind a real embedding backend (pgvector,
 * Meilisearch, Pinecone, …) to VectorSearchInterface and optionally an
 * embedding model to EmbeddingProviderInterface — no other code changes required.
 */
class DatabaseVectorSearch implements VectorSearchInterface
{
    public function __construct(protected ?EmbeddingProviderInterface $embeddings = null) {}

    public function search(string $query, int $limit = 5, ?string $language = null): array
    {
        $queryTerms = $this->terms($query);

        if ($queryTerms === []) {
            return [];
        }

        $articles = KnowledgeArticle::published()
            ->when($language, fn ($q) => $q->where('language', $language))
            ->orderByDesc('is_featured')
            ->orderByDesc('helpful_count')
            ->take(200)
            ->get(['id', 'title', 'slug', 'content', 'excerpt', 'category_id', 'language', 'helpful_count']);

        if ($articles->isEmpty()) {
            return [];
        }

        $documents = [];
        foreach ($articles as $article) {
            $documents[$article->id] = $this->tokens(
                $article->title.' '.($article->excerpt ?? '').' '.mb_substr(strip_tags($article->content ?? ''), 0, 4000)
            );
        }

        $idf = $this->inverseDocumentFrequency($documents);
        $queryVector = $this->tfIdfVector(array_count_values($this->tokens($query)), $idf);

        if ($this->magnitude($queryVector) <= 0) {
            return [];
        }

        $scored = [];
        foreach ($articles as $article) {
            $docVector = $this->tfIdfVector(array_count_values($documents[$article->id]), $idf);
            $score = $this->cosine($queryVector, $docVector);

            if ($score > 0) {
                if ($article->helpful_count > 0) {
                    $score *= 1 + min(0.25, $article->helpful_count / 200);
                }
                $scored[] = ['article' => $article, 'score' => round($score, 4)];
            }
        }

        usort($scored, fn ($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($scored, 0, max(1, $limit));
    }

    /**
     * @param  array<int, array<int, string>>  $documents
     * @return array<string, float>
     */
    protected function inverseDocumentFrequency(array $documents): array
    {
        $docCount = count($documents);
        $docFreq = [];

        foreach ($documents as $terms) {
            foreach (array_unique($terms) as $term) {
                $docFreq[$term] = ($docFreq[$term] ?? 0) + 1;
            }
        }

        $idf = [];
        foreach ($docFreq as $term => $df) {
            $idf[$term] = log(($docCount + 1) / ($df + 1)) + 1;
        }

        return $idf;
    }

    /**
     * @param  array<string, int>  $termFreq
     * @param  array<string, float>  $idf
     * @return array<string, float>
     */
    protected function tfIdfVector(array $termFreq, array $idf): array
    {
        $vector = [];
        foreach ($termFreq as $term => $tf) {
            if (isset($idf[$term])) {
                $vector[$term] = (1 + log(max(1, $tf))) * $idf[$term];
            }
        }

        return $vector;
    }

    /**
     * @param  array<string, float>  $a
     * @param  array<string, float>  $b
     */
    protected function cosine(array $a, array $b): float
    {
        $dot = 0.0;
        foreach ($a as $term => $weight) {
            if (isset($b[$term])) {
                $dot += $weight * $b[$term];
            }
        }

        $mag = $this->magnitude($a) * $this->magnitude($b);

        return $mag > 0 ? $dot / $mag : 0.0;
    }

    /**
     * @param  array<string, float>  $vector
     */
    protected function magnitude(array $vector): float
    {
        $sum = 0.0;
        foreach ($vector as $weight) {
            $sum += $weight * $weight;
        }

        return sqrt($sum);
    }

    /**
     * @return array<int, string>
     */
    protected function terms(string $query): array
    {
        return array_values(array_unique($this->tokens($query)));
    }

    /**
     * @return array<int, string>
     */
    protected function tokens(string $text): array
    {
        $words = preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower($text));
        $stopwords = ['the', 'a', 'an', 'and', 'or', 'yang', 'dan', 'atau', 'di', 'ke', 'dari', 'untuk', 'dengan', 'pada', 'adalah', 'ini', 'itu', 'saya', 'kami', 'anda'];

        return array_values(array_filter(
            $words,
            fn ($w) => mb_strlen($w) >= 3 && ! in_array($w, $stopwords, true)
        ));
    }
}
