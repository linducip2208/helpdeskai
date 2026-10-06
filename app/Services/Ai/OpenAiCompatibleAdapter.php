<?php

namespace App\Services\Ai;

use App\Models\AiProvider;
use App\Models\AiProviderModel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAiCompatibleAdapter implements AiAdapterInterface
{
    public function send(AiProvider $provider, AiProviderModel $model, array $request): array
    {
        try {
            $response = Http::timeout(120)
                ->withHeaders($this->buildHeaders($provider))
                ->post(rtrim($provider->base_url, '/') . '/v1/chat/completions', [
                    'model' => $request['model'],
                    'messages' => $request['messages'],
                    'max_tokens' => $request['options']['max_tokens'] ?? $model->max_tokens ?? 4096,
                    'temperature' => $request['options']['temperature'] ?? 0.7,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'content' => $data['choices'][0]['message']['content'] ?? '',
                    'input_tokens' => $data['usage']['prompt_tokens'] ?? 0,
                    'output_tokens' => $data['usage']['completion_tokens'] ?? 0,
                    'model' => $data['model'] ?? $request['model'],
                ];
            }

            Log::error('OpenAI-compatible API error', [
                'provider' => $provider->name,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return ['error' => "API error: {$response->status()} - {$response->body()}"];
        } catch (\Exception $e) {
            Log::error('OpenAI-compatible request failed', [
                'provider' => $provider->name,
                'error' => $e->getMessage(),
            ]);

            return ['error' => $e->getMessage()];
        }
    }

    public function testConnection(AiProvider $provider): array
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders($this->buildHeaders($provider))
                ->get(rtrim($provider->base_url, '/') . '/v1/models');

            if ($response->successful()) {
                return ['success' => true, 'message' => 'Connection successful.'];
            }

            return ['success' => false, 'message' => "HTTP {$response->status()}: {$response->body()}"];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function listModels(AiProvider $provider): array
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders($this->buildHeaders($provider))
                ->get(rtrim($provider->base_url, '/') . '/v1/models');

            if ($response->successful()) {
                $models = collect($response->json('data', []))
                    ->pluck('id')
                    ->toArray();

                return ['success' => true, 'models' => $models];
            }

            return ['success' => false, 'message' => "HTTP {$response->status()}"];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function buildHeaders(AiProvider $provider): array
    {
        $headers = [
            'Authorization' => 'Bearer ' . $provider->decrypted_api_key,
            'Content-Type' => 'application/json',
        ];

        if (! empty($provider->extra_headers)) {
            $headers = array_merge($headers, $provider->extra_headers);
        }

        return $headers;
    }
}
