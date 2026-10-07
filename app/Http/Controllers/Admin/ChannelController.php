<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Channels\ChannelManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChannelController extends Controller
{
    public function index(ChannelManager $channels): View
    {
        return view('admin.channels.index', [
            'channels' => $channels->statuses(),
            'webhookUrls' => [
                'whatsapp_verify' => url('/webhooks/whatsapp'),
                'whatsapp' => url('/webhooks/whatsapp'),
                'telegram' => url('/webhooks/telegram'),
            ],
        ]);
    }

    public function test(Request $request, ChannelManager $channels): RedirectResponse
    {
        $validated = $request->validate([
            'channel' => 'required|string|in:whatsapp,telegram',
            'recipient' => 'required|string|max:64',
            'message' => 'required|string|max:500',
        ]);

        $driver = $channels->driver($validated['channel']);
        abort_unless($driver && $driver->enabled(), 422, 'Channel is not configured.');

        $id = $driver->send($validated['recipient'], $validated['message']);

        if ($id === null) {
            return back()->with('error', 'Test message failed to send. Check credentials and logs.');
        }

        return back()->with('success', 'Test message sent.');
    }
}
