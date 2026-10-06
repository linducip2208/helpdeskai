@props(['active' => false])

<a {{ $attributes->merge(['class' => 'nav-link' . (($active ?? false) ? ' active' : '')]) }}>
    <span class="nav-link-title">{{ $slot }}</span>
</a>
