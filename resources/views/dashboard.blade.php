<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-xl p-6">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Open Tickets</div>
                    <div class="mt-2 text-3xl font-bold text-amber-600">{{ $stats['open_tickets'] ?? 0 }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-xl p-6">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Tickets</div>
                    <div class="mt-2 text-3xl font-bold text-indigo-600">{{ $stats['total_tickets'] ?? 0 }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-xl p-6">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Resolved</div>
                    <div class="mt-2 text-3xl font-bold text-emerald-600">{{ $stats['resolved_tickets'] ?? 0 }}</div>
                </div>
            </div>

            <!-- Recent Tickets -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-xl">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Tickets</h3>
                    <a href="{{ route('user.tickets.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">New Ticket</a>
                </div>
                <div class="p-6">
                    @if(isset($recentTickets) && count($recentTickets) > 0)
                        <div class="space-y-3">
                            @foreach($recentTickets as $ticket)
                                @php $st = (string) ($ticket->status?->value ?? $ticket->status); @endphp
                                <a href="{{ route('user.tickets.show', $ticket) }}" class="block p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $ticket->subject }}</span>
                                            <span class="ml-2 px-2 py-0.5 text-xs rounded-full
                                                @if($st === 'open') bg-amber-100 text-amber-800
                                                @elseif($st === 'in_progress') bg-blue-100 text-blue-800
                                                @elseif($st === 'resolved') bg-emerald-100 text-emerald-800
                                                @else bg-gray-100 text-gray-800 @endif">
                                                {{ ucfirst(str_replace('_', ' ', $st)) }}
                                            </span>
                                        </div>
                                        <span class="text-xs text-gray-500">{{ $ticket->created_at->diffForHumans() }}</span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-8">No tickets yet. <a href="{{ route('user.tickets.create') }}" class="text-indigo-600 hover:underline">Create your first ticket</a>.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
