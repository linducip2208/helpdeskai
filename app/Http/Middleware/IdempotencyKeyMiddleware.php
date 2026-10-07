<?php

namespace App\Http\Middleware;

use App\Models\IdempotencyKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Safe retry for API mutations. Clients send `Idempotency-Key: <uuid>`.
 * A repeated request with the same key + endpoint returns the stored
 * response instead of executing the mutation again. Keys expire after 24h.
 */
class IdempotencyKeyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header('Idempotency-Key');

        if (! $key || ! $request->user() || ! preg_match('/^[A-Za-z0-9_-]{8,64}$/', $key)) {
            return $next($request);
        }

        $existing = IdempotencyKey::where('key', $key)
            ->where('user_id', $request->user()->id)
            ->where('method', $request->method())
            ->where('path', $request->path())
            ->first();

        if ($existing && $existing->isFresh() && $existing->response_code !== null) {
            return response()->json(
                array_merge((array) $existing->response_body, ['idempotent_replay' => true]),
                $existing->response_code
            );
        }

        if (! $existing) {
            $existing = IdempotencyKey::create([
                'key' => $key,
                'user_id' => $request->user()->id,
                'method' => $request->method(),
                'path' => $request->path(),
                'expires_at' => now()->addDay(),
            ]);
        }

        $response = $next($request);

        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
            $decoded = json_decode($response->getContent(), true);
            $existing->update([
                'response_code' => $response->getStatusCode(),
                'response_body' => is_array($decoded) ? $decoded : ['raw' => substr((string) $response->getContent(), 0, 4000)],
            ]);
        }

        return $response;
    }
}
