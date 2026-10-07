<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KnowledgeArticle;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $query = trim((string) $request->get('q', ''));
        $results = ['tickets' => collect(), 'users' => collect(), 'articles' => collect()];

        if (mb_strlen($query) >= 2) {
            $like = '%'.$query.'%';

            if ($request->user()->can('tickets.view')) {
                $results['tickets'] = Ticket::with(['user:id,name', 'department:id,name'])
                    ->where(function ($q) use ($like) {
                        $q->where('subject', 'like', $like)
                            ->orWhere('body', 'like', $like)
                            ->orWhere('uid', 'like', strtoupper($like));
                    })
                    ->latest()
                    ->take(10)
                    ->get(['id', 'uid', 'subject', 'status', 'priority', 'user_id', 'department_id', 'created_at']);
            }

            if ($request->user()->can('customers.view')) {
                $results['users'] = User::where(function ($q) use ($like) {
                    $q->where('name', 'like', $like)->orWhere('email', 'like', $like);
                })->take(10)->get(['id', 'name', 'email']);
            }

            if ($request->user()->can('manage_knowledge')) {
                $results['articles'] = KnowledgeArticle::where(function ($q) use ($like) {
                    $q->where('title', 'like', $like)->orWhere('content', 'like', $like);
                })->take(10)->get(['id', 'title', 'slug', 'status']);
            }
        }

        return view('admin.search.index', [
            'query' => $query,
            'results' => $results,
        ]);
    }
}
