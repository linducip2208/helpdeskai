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
        ]);

        $provider = AiProvider::create([
            'name' => $data['name'],
            'api_format' => $data['api_format'],
            'base_url' => $data['base_url'],
            'api_key_encrypted' => Crypt::encryptString($data['api_key']),
            'extra_headers' => isset($data['extra_headers']) ? json_decode($data['extra_headers'], true) : null,
            'notes' => $data['notes'] ?? null,
            'is_active' => true,
        ]);

        if ($request->filled('default_models')) {
            foreach (array_filter(array_map('trim', explode(',', $request->input('default_models')))) as $modelName) {
                AiProviderModel::create([
                    'provider_id' => $provider->id,
                    'model_name' => $modelName,
                    'display_name' => $modelName,
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
        ]);

        $update = [
            'name' => $data['name'],
            'api_format' => $data['api_format'],
            'base_url' => $data['base_url'],
            'extra_headers' => isset($data['extra_headers']) ? json_decode($data['extra_headers'], true) : null,
            'is_active' => (bool) ($data['is_active'] ?? false),
            'notes' => $data['notes'] ?? null,
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
