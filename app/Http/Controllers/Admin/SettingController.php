<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        $settings = (object) Setting::pluck('value', 'key')->all();

        return view('admin.settings.index', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        foreach ($request->except('_token', '_method') as $key => $value) {
            if (! is_string($key) || ! preg_match('/^[A-Za-z0-9_.]+$/', $key)) {
                continue;
            }
            Setting::set($key, $value ?? '');
        }

        foreach (['notify_new_ticket', 'notify_ticket_reply', 'notify_sla_breach', 'ai.enabled', 'ai.process_ticket_content', 'ai.qa_enabled'] as $booleanKey) {
            if (! $request->has($booleanKey)) {
                Setting::set($booleanKey, false);
            }
        }

        return redirect()->back()->with('success', 'Settings saved.');
    }
}
