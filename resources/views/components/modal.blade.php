@props(['name', 'show' => false, 'maxWidth' => '2xl'])

@php
$maxWidthClass = match ($maxWidth) {
    'sm' => 'modal-sm',
    'lg' => 'modal-lg',
    'xl' => 'modal-xl',
    default => '',
};
@endphp

<div class="modal modal-blur fade" id="{{ $name }}" tabindex="-1" role="dialog" aria-hidden="true" x-data="{ show: @js($show) }" x-show="show" x-on:open-modal.window="$event.detail == '{{ $name }}' ? show = true : null" x-on:close-modal.window="$event.detail == '{{ $name }}' ? show = false : null" x-on:keydown.escape.window="show = false" style="display: none;">
    <div class="modal-dialog {{ $maxWidthClass }} modal-dialog-centered" role="document">
        <div class="modal-content">
            {{ $slot }}
        </div>
    </div>
</div>
