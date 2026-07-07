@props([
    'icon' => 'fas fa-inbox',
    'title' => null,
    'message' => null,
    'actionUrl' => null,
    'actionText' => null,
    'actionIcon' => null,
    'actionVariant' => 'primary',
])

<div {{ $attributes->class(['text-center py-5']) }}>
    <div class="text-muted">
        <i class="{{ $icon }} fa-3x mb-3 d-block text-secondary opacity-50" aria-hidden="true"></i>
        @if($title)
            <h5 class="fw-bold">{{ $title }}</h5>
        @endif
        @if($message)
            <p class="mb-3">{{ $message }}</p>
        @endif
        {{ $slot }}
        @if($actionUrl && $actionText)
            <a href="{{ $actionUrl }}" class="btn btn-{{ $actionVariant }}">
                @if($actionIcon)
                    <i class="{{ $actionIcon }} me-2" aria-hidden="true"></i>
                @endif
                {{ $actionText }}
            </a>
        @endif
    </div>
</div>
