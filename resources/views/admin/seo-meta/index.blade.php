@extends('layouts.admin')
@section('title', 'SEO Meta Overrides')
@section('content')

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">SEO Meta Overrides</h2>
            <p class="text-sm text-slate-500 mt-1">Per-URL override untuk title, description, og:image, dan JSON-LD schema. Berlaku saat URL aplikasi match pattern.</p>
        </div>
        <a href="{{ route('admin.seo-meta.create') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm rounded-lg">+ Add Override</a>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-3 text-left">URL Pattern</th>
                        <th class="px-4 py-3 text-left">Meta Title</th>
                        <th class="px-4 py-3 text-left">Canonical</th>
                        <th class="px-4 py-3 text-center">Noindex</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($items as $item)
                        <tr>
                            <td class="px-4 py-3 font-mono text-xs">{{ $item->url_pattern ?? '—' }}</td>
                            <td class="px-4 py-3 truncate max-w-xs">{{ $item->meta_title ?? '—' }}</td>
                            <td class="px-4 py-3 text-xs text-slate-500 truncate max-w-xs">{{ $item->canonical_url ?? '—' }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($item->noindex)
                                    <span class="text-xs px-2 py-0.5 bg-rose-50 text-rose-700 rounded">noindex</span>
                                @else
                                    <span class="text-xs px-2 py-0.5 bg-emerald-50 text-emerald-700 rounded">index</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right space-x-2">
                                <a href="{{ route('admin.seo-meta.edit', $item) }}" class="text-xs text-indigo-600 hover:text-indigo-700">Edit</a>
                                <form method="POST" action="{{ route('admin.seo-meta.destroy', $item) }}" class="inline" onsubmit="return confirm('Delete this override?')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs text-rose-600 hover:text-rose-700">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-12 text-center text-slate-400">Belum ada SEO override. Add Override untuk meta per URL.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($items->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $items->links() }}</div>
        @endif
    </div>
</div>

@endsection
