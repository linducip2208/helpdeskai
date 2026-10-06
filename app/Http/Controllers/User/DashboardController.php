<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        return view('dashboard', [
            'stats' => [
                'open_tickets' => Ticket::where('user_id', $user->id)->whereIn('status', ['open', 'in_progress'])->count(),
                'total_tickets' => Ticket::where('user_id', $user->id)->count(),
                'resolved_tickets' => Ticket::where('user_id', $user->id)->where('status', 'resolved')->count(),
            ],
            'recentTickets' => Ticket::where('user_id', $user->id)->latest()->take(5)->get(),
        ]);
    }
}
