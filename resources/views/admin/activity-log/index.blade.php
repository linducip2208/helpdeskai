@extends('layouts.admin')
@section('title', 'Activity Log')
@section('content')

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-slate-900">Activity Log</h2>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <form class="flex flex-col sm:flex-row gap-3 sm:gap-4">
            <input type="text" name="search" placeholder="Search activities..." class="flex-1 rounded-lg border-gray-200 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            <select name="user_id" class="rounded-lg border-gray-200 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <option value="">All Users</option>
                @foreach($users ?? [] as $user)
                <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium rounded-lg transition">Filter</button>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Action</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Subject</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">IP Address</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($logs ?? [] as $activity)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-7 h-7 bg-slate-100 rounded-full flex items-center justify-center text-slate-600 text-xs font-semibold mr-2">
                                    {{ substr($activity->user->name ?? 'S', 0, 1) }}
                                </div>
                                <span class="text-sm text-slate-700">{{ $activity->user->name ?? 'System' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-600">{{ $activity->action }}</td>
                        <td class="px-6 py-4 text-sm text-slate-500">{{ Str::limit($activity->target_label ?? '', 60) }}</td>
                        <td class="px-6 py-4 text-sm text-slate-500 font-mono">{{ $activity->ip_address ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm text-slate-500">{{ $activity->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-6 py-12 text-center text-slate-400">No activity recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-3 bg-gray-50 border-t border-gray-100">
            @if(isset($logs) && method_exists($logs, 'links'))
                {{ $logs->links() }}
            @endif
        </div>
    </div>
</div>

@endsection
