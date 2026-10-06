<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function switch(Request $request, string $locale): RedirectResponse
    {
        abort_unless(in_array($locale, ['en', 'id'], true), 404);

        session(['locale' => $locale]);

        return back(fallback: url('/'));
    }
}
