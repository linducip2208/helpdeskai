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
            Setting::set($key, $value ?? '');
        }

        return redirect()->back()->with('success', 'Settings saved.');
    }
}
