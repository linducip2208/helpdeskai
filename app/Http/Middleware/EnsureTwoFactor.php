<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactor
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->two_factor_confirmed_at && ! $request->session()->get('2fa_passed')) {
            $whitelist = ['two-factor.challenge', 'two-factor.verify', 'logout'];
            $current = optional($request->route())->getName();

            if (! in_array($current, $whitelist, true)) {
                return redirect()->route('two-factor.challenge');
            }
        }

        return $next($request);
    }
}
