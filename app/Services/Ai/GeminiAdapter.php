<?php

namespace App\Services\Ai;

use App\Models\AiProvider;
use App\Models\AiProviderModel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiAdapter implements AiAdapterInterface
{
    public function send(AiProvider $provider, AiProviderModel $model, array $request): array
    {
        try {
            $contents = [];
            $systemInstructions = '';

            foreach ($request['messages'] as $message) {
                if ($message['role'] === 'system') {
                    $systemInstructions .= $message['content'] . "\n";
                } else {
                    $role = $message['role'] === 'assistant' ? 'model' : 'user';
                    $contents[] = [
                        'role' => $role,
                        'parts' => [['text' => $message['content']]],
                    ];
                }
            }

            $body = ['contents' => $contents];

            if ($systemInstructions) {
                $body['system_instruction'] = [
                    'parts' => [['text' => trim($systemInstructions)]],
                ];
            }

            $url = rtrim($provider->base_url, '/') . '/v1beta/models/' . $request['model'] . ':generateContent';

            $response = Http::timeout(120)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->get($url, array_merge(['key' => $provider->decrypted_api_key], $body));

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'content' => $data['candidates'][0]['content']['parts'][0]['text'] ?? '',
                    'input_tokens' => $data['usageMetadata']['promptTokenCount'] ?? 0,
                    'output_tokens' => $data['usageMetadata']['candidatesTokenCount'] ?? 0,
                    'model' => $request['model'],
                ];
            }

            Log::error('Gemini API error', [
                'provider' => $provider->name,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return ['error' => "API error: {$response->status()} - {$response->body()}"];
        } catch (\Exception $e) {
            Log::error('Gemini request failed', [
                'provider' => $provider->name,
                'error' => $e->getMessage(),
            ]);

            return ['error' => $e->getMessage()];
        }
    }

    public function testConnection(AiProvider $provider): array
    {
        try {
            $url = rtrim($provider->base_url, '/') . '/v1beta/models';

            $response = Http::timeout(30)
                ->get($url, ['key' => $provider->decrypted_api_key]);

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
            $url = rtrim($provider->base_url, '/') . '/v1beta/models';

            $response = Http::timeout(30)
                ->get($url, ['key' => $provider->decrypted_api_key]);

            if ($response->successful()) {
                $models = collect($response->json('models', []))
                    ->filter(fn ($m) => str_starts_with($m['name'], 'models/'))
                    ->map(fn ($m) => str_replace('models/', '', $m['name']))
                    ->values()
                    ->toArray();

                return ['success' => true, 'models' => $models];
            }

            return ['success' => false, 'message' => "HTTP {$response->status()}"];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
