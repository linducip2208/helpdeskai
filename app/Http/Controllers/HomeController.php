<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Service;
use App\Models\Setting;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $services = Service::where('is_active', true)->orderBy('sort_order')->take(6)->get();
        $posts = Post::with('user')->where('status', 'published')->latest()->take(3)->get();

        return view('home', [
            'services' => $services,
            'posts' => $posts,
            'appName' => Setting::get('app_name', 'HelpDesk AI'),
            'appDescription' => Setting::get('app_description', 'AI-Powered Customer Support'),
        ]);
    }
}
