@props([
    'title',
    'subtitle' => null,
    'subtitleIcon' => null,
    'icon' => null,
    'iconColor' => 'primary',
    'showTimestamp' => true,
])

<div {{ $attributes->class(['d-flex justify-content-between align-items-center mb-4']) }}>
    <div>
        <h1 class="h3 mb-1 text-gray-800">
            @if($icon)
                <i class="{{ $icon }} text-{{ $iconColor }} me-2" aria-hidden="true"></i>
            @endif
            <strong>{{ $title }}</strong>
        </h1>

        @if($subtitle)
            <p class="text-muted mb-0">
                @if($subtitleIcon)
                    <i class="{{ $subtitleIcon }} me-1" aria-hidden="true"></i>
                @endif
                {{ $subtitle }}
            </p>
        @endif
    </div>

    @if($showTimestamp)
        <div class="text-end">
            <p class="mb-0 text-muted">
                <i class="far fa-calendar-alt me-2" aria-hidden="true"></i>
                {{ now()->translatedFormat('l, d \d\e F \d\e Y') }}
            </p>
            <p class="mb-0 text-muted small">
                <i class="far fa-clock me-1" aria-hidden="true"></i>
                {{ now()->format('h:i A') }}
            </p>
        </div>
    @endif
</div>
