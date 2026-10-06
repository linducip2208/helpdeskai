<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authenticate API requests via Sanctum token OR scoped API key.
 *
 * Sanctum personal tokens contain a "|" separator ("id|secret"),
 * while API keys are plain hex strings sent as Bearer or X-API-Key.
 */
class ApiAuthenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken() ?? $request->header('X-API-Key');

        if ($token && ! str_contains($token, '|')) {
            return app(ApiKeyAuth::class)->handle($request, $next);
        }

        return app(Authenticate::class)->handle($request, $next, 'sanctum');
    }
}
