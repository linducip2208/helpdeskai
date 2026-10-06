<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\LicenseClient;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LicenseController extends Controller
{
    public function __construct(private LicenseClient $client) {}

    public function index(Request $request): View
    {
        $domain = strtolower($request->getHost());
        $data = $this->client->verify($domain);

        return view('admin.license.index', [
            'domain' => $domain,
            'paired' => $data !== null,
            'data' => $data,
            'marketplaceUrl' => rtrim((string) config('license.server_url'), '/'),
        ]);
    }
}
