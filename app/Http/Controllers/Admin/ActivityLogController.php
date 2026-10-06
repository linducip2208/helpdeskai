<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $logs = ActivityLog::with('user')
            ->when($request->action, fn ($q) => $q->where('action', $request->action))
            ->when($request->search, fn ($q) => $q->where('target_label', 'like', "%{$request->search}%"))
            ->latest()
            ->paginate(50);

        return view('admin.activity-log.index', ['logs' => $logs]);
    }
}
