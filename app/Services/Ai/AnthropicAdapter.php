<?php

namespace App\Services\Ai;

use App\Models\AiProvider;
use App\Models\AiProviderModel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AnthropicAdapter implements AiAdapterInterface
{
    public function send(AiProvider $provider, AiProviderModel $model, array $request): array
    {
        try {
            $systemMessages = [];
            $chatMessages = [];

            foreach ($request['messages'] as $message) {
                if ($message['role'] === 'system') {
                    $systemMessages[] = $message['content'];
                } else {
                    $chatMessages[] = [
                        'role' => $message['role'],
                        'content' => $message['content'],
                    ];
                }
            }

            $body = [
                'model' => $request['model'],
                'max_tokens' => $request['options']['max_tokens'] ?? $model->max_tokens ?? 4096,
                'messages' => $chatMessages,
                'temperature' => $request['options']['temperature'] ?? 0.7,
            ];

            if ($systemMessages) {
                $body['system'] = implode("\n", $systemMessages);
            }

            $response = Http::timeout(60)->retry(1, 500)
                ->withHeaders([
                    'x-api-key' => $provider->decrypted_api_key,
                    'anthropic-version' => '2023-06-01',
                    'Content-Type' => 'application/json',
                ])
                ->post(rtrim($provider->base_url, '/').'/v1/messages', $body);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'content' => $data['content'][0]['text'] ?? '',
                    'input_tokens' => $data['usage']['input_tokens'] ?? 0,
                    'output_tokens' => $data['usage']['output_tokens'] ?? 0,
                    'model' => $data['model'] ?? $request['model'],
                ];
            }

            Log::error('Anthropic API error', [
                'provider' => $provider->name,
                'status' => $response->status(),
                'body' => substr($response->body(), 0, 2000),
            ]);

            return ['error' => "Provider request failed (HTTP {$response->status()})."];
        } catch (\Exception $e) {
            Log::error('Anthropic request failed', [
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
                ->withHeaders([
                    'x-api-key' => $provider->decrypted_api_key,
                    'anthropic-version' => '2023-06-01',
                ])
                ->get(rtrim($provider->base_url, '/'));

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
        $models = [
            'claude-3-5-sonnet-20241022',
            'claude-3-5-sonnet-20240620',
            'claude-3-5-haiku-20241022',
            'claude-3-opus-20240229',
            'claude-3-sonnet-20240229',
            'claude-3-haiku-20240307',
        ];

        return ['success' => true, 'models' => $models];
    }
}
