<?php

namespace App\Services;

use App\Models\AiQuery;
use App\Models\KnowledgeArticle;
use App\Models\Ticket;
use App\Services\Rag\DatabaseVectorSearch;
use App\Services\Rag\VectorSearchInterface;
use Illuminate\Support\Facades\Log;

class KnowledgeRagService
{
    public function __construct(
        protected VectorSearchInterface $search = new DatabaseVectorSearch,
    ) {}

    /**
     * Retrieve relevant published articles with relevance scores.
     *
     * @return array<int, array{article: KnowledgeArticle, score: float}>
     */
    public function retrieve(string $query, int $limit = 5, ?string $language = null): array
    {
        return $this->search->search($query, $limit, $language);
    }

    /**
     * Answer a question grounded in retrieved articles.
     *
     * @return array{answer: ?string, confidence: ?float, sources: array<int, array{id: int, title: string, slug: string, score: float}>, error?: string}
     */
    public function answer(string $question, int $limit = 4, ?int $userId = null, ?int $ticketId = null): array
    {
        $hits = $this->retrieve($question, $limit);

        if ($hits === []) {
            return ['answer' => null, 'confidence' => null, 'sources' => [], 'error' => 'No relevant articles found.'];
        }

        $context = '';
        $sources = [];
        foreach ($hits as $i => $hit) {
            $article = $hit['article'];
            $sources[] = [
                'id' => $article->id,
                'title' => $article->title,
                'slug' => $article->slug,
                'score' => $hit['score'],
            ];
            $context .= '[Source '.($i + 1).': '.$article->title."]\n".mb_substr(strip_tags($article->content ?? ''), 0, 2000)."\n\n";
        }

        $messages = [
            [
                'role' => 'system',
                'content' => "You are a support assistant. Answer ONLY using the provided sources. Output ONLY compact JSON, no prose, no markdown.\n"
                    .'Schema: {"answer":"<concise answer or null if sources do not cover the question>","confidence":<0.0-1.0 or null>}',
            ],
            ['role' => 'user', 'content' => "Sources:\n{$context}\nQuestion: {$question}"],
        ];

        try {
            $result = app(AiService::class)->dispatch('knowledge.answer', $messages, ['temperature' => 0.1]);
        } catch (\Throwable $e) {
            Log::warning('RAG answer dispatch failed.', ['error' => $e->getMessage()]);

            return ['answer' => null, 'confidence' => null, 'sources' => $sources, 'error' => 'AI unavailable.'];
        }

        if (empty($result['content'])) {
            return ['answer' => null, 'confidence' => null, 'sources' => $sources, 'error' => $result['error'] ?? 'AI unavailable.'];
        }

        $parsed = $this->extractJson($result['content']);
        $answer = is_array($parsed) ? ($parsed['answer'] ?? null) : null;
        $confidence = is_array($parsed) && isset($parsed['confidence']) && is_numeric($parsed['confidence'])
            ? max(0, min(1, (float) $parsed['confidence']))
            : null;

        if (! is_string($answer) || trim($answer) === '') {
            return ['answer' => null, 'confidence' => null, 'sources' => $sources, 'error' => 'AI could not answer from the sources.'];
        }

        try {
            AiQuery::create([
                'question' => mb_substr($question, 0, 2000),
                'answer' => $answer,
                'confidence' => $confidence,
                'sources' => $sources,
                'provider' => $result['provider'] ?? null,
                'model' => $result['model'] ?? null,
                'input_tokens' => (int) ($result['input_tokens'] ?? 0),
                'output_tokens' => (int) ($result['output_tokens'] ?? 0),
                'latency_ms' => isset($result['latency_ms']) ? (int) $result['latency_ms'] : null,
                'cost_estimated' => $result['cost_estimated'] ?? 0,
                'ticket_id' => $ticketId,
                'user_id' => $userId ?? auth()->id(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('AiQuery logging failed.', ['error' => $e->getMessage()]);
        }

        return ['answer' => $answer, 'confidence' => $confidence, 'sources' => $sources];
    }

    /**
     * Recommend articles for a ticket (same retrieval, no LLM call).
     *
     * @return array<int, array{id: int, title: string, slug: string, score: float}>
     */
    public function recommendForTicket(Ticket $ticket, int $limit = 4): array
    {
        $query = $ticket->subject."\n".$ticket->body;
        $hits = $this->retrieve($query, $limit);

        return array_map(fn ($hit) => [
            'id' => $hit['article']->id,
            'title' => $hit['article']->title,
            'slug' => $hit['article']->slug,
            'score' => $hit['score'],
        ], $hits);
    }

    protected function extractJson(string $content): mixed
    {
        $content = trim($content);

        if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/i', $content, $m)) {
            $content = trim($m[1]);
        }

        if (preg_match('/\{[\s\S]*\}/', $content, $m)) {
            $content = $m[0];
        }

        return json_decode($content, true);
    }
}
