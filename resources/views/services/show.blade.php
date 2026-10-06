<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $service->name ?? 'Service Detail' }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $service->name }}</h1>
                        <span class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">
                            {{ $service->price ? '$'.number_format($service->price, 2) : 'Free' }}
                        </span>
                    </div>
                    @if($service->duration ?? false)
                    <div class="text-sm text-gray-400 mb-4">Duration: {{ $service->duration }}</div>
                    @endif
                    <div class="prose prose-lg max-w-none text-gray-700 dark:text-gray-300">
                        {!! nl2br(e($service->description ?? '')) !!}
                    </div>
                    <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <a href="{{ route('services.index') }}" class="text-sm text-indigo-600 hover:text-indigo-700">&larr; All Services</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
