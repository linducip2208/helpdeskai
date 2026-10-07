<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationPreference;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationPreferenceController extends Controller
{
    public function index(Request $request): View
    {
        $prefs = NotificationPreference::where('user_id', $request->user()->id)
            ->get()
            ->keyBy('event');

        return view('profile.notifications', [
            'events' => NotificationPreference::EVENTS,
            'channels' => NotificationPreference::CHANNELS,
            'prefs' => $prefs,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'preferences' => ['nullable', 'array'],
            'preferences.*' => ['nullable', 'array'],
            'preferences.*.*' => ['string', 'in:db,email,push'],
        ]);

        $input = $validated['preferences'] ?? [];

        foreach (NotificationPreference::EVENTS as $event) {
            $channels = array_values(array_intersect(
                NotificationPreference::CHANNELS,
                (array) ($input[$event] ?? [])
            ));

            NotificationPreference::updateOrCreate(
                ['user_id' => $request->user()->id, 'event' => $event],
                ['channels' => $channels]
            );
        }

        return back()->with('success', __('Notification preferences saved.'));
    }
}
