<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use App\Services\WebPushService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    public function vapidKey(): JsonResponse
    {
        return response()->json([
            'key' => config('webpush.vapid.public_key'),
        ]);
    }

    public function subscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => 'required|string|url',
            'keys.p256dh' => 'required|string',
            'keys.auth' => 'required|string',
        ]);

        $sub = PushSubscription::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'endpoint_hash' => hash('sha256', $validated['endpoint']),
            ],
            [
                'endpoint' => $validated['endpoint'],
                'p256dh' => $validated['keys']['p256dh'],
                'auth' => $validated['keys']['auth'],
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
            ],
        );

        return response()->json(['id' => $sub->id, 'ok' => true]);
    }

    public function unsubscribe(Request $request): JsonResponse
    {
        $request->validate(['endpoint' => 'required|string']);

        PushSubscription::where('user_id', $request->user()->id)
            ->where('endpoint_hash', hash('sha256', $request->endpoint))
            ->delete();

        return response()->json(['ok' => true]);
    }

    public function test(Request $request, WebPushService $push): JsonResponse
    {
        $subs = PushSubscription::where('user_id', $request->user()->id)->get();

        $sent = 0;
        foreach ($subs as $sub) {
            if ($push->send($sub, [
                'title' => 'HelpDesk AI test',
                'body' => 'Push notifications are working.',
                'url' => route('dashboard'),
            ])) {
                $sent++;
            }
        }

        return response()->json([
            'subscriptions' => $subs->count(),
            'sent' => $sent,
        ]);
    }
}
