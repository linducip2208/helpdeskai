<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use App\Services\ActivityLogService;
use App\Services\WebhookService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class WebhookEndpointController extends Controller
{
    public function index(): View
    {
        return view('admin.webhooks.index', [
            'endpoints' => WebhookEndpoint::withCount(['deliveries as failed_deliveries' => fn ($q) => $q->where('status', 'failed')])->latest()->get(),
            'events' => WebhookEndpoint::EVENTS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:2048',
            'events' => 'required|array|min:1',
            'events.*' => 'in:'.implode(',', WebhookEndpoint::EVENTS),
            'timeout_seconds' => 'nullable|integer|min:3|max:60',
            'is_active' => 'boolean',
        ]);

        $endpoint = WebhookEndpoint::create([
            'name' => $validated['name'],
            'url' => $validated['url'],
            'events' => array_values($validated['events']),
            'timeout_seconds' => $validated['timeout_seconds'] ?? 10,
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);
        $endpoint->setSecret(Str::random(48));

        ActivityLogService::logCustom(auth()->id(), 'webhook_create', WebhookEndpoint::class, $endpoint->id, $endpoint->name);

        return back()->with('success', 'Webhook endpoint created.');
    }

    public function edit(WebhookEndpoint $webhook): View
    {
        return view('admin.webhooks.edit', [
            'webhook' => $webhook,
            'events' => WebhookEndpoint::EVENTS,
        ]);
    }

    public function update(Request $request, WebhookEndpoint $webhook): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:2048',
            'events' => 'required|array|min:1',
            'events.*' => 'in:'.implode(',', WebhookEndpoint::EVENTS),
            'timeout_seconds' => 'nullable|integer|min:3|max:60',
            'is_active' => 'boolean',
            'rotate_secret' => 'boolean',
        ]);

        $webhook->update([
            'name' => $validated['name'],
            'url' => $validated['url'],
            'events' => array_values($validated['events']),
            'timeout_seconds' => $validated['timeout_seconds'] ?? 10,
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        if (! empty($validated['rotate_secret'])) {
            $webhook->setSecret(Str::random(48));
        }

        ActivityLogService::logCustom(auth()->id(), 'webhook_update', WebhookEndpoint::class, $webhook->id, $webhook->name);

        return back()->with('success', 'Webhook endpoint updated.');
    }

    public function destroy(WebhookEndpoint $webhook): RedirectResponse
    {
        $name = $webhook->name;
        $id = $webhook->id;
        $webhook->delete();

        ActivityLogService::logCustom(auth()->id(), 'webhook_delete', WebhookEndpoint::class, $id, $name);

        return back()->with('success', 'Webhook endpoint deleted.');
    }

    public function test(WebhookEndpoint $webhook, WebhookService $webhooks): RedirectResponse
    {
        $result = $webhooks->sendTest($webhook);

        if ($result['ok']) {
            return back()->with('success', 'Test event delivered (HTTP '.$result['status'].').');
        }

        return back()->with('error', 'Test event failed: '.($result['status'] ? 'HTTP '.$result['status'].' — ' : '').substr((string) $result['body'], 0, 300));
    }

    public function deliveries(Request $request): View
    {
        $deliveries = WebhookDelivery::with('endpoint:id,name')
            ->when($request->endpoint_id, fn ($q) => $q->where('webhook_endpoint_id', $request->endpoint_id))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.webhooks.deliveries', [
            'deliveries' => $deliveries,
            'endpoints' => WebhookEndpoint::orderBy('name')->get(['id', 'name']),
            'filters' => $request->only(['endpoint_id', 'status']),
        ]);
    }
}
