<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AiPresetSeeder extends Seeder
{
    public function run(): void
    {
        $presets = [
            [
                'name' => 'openai',
                'name_suggestion' => 'OpenAI',
                'api_format' => 'openai',
                'base_url' => 'https://api.openai.com/v1',
                'models_suggestion' => ['gpt-4o', 'gpt-4-turbo', 'gpt-4', 'gpt-3.5-turbo'],
                'signup_url' => 'https://platform.openai.com/signup',
                'docs_url' => 'https://platform.openai.com/docs',
            ],
            [
                'name' => 'deepseek',
                'name_suggestion' => 'DeepSeek',
                'api_format' => 'openai',
                'base_url' => 'https://api.deepseek.com/v1',
                'models_suggestion' => ['deepseek-chat', 'deepseek-reasoner'],
                'signup_url' => 'https://platform.deepseek.com/signup',
                'docs_url' => 'https://platform.deepseek.com/docs',
            ],
            [
                'name' => 'groq',
                'name_suggestion' => 'Groq',
                'api_format' => 'openai',
                'base_url' => 'https://api.groq.com/openai/v1',
                'models_suggestion' => ['llama-3.3-70b-versatile', 'mixtral-8x7b-32768', 'gemma2-9b-it', 'llama-3.2-90b-vision-preview'],
                'signup_url' => 'https://console.groq.com/login',
                'docs_url' => 'https://console.groq.com/docs',
            ],
            [
                'name' => 'claude',
                'name_suggestion' => 'Anthropic Claude',
                'api_format' => 'anthropic',
                'base_url' => 'https://api.anthropic.com/v1',
                'models_suggestion' => ['claude-sonnet-4-20250514', 'claude-3-5-sonnet-20241022', 'claude-3-5-haiku-20241022', 'claude-opus-4-20250514'],
                'signup_url' => 'https://console.anthropic.com/',
                'docs_url' => 'https://docs.anthropic.com/en/docs',
            ],
            [
                'name' => 'gemini',
                'name_suggestion' => 'Google Gemini',
                'api_format' => 'gemini',
                'base_url' => 'https://generativelanguage.googleapis.com/v1beta',
                'models_suggestion' => ['gemini-2.5-pro-preview-05-06', 'gemini-2.5-flash-preview-05-20', 'gemini-2.0-flash'],
                'signup_url' => 'https://aistudio.google.com/',
                'docs_url' => 'https://ai.google.dev/docs',
            ],
            [
                'name' => 'mistral',
                'name_suggestion' => 'Mistral AI',
                'api_format' => 'openai',
                'base_url' => 'https://api.mistral.ai/v1',
                'models_suggestion' => ['mistral-large-latest', 'mistral-medium-latest', 'mistral-small-latest', 'pixtral-large-latest'],
                'signup_url' => 'https://console.mistral.ai/',
                'docs_url' => 'https://docs.mistral.ai/',
            ],
            [
                'name' => 'together',
                'name_suggestion' => 'Together AI',
                'api_format' => 'openai',
                'base_url' => 'https://api.together.xyz/v1',
                'models_suggestion' => ['meta-llama/Llama-3.3-70B-Instruct-Turbo', 'mistralai/Mixtral-8x7B-Instruct-v0.1', 'Qwen/Qwen2-72B-Instruct'],
                'signup_url' => 'https://api.together.xyz/',
                'docs_url' => 'https://docs.together.ai/',
            ],
            [
                'name' => 'fireworks',
                'name_suggestion' => 'Fireworks AI',
                'api_format' => 'openai',
                'base_url' => 'https://api.fireworks.ai/inference/v1',
                'models_suggestion' => ['accounts/fireworks/models/llama-v3p3-70b-instruct', 'accounts/fireworks/models/mixtral-8x22b-instruct'],
                'signup_url' => 'https://fireworks.ai/',
                'docs_url' => 'https://docs.fireworks.ai/',
            ],
            [
                'name' => 'openrouter',
                'name_suggestion' => 'OpenRouter',
                'api_format' => 'openai',
                'base_url' => 'https://openrouter.ai/api/v1',
                'models_suggestion' => ['openai/gpt-4o', 'anthropic/claude-sonnet-4', 'google/gemini-2.5-pro', 'meta-llama/llama-3.3-70b-instruct'],
                'signup_url' => 'https://openrouter.ai/',
                'docs_url' => 'https://openrouter.ai/docs',
            ],
            [
                'name' => 'xai',
                'name_suggestion' => 'xAI Grok',
                'api_format' => 'openai',
                'base_url' => 'https://api.x.ai/v1',
                'models_suggestion' => ['grok-beta'],
                'signup_url' => 'https://x.ai/',
                'docs_url' => 'https://docs.x.ai/',
            ],
            [
                'name' => 'anyscale',
                'name_suggestion' => 'Anyscale',
                'api_format' => 'openai',
                'base_url' => 'https://api.endpoints.anyscale.com/v1',
                'models_suggestion' => ['meta-llama/Llama-3.3-70B-Instruct', 'mistralai/Mixtral-8x7B-Instruct-v0.1'],
                'signup_url' => 'https://www.anyscale.com/',
                'docs_url' => 'https://docs.anyscale.com/',
            ],
            [
                'name' => 'cerebras',
                'name_suggestion' => 'Cerebras',
                'api_format' => 'openai',
                'base_url' => 'https://api.cerebras.ai/v1',
                'models_suggestion' => ['llama3.3-70b', 'llama3.2-3b'],
                'signup_url' => 'https://cloud.cerebras.ai/',
                'docs_url' => 'https://docs.cerebras.ai/',
            ],
            [
                'name' => 'lepton',
                'name_suggestion' => 'Lepton AI',
                'api_format' => 'openai',
                'base_url' => 'https://llama3-2-3b.lepton.run/api/v1',
                'models_suggestion' => ['llama3-2-3b'],
                'signup_url' => 'https://www.lepton.ai/',
                'docs_url' => 'https://www.lepton.ai/docs',
            ],
            [
                'name' => 'ollama',
                'name_suggestion' => 'Ollama (Local)',
                'api_format' => 'openai',
                'base_url' => 'http://localhost:11434/v1',
                'models_suggestion' => ['llama3.2', 'mistral', 'gemma2', 'qwen2.5', 'phi4', 'deepseek-r1'],
                'signup_url' => null,
                'docs_url' => 'https://ollama.ai/docs',
            ],
            [
                'name' => 'lmstudio',
                'name_suggestion' => 'LM Studio (Local)',
                'api_format' => 'openai',
                'base_url' => 'http://localhost:1234/v1',
                'models_suggestion' => ['any-loaded-model'],
                'signup_url' => null,
                'docs_url' => 'https://lmstudio.ai/docs',
            ],
            [
                'name' => 'cloudflare',
                'name_suggestion' => 'Cloudflare Workers AI',
                'api_format' => 'openai',
                'base_url' => 'https://api.cloudflare.com/client/v4/accounts/{account_id}/ai/v1',
                'models_suggestion' => ['@cf/meta/llama-3.3-70b-instruct-fp8-fast', '@cf/mistral/mistral-7b-instruct-v0.1'],
                'signup_url' => 'https://dash.cloudflare.com/',
                'docs_url' => 'https://developers.cloudflare.com/workers-ai/',
            ],
            [
                'name' => 'replicate',
                'name_suggestion' => 'Replicate',
                'api_format' => 'openai',
                'base_url' => 'https://api.replicate.com/v1',
                'models_suggestion' => ['meta/llama-3.3-70b-instruct'],
                'signup_url' => 'https://replicate.com/',
                'docs_url' => 'https://replicate.com/docs',
            ],
            [
                'name' => 'stability',
                'name_suggestion' => 'Stability AI',
                'api_format' => 'stability',
                'base_url' => 'https://api.stability.ai/v1',
                'models_suggestion' => ['stable-diffusion-xl-1024-v1-0'],
                'signup_url' => 'https://platform.stability.ai/',
                'docs_url' => 'https://platform.stability.ai/docs',
            ],
            [
                'name' => 'cohere',
                'name_suggestion' => 'Cohere',
                'api_format' => 'cohere',
                'base_url' => 'https://api.cohere.ai/v2',
                'models_suggestion' => ['command-r-plus', 'command-r', 'command'],
                'signup_url' => 'https://dashboard.cohere.com/',
                'docs_url' => 'https://docs.cohere.com/docs',
            ],
            [
                'name' => 'perplexity',
                'name_suggestion' => 'Perplexity',
                'api_format' => 'openai',
                'base_url' => 'https://api.perplexity.ai',
                'models_suggestion' => ['sonar-pro', 'sonar', 'llama-3.1-sonar-small-128k-online'],
                'signup_url' => 'https://www.perplexity.ai/',
                'docs_url' => 'https://docs.perplexity.ai/',
            ],
        ];

        $presetPath = storage_path('app/ai-presets');

        if (! is_dir($presetPath)) {
            mkdir($presetPath, 0755, true);
        }

        foreach ($presets as $preset) {
            $filename = $preset['name'].'.json';
            unset($preset['name']);
            file_put_contents(
                $presetPath.'/'.$filename,
                json_encode($preset, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
            );
        }
    }
}
