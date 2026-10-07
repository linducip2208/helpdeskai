<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ImportJob;
use App\Models\ImportLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ImportController extends Controller
{
    public function index(): View
    {
        return view('admin.imports.index', [
            'logs' => ImportLog::with('user:id,name')->latest('id')->paginate(20),
        ]);
    }

    public function upload(Request $request): View
    {
        $validated = $request->validate([
            'type' => 'required|in:customers,tickets',
            'file' => 'required|file|max:10240|mimetypes:text/csv,text/plain,application/csv,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);

        $path = $request->file('file')->store('imports');

        $data = ImportJob::readRows(Storage::disk('local')->path($path), 200);
        $preview = array_slice($data['rows'], 0, 5);

        $log = ImportLog::create([
            'type' => $validated['type'],
            'filename' => $path,
            'total_rows' => count($data['rows']),
            'status' => ImportLog::STATUS_PENDING,
            'user_id' => auth()->id(),
        ]);

        return view('admin.imports.preview', [
            'log' => $log,
            'headers' => $data['headers'],
            'preview' => $preview,
            'fields' => ImportJob::fieldsFor($validated['type']),
            'mapping' => ImportJob::defaultMapping($validated['type'], $data['headers']),
            'dryRun' => null,
        ]);
    }

    public function confirm(Request $request, ImportLog $log): View|RedirectResponse
    {
        $validated = $request->validate([
            'mapping' => 'required|array',
            'mapping.*' => 'nullable|string|max:191',
            'dry_run' => 'nullable|boolean',
        ]);

        if (! Storage::disk('local')->exists($log->filename)) {
            return back()->with('error', __('Import file not found.'));
        }

        $data = ImportJob::readRows(Storage::disk('local')->path($log->filename));
        $rows = ImportJob::applyMapping($data['rows'], array_filter($validated['mapping']));

        if ($request->boolean('dry_run')) {
            return view('admin.imports.preview', [
                'log' => $log,
                'headers' => $data['headers'],
                'preview' => array_slice($data['rows'], 0, 5),
                'fields' => ImportJob::fieldsFor($log->type),
                'mapping' => $validated['mapping'],
                'dryRun' => ImportJob::dryRun($log->type, $rows),
            ]);
        }

        if (! $log->isRerunnable()) {
            return redirect()->route('admin.imports.show', $log)->with('error', __('This import already succeeded and cannot be re-run.'));
        }

        $log->update(['total_rows' => count($rows), 'status' => ImportLog::STATUS_PROCESSING]);

        ImportJob::dispatch($log->id, array_filter($validated['mapping']));

        return redirect()->route('admin.imports.show', $log)->with('success', __('Import started.'));
    }

    public function show(ImportLog $log): View
    {
        return view('admin.imports.show', [
            'log' => $log,
            'dryRun' => null,
            'isDryRun' => false,
        ]);
    }
}
