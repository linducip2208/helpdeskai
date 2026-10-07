<?php

namespace App\Services;

use App\Jobs\SendWebPushNotification;
use App\Models\Notification;
use App\Models\PushSubscription;
use App\Models\User;

class AppNotificationService
{
    public function __construct(private WebPushService $webPush) {}

    public function notify(User $user, string $type, string $title, string $body, ?string $url = null, array $extra = []): Notification
    {
        $notification = Notification::create([
            'type' => $type,
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => array_merge([
                'title' => $title,
                'body' => $body,
                'url' => $url,
            ], $extra),
        ]);

        $this->pushIfSubscribed($user, $title, $body, $url);

        return $notification;
    }

    public function notifyMany(iterable $users, string $type, string $title, string $body, ?string $url = null, array $extra = []): int
    {
        $count = 0;
        foreach ($users as $user) {
            if ($user instanceof User) {
                $this->notify($user, $type, $title, $body, $url, $extra);
                $count++;
            }
        }

        return $count;
    }

    private function pushIfSubscribed(User $user, string $title, string $body, ?string $url): void
    {
        $ids = PushSubscription::where('user_id', $user->id)->pluck('id');

        if ($ids->isEmpty()) {
            return;
        }

        $payload = [
            'title' => $title,
            'body' => $body,
            'url' => $url,
            'icon' => '/icons/icon-192.svg',
        ];

        foreach ($ids as $id) {
            SendWebPushNotification::dispatch($id, $payload);
        }
    }
}
