<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\View\View;

class ServicePageController extends Controller
{
    public function index(): View
    {
        return view('services.index', [
            'services' => Service::where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function show(string $slug): View
    {
        $service = Service::where('slug', $slug)->where('is_active', true)->firstOrFail();

        return view('services.show', ['service' => $service]);
    }
}
