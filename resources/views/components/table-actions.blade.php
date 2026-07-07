@props(['label' => 'Acciones'])

<div {{ $attributes->class(['btn-group']) }} role="group" aria-label="{{ $label }}">
    {{ $slot }}
</div>
