<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $conversations = Conversation::with(['user', 'lastMessage'])
            ->where('user_id', $request->user()->id)
            ->orWhere('assigned_to', $request->user()->id)
            ->latest('last_message_at')
            ->paginate(25);

        return response()->json($conversations);
    }

    public function show(Conversation $conversation): JsonResponse
    {
        $messages = $conversation->messages()
            ->with('user')
            ->orderBy('created_at')
            ->get();

        return response()->json(['data' => [
            'conversation' => $conversation->load(['user', 'assignedTo']),
            'messages' => $messages,
        ]]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate(['body' => 'required|string']);

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

        return response()->json(['data' => $message->load('user')], 201);
    }
}
