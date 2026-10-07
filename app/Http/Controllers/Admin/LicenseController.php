<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\LicenseClient;
use Illuminate\Http\RedirectResponse;
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
            'lastValidatedAt' => $this->client->lastValidatedAt(),
            'marketplaceUrl' => rtrim((string) config('license.server_url'), '/'),
        ]);
    }

    public function refresh(Request $request): RedirectResponse
    {
        $domain = strtolower($request->getHost());
        $this->client->forgetHeartbeat();
        $data = $this->client->verify($domain);

        ActivityLogService::logCustom(auth()->id(), 'license_refresh', User::class, auth()->id(), (string) auth()->user()?->email);

        if ($data) {
            return back()->with('success', 'License re-validated successfully.');
        }

        return back()->with('error', 'License validation failed. Check pairing or marketplace reachability.');
    }

    public function deactivate(Request $request): RedirectResponse
    {
        $this->client->clearLock();

        ActivityLogService::logCustom(auth()->id(), 'license_deactivate', User::class, auth()->id(), (string) auth()->user()?->email);

        return redirect()->route('admin.license.index')->with('success', 'License deactivated on this installation.');
    }
}
