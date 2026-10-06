<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PushSubscription;
use App\Services\WebPushService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PushSubscriptionController extends Controller
{
    public function __construct(private WebPushService $webPush) {}

    public function index(): View
    {
        return view('admin.push-subscriptions.index', [
            'subscriptions' => PushSubscription::with('user:id,name,email')
                ->latest()
                ->paginate(30),
            'totals' => [
                'all' => PushSubscription::count(),
                'unique_users' => PushSubscription::distinct('user_id')->count('user_id'),
            ],
        ]);
    }

    public function destroy(PushSubscription $pushSubscription): RedirectResponse
    {
        $pushSubscription->delete();

        return back()->with('success', 'Subscription removed.');
    }

    public function broadcast(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:120',
            'body' => 'required|string|max:300',
            'url' => 'nullable|url',
            'target' => 'required|in:all,user',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $query = PushSubscription::query();
        if ($data['target'] === 'user' && ! empty($data['user_id'])) {
            $query->where('user_id', $data['user_id']);
        }

        $payload = [
            'title' => $data['title'],
            'body' => $data['body'],
            'url' => $data['url'] ?? null,
            'icon' => '/icons/icon-192.svg',
        ];

        $sent = 0;
        $failed = 0;
        $query->chunkById(100, function ($chunk) use (&$sent, &$failed, $payload) {
            foreach ($chunk as $sub) {
                if ($this->webPush->send($sub, $payload)) {
                    $sent++;
                } else {
                    $failed++;
                }
            }
        });

        if (! config('webpush.vapid.public_key') || ! config('webpush.vapid.private_key')) {
            return back()->with('error', 'VAPID keys belum di-generate. Jalankan: php artisan webpush:vapid-keys, lalu paste ke .env (VAPID_PUBLIC_KEY, VAPID_PRIVATE_KEY).');
        }

        return back()->with('success', "Push terkirim ke {$sent} subscriber. Failed: {$failed}.");
    }
}
