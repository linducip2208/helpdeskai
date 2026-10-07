<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiBudget;
use App\Models\AiFeatureConfig;
use App\Models\AiProvider;
use App\Services\ActivityLogService;
use App\Services\AiBudgetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiBudgetController extends Controller
{
    public function index(AiBudgetService $budgets): View
    {
        return view('admin.ai-budgets.index', [
            'budgets' => AiBudget::orderBy('scope')->orderBy('period')->get(),
            'spending' => $budgets->summary(),
            'providers' => AiProvider::orderBy('name')->get(['id', 'name']),
            'features' => AiFeatureConfig::orderBy('feature_key')->pluck('feature_key'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'scope' => 'required|string|in:'.implode(',', AiBudget::SCOPES),
            'scope_id' => 'nullable|string|max:191',
            'period' => 'required|string|in:'.implode(',', AiBudget::PERIODS),
            'limit_usd' => 'required|numeric|min:0.0001',
        ]);

        if (in_array($validated['scope'], ['provider', 'feature'], true) && empty($validated['scope_id'])) {
            return back()->withErrors(['scope_id' => 'Target is required for provider/feature budgets.'])->withInput();
        }

        if ($validated['scope'] === 'global') {
            $validated['scope_id'] = null;
        }

        AiBudget::updateOrCreate(
            [
                'scope' => $validated['scope'],
                'scope_id' => $validated['scope_id'],
                'period' => $validated['period'],
            ],
            ['limit_usd' => $validated['limit_usd'], 'is_active' => true]
        );

        ActivityLogService::logCustom(auth()->id(), 'ai_budget_save', AiBudget::class, null, $validated['scope'].'/'.$validated['period']);

        return back()->with('success', 'Budget saved. Enforcement is immediate.');
    }

    public function destroy(AiBudget $budget): RedirectResponse
    {
        $budget->delete();

        ActivityLogService::logCustom(auth()->id(), 'ai_budget_delete', AiBudget::class, $budget->id, $budget->label());

        return back()->with('success', 'Budget removed.');
    }
}
