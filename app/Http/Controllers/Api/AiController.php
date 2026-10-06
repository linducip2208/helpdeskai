<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiController extends Controller
{
    protected function ensureStaff(Request $request): void
    {
        abort_unless(
            $request->user()->hasRole(['admin', 'manager', 'agent']),
            403,
            'AI tools are available to support staff only.'
        );
    }

    protected function envelope(array $result, string $okMessage): JsonResponse
    {
        $failed = isset($result['error']);

        return response()->json([
            'success' => ! $failed,
            'data' => $failed ? null : $result,
            'message' => $failed ? 'AI provider unavailable. Please try again later.' : $okMessage,
        ], $failed ? 503 : 200);
    }

    public function classify(Request $request): JsonResponse
    {
        $this->ensureStaff($request);

        $request->validate([
            'subject' => 'required|string|max:500',
            'body' => 'required|string|max:5000',
        ]);

        $result = app(AiService::class)->dispatch('ticket.classify', [
            ['role' => 'system', 'content' => 'You classify support tickets. Return JSON: {"department":"...", "category":"...", "priority":"low|medium|high|urgent", "confidence": 0.0-1.0}'],
            ['role' => 'user', 'content' => "Subject: {$request->subject}\nBody: {$request->body}"],
        ]);

        return $this->envelope($result, 'Ticket classified.');
    }

    public function suggest(Request $request): JsonResponse
    {
        $this->ensureStaff($request);

        $request->validate([
            'ticket_subject' => 'required|string|max:500',
            'ticket_body' => 'required|string|max:5000',
            'tone' => 'nullable|string|in:professional,friendly,empathetic,brief',
        ]);

        $tone = $request->tone ?? 'professional';

        $result = app(AiService::class)->dispatch('ticket.suggest', [
            ['role' => 'system', 'content' => "You suggest responses for support agents. Use a {$tone} tone. Return only the suggested response text."],
            ['role' => 'user', 'content' => "Ticket subject: {$request->ticket_subject}\nTicket body: {$request->ticket_body}\nProvide a helpful response."],
        ]);

        return $this->envelope($result, 'Suggestion generated.');
    }

    public function sentiment(Request $request): JsonResponse
    {
        $this->ensureStaff($request);

        $request->validate([
            'text' => 'required|string|max:5000',
        ]);

        $result = app(AiService::class)->dispatch('ticket.sentiment', [
            ['role' => 'system', 'content' => 'Analyze the sentiment of support messages. Return JSON: {"sentiment":"positive|neutral|negative","score":0.0-1.0,"urgency":0-10}'],
            ['role' => 'user', 'content' => $request->text],
        ]);

        return $this->envelope($result, 'Sentiment analyzed.');
    }
}
