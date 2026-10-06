@extends('layouts.admin')
@section('title', 'API Keys')
@section('content')

<div class="space-y-6">
    @if (session('plain_key'))
        <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg">
            <p class="text-sm font-medium text-amber-900 mb-2">{{ session('success') }}</p>
            <code class="block bg-white border border-amber-200 rounded p-3 font-mono text-sm break-all select-all">{{ session('plain_key') }}</code>
        </div>
    @elseif (session('success'))
        <div class="p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ session('success') }}</div>
    @endif

    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-slate-900">API Keys</h2>
        <button type="button" onclick="document.getElementById('create-modal').classList.remove('hidden')" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Generate Key
        </button>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Key</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Permissions</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Last Used</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($apiKeys ?? [] as $key)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-medium text-slate-900">{{ $key->name }}</td>
                        <td class="px-6 py-4 text-sm text-slate-500 font-mono">{{ Str::limit($key->masked_key ?? '••••••••••••••••', 16) }}</td>
                        <td class="px-6 py-4 text-sm text-slate-500">{{ $key->permissions ?? 'read' }}</td>
                        <td class="px-6 py-4 text-sm text-slate-500">{{ $key->last_used_at?->diffForHumans() ?? 'Never' }}</td>
                        <td class="px-6 py-4 text-sm text-slate-500">{{ $key->created_at->format('M d, Y') }}</td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <form action="{{ route('admin.api-keys.destroy', $key) }}" method="POST" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-600 text-sm font-medium" onclick="return confirm('Revoke this key?')">Revoke</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-6 py-12 text-center text-slate-400">No API keys generated yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div id="create-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-slate-900">Generate API Key</h3>
                <button onclick="document.getElementById('create-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-2xl leading-none">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.api-keys.store') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Key Name</label>
                        <input type="text" name="name" placeholder="e.g. Mobile App, Integration" class="w-full rounded-lg border-gray-200 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Permissions</label>
                        <select name="permissions" class="w-full rounded-lg border-gray-200 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="read">Read Only</option>
                            <option value="read-write">Read & Write</option>
                            <option value="full">Full Access</option>
                        </select>
                    </div>
                    <div class="flex justify-end space-x-3 pt-2">
                        <button type="button" onclick="document.getElementById('create-modal').classList.add('hidden')" class="px-4 py-2 border border-gray-200 text-sm font-medium rounded-lg hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg">Generate</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
