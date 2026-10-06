<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $apiKey = \App\Models\ApiKey::where('key', hash('sha256', explode('|', $token)[1] ?? ''))
            ->where('is_active', true)
            ->first();

        if (! $apiKey) {
            return response()->json(['message' => 'Invalid API key.'], 401);
        }

        if ($apiKey->expires_at && $apiKey->expires_at->isPast()) {
            return response()->json(['message' => 'API key expired.'], 401);
        }

        $apiKey->increment('usage_count');
        $apiKey->update(['last_used_at' => now()]);

        return $next($request);
    }
}
