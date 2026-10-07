<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AutomationRule;
use App\Models\AutomationRun;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AutomationRunController extends Controller
{
    public function index(Request $request): View
    {
        $runs = AutomationRun::with(['rule:id,name', 'ticket:id,uid,subject'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->rule_id, fn ($q) => $q->where('rule_id', $request->rule_id))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.automation-runs.index', [
            'runs' => $runs,
            'rules' => AutomationRule::orderBy('name')->get(['id', 'name']),
            'filters' => $request->only(['status', 'rule_id']),
        ]);
    }
}
