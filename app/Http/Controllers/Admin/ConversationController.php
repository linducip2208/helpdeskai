<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConversationController extends Controller
{
    public function index(Request $request): View
    {
        $conversations = Conversation::with(['user', 'assignedTo'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->search, fn ($q) => $q->whereHas('user', fn ($q) => $q->where('name', 'like', "%{$request->search}%")))
            ->withCount('messages')
            ->latest('last_message_at')
            ->paginate($request->per_page ?? 25)
            ->withQueryString();

        return view('admin.conversations.index', [
            'conversations' => $conversations,
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    public function show(Conversation $conversation): View
    {
        $conversation->load(['user', 'assignedTo', 'messages.user']);

        return view('admin.conversations.show', [
            'conversation' => $conversation,
            'agents' => User::whereHas('roles', fn ($q) => $q->where('name', 'agent'))->get(['id', 'name']),
        ]);
    }

    public function reply(Request $request, Conversation $conversation): RedirectResponse
    {
        $validated = $request->validate([
            'body' => 'required|string',
        ]);

        $message = $conversation->messages()->create([
            'user_id' => auth()->id(),
            'body' => $validated['body'],
            'type' => 'agent',
        ]);

        $conversation->update([
            'last_message_at' => now(),
            'status' => 'open',
        ]);

        ActivityLogService::logCustom(auth()->id(), 'conversation_reply', ConversationMessage::class, $message->id, "Reply to conversation #{$conversation->id}");

        return back()->with('success', 'Reply sent.');
    }

    public function create(): View
    {
        return view('admin.conversations.create', [
            'customers' => User::whereHas('roles', fn ($q) => $q->where('name', 'customer'))->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'subject' => 'nullable|string|max:255',
            'body' => 'required|string',
        ]);

        $conversation = Conversation::create([
            'user_id' => $validated['user_id'],
            'subject' => $validated['subject'] ?? null,
            'status' => 'open',
            'last_message_at' => now(),
        ]);

        $conversation->messages()->create([
            'user_id' => $validated['user_id'],
            'body' => $validated['body'],
            'type' => 'customer',
        ]);

        ActivityLogService::logCustom(auth()->id(), 'conversation_create', Conversation::class, $conversation->id, "Conversation #{$conversation->id}");

        return redirect()->route('admin.conversations.show', $conversation)->with('success', 'Conversation created.');
    }

    public function destroy(Conversation $conversation): RedirectResponse
    {
        $id = $conversation->id;
        $conversation->delete();
        ActivityLogService::logCustom(auth()->id(), 'conversation_delete', Conversation::class, $id, "Conversation #{$id}");

        return redirect()->route('admin.conversations.index')->with('success', 'Conversation deleted.');
    }
}
