<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    protected function baseQuery(Request $request)
    {
        $userId = $request->user()->id;

        return Conversation::with(['user', 'lastMessage'])
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                    ->orWhere('assigned_to', $userId);
            });
    }

    protected function authorizeConversation(Request $request, Conversation $conversation): void
    {
        $userId = $request->user()->id;

        abort_unless(
            $conversation->user_id === $userId
                || $conversation->assigned_to === $userId
                || $request->user()->hasRole(['admin', 'manager', 'agent']),
            403,
            'You are not authorized to access this conversation.'
        );
    }

    public function index(Request $request): JsonResponse
    {
        $conversations = $this->baseQuery($request)
            ->latest('last_message_at')
            ->paginate(25);

        return response()->json([
            'success' => true,
            'data' => $conversations,
            'message' => 'Conversations retrieved.',
        ]);
    }

    public function show(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorizeConversation($request, $conversation);

        $messages = $conversation->messages()
            ->with('user')
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'conversation' => $conversation->load(['user', 'assignedTo']),
                'messages' => $messages,
            ],
            'message' => 'Conversation retrieved.',
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate(['body' => 'required|string|max:10000']);

        $conversation = Conversation::firstOrCreate(
            ['user_id' => $request->user()->id, 'status' => 'open'],
            ['last_message_at' => now()]
        );

        $message = ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'user_id' => $request->user()->id,
            'body' => $request->body,
            'type' => 'text',
        ]);

        $conversation->update(['last_message_at' => now()]);

        return response()->json([
            'success' => true,
            'data' => $message->load('user'),
            'message' => 'Message sent.',
        ], 201);
    }

    public function message(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorizeConversation($request, $conversation);

        $request->validate(['body' => 'required|string|max:10000']);

        $message = $conversation->messages()->create([
            'user_id' => $request->user()->id,
            'body' => $request->body,
            'type' => 'text',
        ]);

        $conversation->update(['last_message_at' => now()]);

        return response()->json([
            'success' => true,
            'data' => $message->load('user'),
            'message' => 'Message sent.',
        ], 201);
    }
}
