@extends('layouts.admin')
@section('title', 'Analytics')
@section('content')

<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-2xl font-bold text-slate-900">Analytics</h2>
        <div class="flex gap-2">
            <a href="{{ route('admin.export.tickets', request()->only(['from','to','status','priority'])) }}"
               class="inline-flex items-center px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm font-medium text-slate-700 hover:bg-gray-50">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/></svg>
                Export Tickets CSV
            </a>
            <a href="{{ route('admin.export.agents') }}"
               class="inline-flex items-center px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm font-medium text-slate-700 hover:bg-gray-50">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/></svg>
                Export Agents CSV
            </a>
        </div>
    </div>

    <!-- Date Range -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <form class="flex flex-col sm:flex-row gap-4 items-end">
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">From</label>
                <input type="date" name="from" value="{{ request('from') }}" class="rounded-lg border-gray-200 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">To</label>
                <input type="date" name="to" value="{{ request('to') }}" class="rounded-lg border-gray-200 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            </div>
            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">Apply</button>
        </form>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <p class="text-sm font-medium text-slate-500">Total Tickets</p>
            <p class="text-3xl font-bold text-slate-900 mt-1">{{ $analytics->total_tickets ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <p class="text-sm font-medium text-slate-500">Resolved</p>
            <p class="text-3xl font-bold text-emerald-600 mt-1">{{ $analytics->resolved ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <p class="text-sm font-medium text-slate-500">Avg First Response</p>
            <p class="text-3xl font-bold text-slate-900 mt-1">{{ $analytics->avg_first_response ?? 'N/A' }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <p class="text-sm font-medium text-slate-500">CSAT Score</p>
            <p class="text-3xl font-bold text-amber-600 mt-1">{{ $analytics->csat_score ?? 'N/A' }}</p>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-semibold text-slate-900 mb-4">Tickets by Status</h3>
            <div class="space-y-3">
                @php $statuses = ['open' => 35, 'pending' => 20, 'resolved' => 30, 'closed' => 15]; @endphp
                @foreach($statuses as $status => $percent)
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-slate-600">{{ ucfirst($status) }}</span>
                        <span class="text-slate-500">{{ $percent }}%</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        <div class="h-2 rounded-full {{ $status === 'open' ? 'bg-emerald-500' : ($status === 'pending' ? 'bg-amber-500' : ($status === 'resolved' ? 'bg-blue-500' : 'bg-slate-400')) }}" style="width: {{ $percent }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-semibold text-slate-900 mb-4">Tickets by Priority</h3>
            <div class="space-y-3">
                @php $priorities = ['urgent' => 10, 'high' => 25, 'medium' => 45, 'low' => 20]; @endphp
                @foreach($priorities as $priority => $percent)
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-slate-600">{{ ucfirst($priority) }}</span>
                        <span class="text-slate-500">{{ $percent }}%</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        <div class="h-2 rounded-full {{ $priority === 'urgent' ? 'bg-red-500' : ($priority === 'high' ? 'bg-orange-500' : ($priority === 'medium' ? 'bg-blue-500' : 'bg-slate-400')) }}" style="width: {{ $percent }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Agent Performance -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-slate-900 mb-4">Agent Performance</h3>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Agent</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Resolved</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Avg Response</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">CSAT</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($agentPerformance ?? [] as $perf)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-medium text-slate-900">{{ $perf->name }}</td>
                        <td class="px-6 py-4 text-sm text-slate-500">{{ $perf->resolved_count }}</td>
                        <td class="px-6 py-4 text-sm text-slate-500">{{ $perf->avg_response }}</td>
                        <td class="px-6 py-4 text-sm text-slate-500">{{ $perf->csat }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-6 py-8 text-center text-slate-400">No data available.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
