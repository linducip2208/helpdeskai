<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HolidayController extends Controller
{
    public function index(): View
    {
        return view('admin.holidays.index', [
            'holidays' => Holiday::orderBy('date')->paginate(25),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date' => 'required|date|unique:holidays,date',
            'name' => 'required|string|max:255',
        ]);

        $holiday = Holiday::create($validated);

        ActivityLogService::logCustom(auth()->id(), 'holiday_create', Holiday::class, $holiday->id, $holiday->name);

        return back()->with('success', 'Holiday added.');
    }

    public function destroy(Holiday $holiday): RedirectResponse
    {
        $name = $holiday->name;
        $id = $holiday->id;
        $holiday->delete();

        ActivityLogService::logCustom(auth()->id(), 'holiday_delete', Holiday::class, $id, $name);

        return back()->with('success', 'Holiday removed.');
    }
}
