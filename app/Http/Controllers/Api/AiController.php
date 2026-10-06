<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiController extends Controller
{
    public function classify(Request $request): JsonResponse
    {
        $request->validate([
            'subject' => 'required|string|max:500',
            'body' => 'required|string|max:5000',
        ]);

        $result = app(AiService::class)->dispatch('ticket.classify', [
            ['role' => 'system', 'content' => 'You classify support tickets. Return JSON: {"department":"...", "category":"...", "priority":"low|medium|high|urgent", "confidence": 0.0-1.0}'],
            ['role' => 'user', 'content' => "Subject: {$request->subject}\nBody: {$request->body}"],
        ]);

        return response()->json(['data' => $result]);
    }

    public function suggest(Request $request): JsonResponse
    {
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

        return response()->json(['data' => $result]);
    }

    public function sentiment(Request $request): JsonResponse
    {
        $request->validate([
            'text' => 'required|string|max:5000',
        ]);

        $result = app(AiService::class)->dispatch('ticket.sentiment', [
            ['role' => 'system', 'content' => 'Analyze the sentiment of support messages. Return JSON: {"sentiment":"positive|neutral|negative","score":0.0-1.0,"urgency":0-10}'],
            ['role' => 'user', 'content' => $request->text],
        ]);

        return response()->json(['data' => $result]);
    }
}
