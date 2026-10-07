<?php

namespace App\Services\Channels;

use App\Models\Department;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketService;
use App\Services\WebhookService;
use Spatie\Permission\Models\Role;

class ChannelManager
{
    /**
     * @return array<string, class-string<ChannelInterface>>
     */
    public static function drivers(): array
    {
        return [
            'whatsapp' => WhatsAppChannel::class,
            'telegram' => TelegramChannel::class,
        ];
    }

    public function driver(string $channel): ?ChannelInterface
    {
        $class = self::drivers()[$channel] ?? null;

        return $class ? app($class) : null;
    }

    /**
     * @return array<string, array{enabled: bool}>
     */
    public function statuses(): array
    {
        $out = [];
        foreach (self::drivers() as $name => $class) {
            $out[$name] = ['enabled' => app($class)->enabled()];
        }

        return $out;
    }

    public function resolveUser(string $channel, string $senderId, string $name): User
    {
        $column = $channel === 'telegram' ? 'telegram_id' : 'whatsapp_id';

        $user = User::where($column, $senderId)->first();

        if ($user) {
            return $user;
        }

        $user = User::create([
            'name' => mb_substr($name !== '' ? $name : $senderId, 0, 255),
            'email' => $senderId.'@'.$channel.'.local',
            'password' => bcrypt(str()->random(32)),
        ]);

        $user->forceFill([$column => $senderId, 'role' => 'customer'])->saveQuietly();

        if (Role::where('name', 'customer')->exists()) {
            $user->assignRole('customer');
        }

        app(WebhookService::class)->dispatchGeneric('customer.created', [
            'customer' => ['id' => $user->id, 'name' => $user->name, 'channel' => $channel],
        ], 'customer-'.$user->id);

        return $user;
    }

    /**
     * Thread onto the sender's latest open ticket, otherwise open a new one.
     */
    public function ingest(TicketData $data): Ticket
    {
        $user = $this->resolveUser($data->channel, $data->senderId, $data->senderName);
        $tickets = app(TicketService::class);

        $open = Ticket::where('user_id', $user->id)
            ->whereIn('status', ['open', 'in_progress', 'waiting', 'answered'])
            ->latest()
            ->first();

        if ($open) {
            $tickets->addReply($open, $user->id, $data->body, false);

            return $open->fresh();
        }

        return $tickets->createTicket([
            'user_id' => $user->id,
            'subject' => $data->subject !== '' ? mb_substr($data->subject, 0, 255) : mb_substr($data->body, 0, 120),
            'body' => $data->body,
            'department_id' => Department::where('is_active', true)->orderBy('id')->value('id'),
            'priority' => 'medium',
            'source' => 'chat',
        ]);
    }
}
