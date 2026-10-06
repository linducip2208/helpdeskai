<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiFeatureConfig;
use App\Models\AiProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiFeatureConfigController extends Controller
{
    public function index(): View
    {
        $features = config('helpdesk.ai.features');
        $configs = AiFeatureConfig::with(['provider', 'model'])->get()->keyBy('feature_key');
        $providers = AiProvider::where('is_active', true)->with('models')->get();

        return view('admin.ai-features.index', [
            'features' => $features,
            'configs' => $configs,
            'providers' => $providers,
        ]);
    }

    public function edit(AiFeatureConfig $aiFeature): View
    {
        return view('admin.ai-features.edit', [
            'config' => $aiFeature->load(['provider', 'model']),
            'providers' => AiProvider::where('is_active', true)->with('models')->get(),
            'feature' => config('helpdesk.ai.features.'.$aiFeature->feature_key, []),
        ]);
    }

    public function update(Request $request, AiFeatureConfig $aiFeature): RedirectResponse
    {
        $aiFeature->update($request->validate([
            'provider_id' => 'nullable|exists:ai_providers,id',
            'model_id' => 'nullable|exists:ai_provider_models,id',
            'is_enabled' => 'boolean',
        ]));

        return redirect()->route('admin.ai-features.index')->with('success', 'AI feature updated.');
    }
}
