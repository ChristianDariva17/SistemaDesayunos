@props([
    'type' => 'secondary',
    'icon' => null,
    'outlined' => false,
])

@php
    $classes = $outlined
        ? "badge bg-{$type} bg-opacity-10 text-{$type} border border-{$type}"
        : "badge bg-{$type}";
@endphp

<span {{ $attributes->class($classes) }}>
    @if($icon)
        <i class="{{ $icon }} me-1" aria-hidden="true"></i>
    @endif
    {{ $slot }}
</span>
