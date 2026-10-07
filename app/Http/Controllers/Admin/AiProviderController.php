<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiProvider;
use App\Models\AiProviderModel;
use App\Services\AiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class AiProviderController extends Controller
{
    public function index(): View
    {
        return view('admin.ai-providers.index', [
            'providers' => AiProvider::withCount('models')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.ai-providers.create', [
            'presets' => $this->loadPresets(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'api_format' => 'required|in:openai_compatible,anthropic,gemini',
            'base_url' => 'required|url|max:500',
            'api_key' => 'required|string',
            'extra_headers' => 'nullable|json',
            'notes' => 'nullable|string',
            'priority' => 'nullable|integer|min:0|max:1000',
            'timeout_seconds' => 'nullable|integer|min:5|max:300',
            'max_retries' => 'nullable|integer|min:1|max:5',
            'organization' => 'nullable|string|max:255',
        ]);

        $provider = AiProvider::create([
            'name' => $data['name'],
            'api_format' => $data['api_format'],
            'base_url' => $data['base_url'],
            'api_key_encrypted' => Crypt::encryptString($data['api_key']),
            'extra_headers' => isset($data['extra_headers']) ? json_decode($data['extra_headers'], true) : null,
            'notes' => $data['notes'] ?? null,
            'priority' => $data['priority'] ?? 0,
            'timeout_seconds' => $data['timeout_seconds'] ?? 60,
            'max_retries' => $data['max_retries'] ?? 2,
            'organization' => $data['organization'] ?? null,
            'is_active' => true,
        ]);

        if ($request->filled('default_models')) {
            foreach (array_filter(array_map('trim', explode(',', $request->input('default_models')))) as $modelName) {
                AiProviderModel::create([
                    'provider_id' => $provider->id,
                    'model_id' => substr($modelName, 0, 200),
                    'display_name' => substr($modelName, 0, 200),
                    'capability' => 'chat',
                    'is_active' => true,
                ]);
            }
        }

        return redirect()->route('admin.ai-providers.index')->with('success', 'Provider added.');
    }

    public function edit(AiProvider $aiProvider): View
    {
        return view('admin.ai-providers.edit', [
            'provider' => $aiProvider->load('models'),
            'presets' => $this->loadPresets(),
        ]);
    }

    public function update(Request $request, AiProvider $aiProvider): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'api_format' => 'required|in:openai_compatible,anthropic,gemini',
            'base_url' => 'required|url|max:500',
            'api_key' => 'nullable|string',
            'extra_headers' => 'nullable|json',
            'is_active' => 'sometimes|boolean',
            'notes' => 'nullable|string',
            'priority' => 'nullable|integer|min:0|max:1000',
            'timeout_seconds' => 'nullable|integer|min:5|max:300',
            'max_retries' => 'nullable|integer|min:1|max:5',
            'organization' => 'nullable|string|max:255',
        ]);

        $update = [
            'name' => $data['name'],
            'api_format' => $data['api_format'],
            'base_url' => $data['base_url'],
            'extra_headers' => isset($data['extra_headers']) ? json_decode($data['extra_headers'], true) : null,
            'is_active' => (bool) ($data['is_active'] ?? false),
            'notes' => $data['notes'] ?? null,
            'priority' => $data['priority'] ?? 0,
            'timeout_seconds' => $data['timeout_seconds'] ?? 60,
            'max_retries' => $data['max_retries'] ?? 2,
            'organization' => $data['organization'] ?? null,
        ];

        if (! empty($data['api_key'])) {
            $update['api_key_encrypted'] = Crypt::encryptString($data['api_key']);
        }

        $aiProvider->update($update);

        return redirect()->back()->with('success', 'Provider updated.');
    }

    public function destroy(AiProvider $aiProvider): RedirectResponse
    {
        $aiProvider->delete();

        return redirect()->route('admin.ai-providers.index')->with('success', 'Provider deleted.');
    }

    public function storeModel(Request $request, AiProvider $provider): RedirectResponse
    {
        $data = $request->validate([
            'model_id' => 'required|string|max:200',
            'display_name' => 'nullable|string|max:200',
            'capability' => 'required|string|in:chat,reasoning,embedding,image,audio,vision',
            'cost_input_per_1m' => 'nullable|numeric|min:0',
            'cost_output_per_1m' => 'nullable|numeric|min:0',
            'context_window' => 'nullable|integer|min:0',
            'max_output_tokens' => 'nullable|integer|min:0',
            'priority' => 'nullable|integer|min:0|max:1000',
            'is_active' => 'boolean',
        ]);

        $provider->models()->create([
            'model_id' => $data['model_id'],
            'display_name' => $data['display_name'] ?: $data['model_id'],
            'capability' => $data['capability'],
            'cost_input_per_1m' => $data['cost_input_per_1m'] ?? null,
            'cost_output_per_1m' => $data['cost_output_per_1m'] ?? null,
            'context_window' => $data['context_window'] ?? null,
            'max_output_tokens' => $data['max_output_tokens'] ?? null,
            'priority' => $data['priority'] ?? 0,
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

        return back()->with('success', 'Model added.');
    }

    public function destroyModel(AiProvider $provider, AiProviderModel $model): RedirectResponse
    {
        abort_unless($model->provider_id === $provider->id, 404);
        $model->delete();

        return back()->with('success', 'Model removed.');
    }

    public function testConnection(AiProvider $provider): JsonResponse
    {
        $result = app(AiService::class)->testConnection($provider);

        return response()->json(['success' => $result]);
    }

    public function fetchModels(AiProvider $provider): JsonResponse
    {
        try {
            $models = app(AiService::class)->listModels($provider);

            return response()->json(['data' => $models]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function loadPresets(): array
    {
        $dir = storage_path('app/ai-presets');
        if (! is_dir($dir)) {
            return [];
        }

        $presets = collect(File::files($dir))
            ->filter(fn ($file) => $file->getExtension() === 'json')
            ->map(function ($file) {
                $data = json_decode(File::get($file->getPathname()), true);
                if (! is_array($data)) {
                    return null;
                }
                $data['slug'] = $file->getFilenameWithoutExtension();
                $data['cost_tier'] = $data['cost_tier'] ?? 99;
                $data['tier_label'] = $data['tier_label'] ?? 'Unknown';
                $data['tier_color'] = $data['tier_color'] ?? 'slate';

                return $data;
            })
            ->filter()
            ->sortBy([
                ['cost_tier', 'asc'],
                ['name_suggestion', 'asc'],
            ])
            ->values()
            ->all();

        return $presets;
    }
}
