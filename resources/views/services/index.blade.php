<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Our Services') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($services ?? [] as $service)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 hover:shadow-md transition">
                    <div class="w-12 h-12 bg-indigo-50 dark:bg-indigo-900 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        <a href="{{ route('services.show', $service) }}" class="hover:text-indigo-600 transition">{{ $service->name }}</a>
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ Str::limit($service->description ?? '', 100) }}</p>
                    <div class="flex items-center justify-between">
                        <span class="text-lg font-bold text-indigo-600 dark:text-indigo-400">
                            {{ $service->price ? '$'.number_format($service->price, 2) : 'Free' }}
                        </span>
                        <a href="{{ route('services.show', $service) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">Learn more &rarr;</a>
                    </div>
                </div>
                @empty
                <div class="col-span-full text-center py-12 text-gray-400 dark:text-gray-500">No services available at this moment.</div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
