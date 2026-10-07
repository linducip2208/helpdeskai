<?php

namespace App\Http\Controllers;

use App\Services\Channels\ChannelManager;
use App\Services\Channels\TelegramChannel;
use App\Services\Channels\TicketData;
use App\Services\Channels\WhatsAppChannel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChannelWebhookController extends Controller
{
    public function whatsappVerify(Request $request)
    {
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        if ($mode === 'subscribe' && $token !== null
            && hash_equals((string) config('services.whatsapp.verify_token'), (string) $token)) {
            return response($challenge, 200)->header('Content-Type', 'text/plain');
        }

        return response()->json(['success' => false, 'message' => 'Verification failed.'], 403);
    }

    public function whatsapp(Request $request, WhatsAppChannel $channel, ChannelManager $manager): JsonResponse
    {
        if (! $channel->validSignature($request->getContent(), $request->header('X-Hub-Signature-256'))) {
            return response()->json(['success' => false, 'message' => 'Invalid signature.'], 403);
        }

        $count = 0;
        foreach ($channel->parseWebhook($request->all()) as $message) {
            if (trim($message['body']) === '') {
                continue;
            }
            $manager->ingest(new TicketData(
                channel: 'whatsapp',
                senderId: $message['from'],
                senderName: $message['name'],
                senderContact: $message['from'],
                subject: mb_substr($message['body'], 0, 120),
                body: $message['body'],
                externalMessageId: $message['message_id'],
            ));
            $count++;
        }

        return response()->json(['success' => true, 'data' => ['ingested' => $count], 'message' => 'OK']);
    }

    public function telegram(Request $request, TelegramChannel $channel, ChannelManager $manager): JsonResponse
    {
        if (! $channel->validSecret($request->header('X-Telegram-Bot-Api-Secret-Token'))) {
            return response()->json(['success' => false, 'message' => 'Invalid secret.'], 403);
        }

        $count = 0;
        foreach ($channel->parseWebhook($request->all()) as $message) {
            if (trim($message['body']) === '') {
                continue;
            }
            if (str_starts_with(trim($message['body']), '/')) {
                continue;
            }
            $manager->ingest(new TicketData(
                channel: 'telegram',
                senderId: $message['from'],
                senderName: $message['name'],
                senderContact: $message['from'],
                subject: mb_substr($message['body'], 0, 120),
                body: $message['body'],
                externalMessageId: $message['message_id'],
            ));
            $count++;
        }

        return response()->json(['success' => true, 'data' => ['ingested' => $count], 'message' => 'OK']);
    }
}
