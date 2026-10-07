<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MyWorkController extends Controller
{
    public function index(Request $request): View
    {
        $userId = $request->user()->id;
        $open = ['open', 'in_progress', 'waiting'];

        $assigned = Ticket::with(['department:id,name'])
            ->where('assigned_to', $userId)
            ->whereIn('status', $open)
            ->latest()
            ->take(10)
            ->get();

        $unassigned = Ticket::with(['department:id,name'])
            ->whereNull('assigned_to')
            ->whereIn('status', $open)
            ->latest()
            ->take(10)
            ->get();

        $atRisk = Ticket::where('assigned_to', $userId)
            ->whereNotNull('sla_warned_at')
            ->where('sla_breached', false)
            ->whereIn('status', $open)
            ->latest('sla_due_at')
            ->take(10)
            ->get();

        $overdue = Ticket::where('assigned_to', $userId)
            ->where('sla_breached', true)
            ->whereIn('status', $open)
            ->latest('sla_due_at')
            ->take(10)
            ->get();

        $highPriority = Ticket::where('priority', 'urgent')
            ->whereIn('status', $open)
            ->latest()
            ->take(10)
            ->get(['id', 'uid', 'subject', 'status', 'assigned_to', 'created_at']);

        $waiting = Ticket::where('status', 'waiting')
            ->latest()
            ->take(10)
            ->get(['id', 'uid', 'subject', 'assigned_to', 'updated_at']);

        $activity = ActivityLog::with('user:id,name')
            ->where('user_id', $userId)
            ->latest()
            ->take(10)
            ->get();

        return view('admin.my-work.index', compact(
            'assigned', 'unassigned', 'atRisk', 'overdue', 'highPriority', 'waiting', 'activity'
        ));
    }
}
