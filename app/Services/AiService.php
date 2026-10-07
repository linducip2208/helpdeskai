<?php

namespace App\Services;

use App\Models\AiFeatureConfig;
use App\Models\AiProvider;
use App\Models\AiProviderModel;
use App\Models\AiUsageLog;
use App\Models\Setting;
use App\Services\Ai\AiAdapterInterface;
use App\Services\Ai\AnthropicAdapter;
use App\Services\Ai\GeminiAdapter;
use App\Services\Ai\OpenAiCompatibleAdapter;
use Illuminate\Support\Facades\Log;

class AiService
{
    /**
     * Features whose payloads may contain ticket/customer content.
     */
    protected const CONTENT_FEATURES = [
        'ticket.classify',
        'ticket.sentiment',
        'ticket.suggest',
        'ticket.summarize',
        'knowledge.answer',
    ];

    public function __construct(protected AiBudgetService $budgets) {}

    /**
     * Dispatch an AI feature with provider failover, budget enforcement,
     * per-attempt usage logging, and privacy policy checks.
     *
     * Never throws: returns ['error' => ...] on any failure so ticketing
     * keeps working when AI is unavailable.
     */
    public function dispatch(
        string $featureKey,
        array $messages,
        array $options = []
    ): array {
        if (! $this->aiEnabled()) {
            return ['error' => 'AI is disabled by policy.'];
        }

        if (in_array($featureKey, self::CONTENT_FEATURES, true) && ! $this->contentProcessingAllowed()) {
            return ['error' => 'AI processing of ticket content is disabled by policy.'];
        }

        $config = AiFeatureConfig::with(['provider', 'model'])
            ->where('feature_key', $featureKey)
            ->where('is_enabled', true)
            ->first();

        if (! $config) {
            Log::warning("AI feature '{$featureKey}' not configured or disabled.");

            return ['error' => 'Feature not configured.'];
        }

        $candidates = $this->candidates($config);

        if ($candidates === []) {
            return ['error' => 'No active AI provider available.'];
        }

        $attempts = 0;
        $lastError = 'No active AI provider available.';
        $fallbackFrom = null;

        foreach ($candidates as [$provider, $model]) {
            $budget = $this->budgets->check($featureKey, $provider->id);
            if (! $budget['allowed']) {
                $lastError = 'AI budget exceeded ('.$budget['blocking']->label().').';
                Log::warning("AI budget blocks '{$featureKey}' on provider {$provider->name}.");

                continue;
            }

            $maxAttempts = max(1, (int) ($provider->max_retries ?? 0));
            for ($i = 1; $i <= $maxAttempts; $i++) {
                $attempts++;
                $result = $this->attempt($provider, $model, $config, $messages, $options, $fallbackFrom, $attempts);

                if (empty($result['error'])) {
                    $result['attempts'] = $attempts;
                    $result['fallback_used'] = $fallbackFrom !== null;

                    return $result;
                }

                $lastError = $result['error'];

                if (! $this->isRetryable($result)) {
                    break;
                }
            }

            $fallbackFrom = $fallbackFrom ?? $provider->id;
        }

        Log::error("AI dispatch failed for '{$featureKey}' after {$attempts} attempt(s): {$lastError}");

        return ['error' => $lastError, 'attempts' => $attempts];
    }

    /**
     * @return array<int, array{0: AiProvider, 1: AiProviderModel}>
     */
    protected function candidates(AiFeatureConfig $config): array
    {
        $candidates = [];

        if ($config->provider && $config->provider->is_active && $config->model && $config->model->is_active) {
            $candidates[] = [$config->provider, $config->model];
        }

        $seen = [];
        foreach ($candidates as [$p, $m]) {
            $seen[$p->id.':'.$m->id] = true;
        }

        $fallbacks = AiProvider::where('is_active', true)
            ->orderByDesc('priority')
            ->orderBy('id')
            ->with(['models' => fn ($q) => $q->where('is_active', true)->orderByDesc('priority')->orderBy('id')])
            ->get();

        foreach ($fallbacks as $provider) {
            $model = $provider->models->first();
            if (! $model) {
                continue;
            }
            if (isset($seen[$provider->id.':'.$model->id])) {
                continue;
            }
            $candidates[] = [$provider, $model];
        }

        return $candidates;
    }

    protected function attempt(
        AiProvider $provider,
        AiProviderModel $model,
        AiFeatureConfig $config,
        array $messages,
        array $options,
        ?int $fallbackFrom,
        int $attemptNo
    ): array {
        $adapter = $this->resolveAdapter($provider->api_format);

        if (! $adapter) {
            return ['error' => 'No adapter available for provider format.'];
        }

        $timeout = (int) ($provider->timeout_seconds ?: 60);
        $startTime = microtime(true);

        try {
            $result = $adapter->send($provider, $model, [
                'model' => $model->model_id,
                'messages' => $messages,
                'options' => array_merge($config->options ?? [], $options, ['timeout' => $timeout]),
            ]);
        } catch (\Throwable $e) {
            $result = ['error' => $e->getMessage()];
        }

        $durationMs = (int) round((microtime(true) - $startTime) * 1000);

        $cost = $this->estimateCost(
            $model->id,
            (int) ($result['input_tokens'] ?? 0),
            (int) ($result['output_tokens'] ?? 0)
        );

        AiUsageLog::create([
            'provider_id' => $provider->id,
            'model_id' => $model->id,
            'fallback_from_provider_id' => $fallbackFrom,
            'attempt_no' => $attemptNo,
            'feature_key' => $config->feature_key,
            'input_tokens' => (int) ($result['input_tokens'] ?? 0),
            'output_tokens' => (int) ($result['output_tokens'] ?? 0),
            'cost_estimated' => $cost,
            'latency_ms' => $durationMs,
            'success' => empty($result['error']),
            'error_message' => isset($result['error']) ? substr((string) $result['error'], 0, 2000) : null,
        ]);

        if (! empty($result['error'])) {
            return ['error' => $result['error'], 'retryable' => $this->isRetryable($result)];
        }

        $result['provider'] = $provider->name;
        $result['provider_id'] = $provider->id;
        $result['model'] = $model->model_id;
        $result['model_id'] = $model->id;
        $result['latency_ms'] = $durationMs;
        $result['cost_estimated'] = $cost;

        return $result;
    }

    /**
     * Only transient failures are worth a retry/fallback. Invalid user
     * input (validation-style errors) will never succeed on another provider.
     */
    protected function isRetryable(array $result): bool
    {
        if (! isset($result['retryable'])) {
            $error = strtolower((string) ($result['error'] ?? ''));

            return ! str_contains($error, 'invalid') || str_contains($error, 'invalid response');
        }

        return (bool) $result['retryable'];
    }

    public function aiEnabled(): bool
    {
        return (bool) Setting::get('ai.enabled', true);
    }

    public function contentProcessingAllowed(): bool
    {
        return (bool) Setting::get('ai.process_ticket_content', true);
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
