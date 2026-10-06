<?php

namespace App\Services;

use App\Models\AiFeatureConfig;
use App\Models\AiProvider;
use App\Models\AiProviderModel;
use App\Models\AiUsageLog;
use App\Services\Ai\AiAdapterInterface;
use App\Services\Ai\AnthropicAdapter;
use App\Services\Ai\GeminiAdapter;
use App\Services\Ai\OpenAiCompatibleAdapter;
use Illuminate\Support\Facades\Log;

class AiService
{
    public function dispatch(
        string $featureKey,
        array $messages,
        array $options = []
    ): array {
        $config = AiFeatureConfig::with(['provider', 'model'])
            ->where('feature_key', $featureKey)
            ->where('is_enabled', true)
            ->first();

        if (! $config || ! $config->provider || ! $config->model) {
            Log::warning("AI feature '{$featureKey}' not configured or disabled.");

            return ['error' => 'Feature not configured.'];
        }

        $provider = $config->provider;
        $model = $config->model;
        $adapter = $this->resolveAdapter($provider->api_format);

        if (! $adapter) {
            Log::warning("No adapter for API format: {$provider->api_format}");

            return ['error' => 'No adapter available for provider format.'];
        }

        $startTime = microtime(true);
        $result = $adapter->send($provider, $model, [
            'model' => $model->model_id,
            'messages' => $messages,
            'options' => array_merge($config->options ?? [], $options),
        ]);
        $durationMs = (int) round((microtime(true) - $startTime) * 1000);

        $cost = $this->estimateCost(
            $model->id,
            $result['input_tokens'] ?? 0,
            $result['output_tokens'] ?? 0
        );

        AiUsageLog::create([
            'provider_id' => $provider->id,
            'model_id' => $model->id,
            'feature_key' => $featureKey,
            'input_tokens' => $result['input_tokens'] ?? 0,
            'output_tokens' => $result['output_tokens'] ?? 0,
            'cost_estimated' => $cost,
            'latency_ms' => $durationMs,
            'success' => empty($result['error']),
            'error_message' => $result['error'] ?? null,
        ]);

        if (! empty($result['error'])) {
            Log::error("AI dispatch error for '{$featureKey}': {$result['error']}");
        }

        return $result;
    }

    public function testConnection(AiProvider $provider): array
    {
        $adapter = $this->resolveAdapter($provider->api_format);

        if (! $adapter) {
            return ['success' => false, 'message' => 'No adapter for this API format.'];
        }

        $result = $adapter->testConnection($provider);

        $provider->update([
            'last_tested_at' => now(),
            'last_test_status' => $result['success'] ? 'success' : 'failed',
        ]);

        return $result;
    }

    public function listModels(AiProvider $provider): array
    {
        $adapter = $this->resolveAdapter($provider->api_format);

        if (! $adapter) {
            return ['success' => false, 'message' => 'No adapter for this API format.'];
        }

        return $adapter->listModels($provider);
    }

    public function estimateCost(
        int $modelId,
        int $inputTokens,
        int $outputTokens
    ): float {
        $model = AiProviderModel::find($modelId);

        if (! $model || (! $model->cost_input_per_1m && ! $model->cost_output_per_1m)) {
            return 0;
        }

        $inputCost = ($inputTokens / 1_000_000) * ($model->cost_input_per_1m ?? 0);
        $outputCost = ($outputTokens / 1_000_000) * ($model->cost_output_per_1m ?? 0);

        return round($inputCost + $outputCost, 6);
    }

    protected function resolveAdapter(string $apiFormat): ?AiAdapterInterface
    {
        return match ($apiFormat) {
            'openai_compatible' => app(OpenAiCompatibleAdapter::class),
            'anthropic' => app(AnthropicAdapter::class),
            'gemini' => app(GeminiAdapter::class),
            default => null,
        };
    }
}
