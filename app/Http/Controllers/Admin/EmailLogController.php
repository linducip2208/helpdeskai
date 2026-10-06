<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = EmailLog::query()->with(['ticket:id,uid,subject', 'reply:id,ticket_id']);

        if ($request->filled('direction')) {
            $query->where('direction', $request->direction);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($w) use ($q) {
                $w->where('from_email', 'like', "%{$q}%")
                  ->orWhere('to_email', 'like', "%{$q}%")
                  ->orWhere('subject', 'like', "%{$q}%");
            });
        }

        return view('admin.email-logs.index', [
            'logs' => $query->latest()->paginate(30)->withQueryString(),
            'totals' => [
                'received' => EmailLog::where('direction', 'inbound')->count(),
                'failed' => EmailLog::where('status', 'failed')->count(),
                'sent' => EmailLog::where('direction', 'outbound')->count(),
            ],
        ]);
    }

    public function show(EmailLog $emailLog): View
    {
        return view('admin.email-logs.show', ['log' => $emailLog]);
    }

    public function destroy(EmailLog $emailLog): RedirectResponse
    {
        $emailLog->delete();

        return back()->with('success', 'Email log deleted.');
    }
}
