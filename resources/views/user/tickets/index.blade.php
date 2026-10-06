<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('My Tickets') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6">
                <a href="{{ route('user.tickets.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    New Ticket
                </a>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Subject</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Priority</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Updated</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($tickets ?? [] as $ticket)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-750">
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">#{{ $ticket->id }}</td>
                                <td class="px-6 py-4">
                                    <a href="{{ route('user.tickets.show', $ticket) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">{{ $ticket->subject }}</a>
                                </td>
                                @php $st = (string) ($ticket->status?->value ?? $ticket->status); @endphp
                                <td class="px-6 py-4">
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $st === 'open' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                        {{ $st === 'pending' ? 'bg-amber-100 text-amber-700' : '' }}
                                        {{ $st === 'resolved' ? 'bg-blue-100 text-blue-700' : '' }}
                                        {{ $st === 'closed' ? 'bg-gray-100 text-gray-700' : '' }}">
                                        {{ ucfirst($st) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ ucfirst($ticket->priority ?? 'normal') }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $ticket->updated_at->diffForHumans() }}</td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('user.tickets.show', $ticket) }}" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">View</a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="px-6 py-12 text-center text-gray-400 dark:text-gray-500">No tickets yet. <a href="{{ route('user.tickets.create') }}" class="text-indigo-600 hover:text-indigo-700">Create one</a>.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-3 bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600">
                    {{ ($tickets ?? collect())->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
