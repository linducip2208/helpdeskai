<?php

namespace App\Http\Controllers;

use App\Models\AiQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AiQueryFeedbackController extends Controller
{
    public function update(Request $request, AiQuery $aiQuery): RedirectResponse
    {
        $validated = $request->validate(['feedback' => 'required|in:helpful,not_helpful']);
        abort_unless($aiQuery->user_id === $request->user()->id || $request->user()->can('ai.use'), 403);
        $aiQuery->update(['feedback' => $validated['feedback']]);

        return back()->with('success', __('Thank you for your feedback.'));
    }
}
