<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class QueueController extends Controller
{
    public function index(): View
    {
        $driver = config('queue.default');
        $pending = null;
        $failed = null;

        if ($driver === 'database' && Schema::hasTable('jobs')) {
            $pending = DB::table('jobs')->selectRaw('queue, COUNT(*) as total')->groupBy('queue')->get();
        }

        if (Schema::hasTable('failed_jobs')) {
            $failed = DB::table('failed_jobs')->latest('failed_at')->take(25)->get();
        }

        return view('admin.queue.index', [
            'driver' => $driver,
            'pending' => $pending,
            'failed' => $failed,
            'failedCount' => $failed !== null ? DB::table('failed_jobs')->count() : null,
        ]);
    }

    public function retry(string $id): RedirectResponse
    {
        abort_unless(Schema::hasTable('failed_jobs'), 404);

        $exit = Artisan::call('queue:retry', ['id' => [$id]]);

        return back()->with($exit === 0 ? 'success' : 'error', $exit === 0 ? 'Job re-queued.' : 'Retry failed.');
    }

    public function forget(string $id): RedirectResponse
    {
        abort_unless(Schema::hasTable('failed_jobs'), 404);

        Artisan::call('queue:forget', ['id' => $id]);

        return back()->with('success', 'Failed job deleted.');
    }

    public function flush(): RedirectResponse
    {
        abort_unless(Schema::hasTable('failed_jobs'), 404);

        Artisan::call('queue:flush');

        return back()->with('success', 'All failed jobs deleted.');
    }
}
