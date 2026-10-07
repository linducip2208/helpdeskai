<?php

namespace App\Services;

use App\Models\QaScore;
use App\Models\Setting;
use App\Models\TicketReply;
use Illuminate\Support\Facades\Log;

class QaService
{
    public const DIMENSIONS = ['accuracy', 'completeness', 'tone', 'empathy', 'policy_compliance', 'knowledge_correctness'];

    public function enabled(): bool
    {
        return (bool) Setting::get('ai.qa_enabled', false);
    }

    public function scoreReply(TicketReply $reply): ?QaScore
    {
        if (! $this->enabled()) {
            return null;
        }

        $existing = QaScore::where('reply_id', $reply->id)->first();
        if ($existing) {
            return $existing;
        }

        $ticket = $reply->ticket;

        $messages = [
            [
                'role' => 'system',
                'content' => 'You are a support QA reviewer. Rate the agent reply 1-5 on: accuracy, completeness, tone, empathy, policy_compliance, knowledge_correctness. Output ONLY compact JSON: {"accuracy":1-5,"completeness":1-5,"tone":1-5,"empathy":1-5,"policy_compliance":1-5,"knowledge_correctness":1-5,"feedback":"<one sentence>"}. Scores are advisory only, never punitive.',
            ],
            [
                'role' => 'user',
                'content' => "Customer issue: {$ticket->subject}\n\n{$ticket->body}\n\nAgent reply:\n{$reply->body}",
            ],
        ];

        try {
            $result = app(AiService::class)->dispatch('ticket.qa_score', $messages, ['temperature' => 0]);
        } catch (\Throwable $e) {
            Log::warning('QA scoring dispatch failed.', ['error' => $e->getMessage()]);

            return null;
        }

        if (empty($result['content'])) {
            return null;
        }

        $parsed = $this->extractJson($result['content']);

        if (! is_array($parsed)) {
            return null;
        }

        $scores = [];
        foreach (self::DIMENSIONS as $dim) {
            $value = $parsed[$dim] ?? null;
            $scores[$dim] = is_numeric($value) ? max(1, min(5, (int) $value)) : null;
        }

        $rated = array_filter($scores, fn ($v) => $v !== null);

        return QaScore::create([
            'reply_id' => $reply->id,
            'ticket_id' => $ticket->id,
            'accuracy' => $scores['accuracy'],
            'completeness' => $scores['completeness'],
            'tone' => $scores['tone'],
            'empathy' => $scores['empathy'],
            'policy_compliance' => $scores['policy_compliance'],
            'knowledge_correctness' => $scores['knowledge_correctness'],
            'overall' => $rated !== [] ? round(array_sum($rated) / count($rated), 2) : null,
            'feedback' => isset($parsed['feedback']) && is_string($parsed['feedback']) ? mb_substr($parsed['feedback'], 0, 1000) : null,
            'provider' => $result['provider'] ?? null,
            'model' => $result['model'] ?? null,
            'input_tokens' => (int) ($result['input_tokens'] ?? 0),
            'output_tokens' => (int) ($result['output_tokens'] ?? 0),
        ]);
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
