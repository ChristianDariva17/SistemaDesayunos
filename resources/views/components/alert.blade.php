@props([
    'type' => 'info',
    'icon' => null,
    'title' => null,
    'dismissible' => true,
])

@php
    $icons = [
        'success' => 'fas fa-check-circle',
        'danger' => 'fas fa-exclamation-circle',
        'error' => 'fas fa-exclamation-circle',
        'warning' => 'fas fa-exclamation-triangle',
        'info' => 'fas fa-info-circle',
    ];

    $alertType = $type === 'error' ? 'danger' : $type;
    $iconClass = $icon ?? ($icons[$type] ?? $icons['info']);
@endphp

<div {{ $attributes->class(['alert', 'alert-'.$alertType, 'alert-dismissible fade show' => $dismissible]) }} role="alert" aria-live="assertive">
    <div class="d-flex align-items-center">
        <i class="{{ $iconClass }} fa-2x me-3" aria-hidden="true"></i>
        <div>
            @if($title)
                <h5 class="alert-heading mb-1">{{ $title }}</h5>
            @endif
            <p class="mb-0">{{ $slot }}</p>
        </div>
    </div>

    @if($dismissible)
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    @endif
</div>
