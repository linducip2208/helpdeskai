<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConversationController extends Controller
{
    public function index(): View
    {
        $conversations = Conversation::with(['user', 'messages' => fn($q) => $q->latest()->limit(1)])
            ->where('user_id', auth()->id())
            ->latest('last_message_at')
            ->get();

        return view('user.conversations.index', ['conversations' => $conversations]);
    }

    public function show(Conversation $conversation): View
    {
        $messages = $conversation->messages()
            ->with('user')
            ->orderBy('created_at')
            ->get();

        return view('user.conversations.show', [
            'conversation' => $conversation->load('user'),
            'messages' => $messages,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $conversation = Conversation::firstOrCreate(
            ['user_id' => auth()->id(), 'status' => 'open'],
            ['last_message_at' => now()]
        );

        return redirect()->route('conversations.show', $conversation);
    }

    public function message(Request $request, Conversation $conversation): RedirectResponse
    {
        $request->validate(['body' => 'required|string']);

        ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'user_id' => auth()->id(),
            'body' => $request->body,
            'type' => 'text',
        ]);

        $conversation->update(['last_message_at' => now()]);

        return redirect()->back();
    }
}
