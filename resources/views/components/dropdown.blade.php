@props(['align' => 'right', 'width' => '48', 'contentClasses' => ''])

@php
$alignmentClasses = match ($align) {
    'left' => 'dropdown-menu-start',
    'top' => 'dropup',
    default => 'dropdown-menu-end',
};
@endphp

<div class="nav-item dropdown">
    <a href="#" class="nav-link" data-bs-toggle="dropdown" role="button" aria-expanded="false">
        {{ $trigger }}
    </a>
    <div class="dropdown-menu {{ $alignmentClasses }} {{ $contentClasses }}">
        {{ $content }}
    </div>
</div>
