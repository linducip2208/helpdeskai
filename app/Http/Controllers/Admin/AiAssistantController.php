<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Ticket;
use App\Services\AiService;
use App\Services\KnowledgeRagService;
use App\Services\TicketService;
use Illuminate\Http\JsonResponse;

class AiAssistantController extends Controller
{
    public function summarize(Ticket $ticket): JsonResponse
    {
        $ticket->loadMissing(['replies.user']);

        $history = $ticket->replies->map(function ($reply) {
            $role = $reply->user?->hasRole('customer') ? 'Customer' : 'Agent';

            return "{$role}: {$reply->body}";
        })->implode("\n\n");

        $result = app(AiService::class)->dispatch('ticket.summarize', [
            [
                'role' => 'system',
                'content' => 'Summarize this support ticket for an agent. Output ONLY compact JSON: {"summary":"<2-3 sentences>","next_action":"<suggested next step>","confidence":<0.0-1.0 or null>}.',
            ],
            ['role' => 'user', 'content' => "Subject: {$ticket->subject}\n\nConversation:\n{$history}"],
        ], ['temperature' => 0.2]);

        if (empty($result['content'])) {
            return response()->json(['success' => false, 'message' => 'AI unavailable. Ticket data unchanged.', 'errors' => []], 503);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'raw' => $result['content'],
                'provider' => $result['provider'] ?? null,
                'model' => $result['model'] ?? null,
            ],
            'message' => 'Summary generated.',
        ]);
    }

    public function similar(Ticket $ticket): JsonResponse
    {
        $similar = Ticket::where('id', '!=', $ticket->id)
            ->when($ticket->department_id, fn ($q) => $q->where('department_id', $ticket->department_id))
            ->when($ticket->category_id, fn ($q) => $q->where('category_id', $ticket->category_id))
            ->with(['department:id,name'])
            ->latest()
            ->take(5)
            ->get(['id', 'uid', 'subject', 'status', 'priority', 'department_id', 'created_at']);

        return response()->json([
            'success' => true,
            'data' => $similar,
            'message' => 'Similar tickets retrieved.',
        ]);
    }

    public function recommend(Ticket $ticket, KnowledgeRagService $rag): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $rag->recommendForTicket($ticket),
            'message' => 'Knowledge recommendations retrieved.',
        ]);
    }

    public function kbAnswer(Ticket $ticket, KnowledgeRagService $rag): JsonResponse
    {
        $question = "Subject: {$ticket->subject}\n\n{$ticket->body}";
        $result = $rag->answer($question, 4, auth()->id(), $ticket->id);

        if ($result['answer'] === null) {
            return response()->json([
                'success' => false,
                'data' => ['sources' => $result['sources']],
                'message' => $result['error'] ?? 'No grounded answer available.',
                'errors' => [],
            ], 422);
        }

        $threshold = (float) Setting::get('ai.low_confidence_threshold', 0.5);
        if (($result['confidence'] ?? 1) < $threshold) {
            $already = $ticket->replies()->where('is_internal', true)
                ->where('body', 'like', 'AI low confidence, needs human review%')
                ->whereDate('created_at', today())
                ->exists();
            if (! $already) {
                app(TicketService::class)->addReply($ticket, auth()->id(), 'AI low confidence, needs human review.', true);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $result,
            'message' => 'Grounded answer generated.',
        ]);
    }
}
