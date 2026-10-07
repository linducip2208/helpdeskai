<?php

namespace App\Services;

use App\Models\AiQuery;
use App\Models\KbSearch;
use Illuminate\Support\Collection;

class KbAnalyticsService
{
    /**
     * Searches that returned zero results (failed searches), newest first.
     */
    public function failedSearches(int $limit = 50): Collection
    {
        return KbSearch::query()
            ->with('user:id,name')
            ->where('results_count', 0)
            ->latest('id')
            ->limit($limit)
            ->get();
    }

    /**
     * Most frequent failed queries with occurrence counts.
     *
     * @return array<int, array{query: string, hits: int}>
     */
    public function topFailedQueries(int $limit = 20): array
    {
        return KbSearch::query()
            ->selectRaw('query, COUNT(*) as hits')
            ->where('results_count', 0)
            ->groupBy('query')
            ->orderByDesc('hits')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => ['query' => $row->query, 'hits' => (int) $row->hits])
            ->all();
    }

    /**
     * AI answer feedback breakdown (helpful / not_helpful / unrated).
     *
     * @return array{helpful: int, not_helpful: int, unrated: int}
     */
    public function aiFeedbackBreakdown(): array
    {
        return [
            'helpful' => AiQuery::where('feedback', 'helpful')->count(),
            'not_helpful' => AiQuery::where('feedback', 'not_helpful')->count(),
            'unrated' => AiQuery::whereNull('feedback')->count(),
        ];
    }

    /**
     * Recent negatively rated AI answers for review.
     */
    public function negativeAiAnswers(int $limit = 50): Collection
    {
        return AiQuery::query()
            ->with(['user:id,name', 'ticket:id,uid,subject'])
            ->where('feedback', 'not_helpful')
            ->latest('id')
            ->limit($limit)
            ->get();
    }

    public function searchVolume(int $days = 30): int
    {
        return KbSearch::where('created_at', '>=', now()->subDays($days))->count();
    }
}
