<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class SupportController extends Controller
{
    public function index(): View
    {
        return view('support', [
            'appName' => config('app.name', 'HelpDesk AI'),
            'salesContact' => config('helpdesk.sales_contact', '081296052010'),
        ]);
    }
}
