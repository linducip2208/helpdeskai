<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyAuth
{
    protected const LEVELS = [
        'read' => 1,
        'read-write' => 2,
        'full' => 3,
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken() ?? $request->header('X-API-Key');

        if (! $token) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.', 'errors' => []], 401);
        }

        $apiKey = ApiKey::where('key', hash('sha256', $token))
            ->where('is_active', true)
            ->first();

        if (! $apiKey || ! $apiKey->user) {
            return response()->json(['success' => false, 'message' => 'Invalid API key.', 'errors' => []], 401);
        }

        if ($apiKey->expires_at && $apiKey->expires_at->isPast()) {
            return response()->json(['success' => false, 'message' => 'API key expired.', 'errors' => []], 401);
        }

        if (! $this->hasScope($apiKey, $request->method())) {
            return response()->json(['success' => false, 'message' => 'API key scope insufficient for this action.', 'errors' => []], 403);
        }

        $apiKey->increment('usage_count');
        $apiKey->update(['last_used_at' => now()]);

        Auth::setUser($apiKey->user);
        $request->attributes->set('api_key', $apiKey);

        return $next($request);
    }

    protected function hasScope(ApiKey $apiKey, string $method): bool
    {
        $required = match (strtoupper($method)) {
            'GET', 'HEAD', 'OPTIONS' => 'read',
            'DELETE' => 'full',
            default => 'read-write',
        };

        return (self::LEVELS[$apiKey->permissions] ?? 0) >= self::LEVELS[$required];
    }
}
