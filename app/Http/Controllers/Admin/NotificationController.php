<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        return view('admin.notifications.index', [
            'notifications' => $this->forUserQuery()->latest()->paginate(30),
        ]);
    }

    public function bell(): JsonResponse
    {
        $unreadCount = $this->forUserQuery()->whereNull('read_at')->count();

        $items = $this->forUserQuery()
            ->latest()
            ->limit(8)
            ->get()
            ->map(function ($n) {
                $data = is_array($n->data) ? $n->data : (array) json_decode($n->data ?? '[]', true);

                return [
                    'id' => $n->id,
                    'title' => $data['title'] ?? class_basename($n->type),
                    'body' => str($data['body'] ?? $data['message'] ?? '')->limit(80),
                    'read_at' => $n->read_at,
                    'action_url' => $data['url'] ?? $data['action_url'] ?? null,
                    'created_at' => $n->created_at?->diffForHumans(),
                ];
            });

        return response()->json([
            'unread_count' => $unreadCount,
            'items' => $items,
        ]);
    }

    public function markRead(): JsonResponse
    {
        $this->forUserQuery()->whereNull('read_at')->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function destroy(Notification $notification): RedirectResponse
    {
        abort_unless($notification->notifiable_id === auth()->id() && $notification->notifiable_type === User::class, 403);
        $notification->delete();

        return back()->with('success', 'Notification deleted.');
    }

    private function forUserQuery()
    {
        return Notification::where('notifiable_type', User::class)
            ->where('notifiable_id', auth()->id());
    }
}
