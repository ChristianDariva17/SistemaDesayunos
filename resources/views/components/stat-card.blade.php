@props([
    'title',
    'value',
    'subtitle' => null,
    'icon',
    'color' => 'primary',
    'href' => null,
    'footerText' => 'Ver todos',
    'uppercaseTitle' => true,
])

<div class="card border-left-{{ $color }} shadow-sm h-100 hover-shadow">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col">
                <div @class([
                    'text-xs font-weight-bold text-'.$color.' mb-2',
                    'text-uppercase' => $uppercaseTitle,
                ])>
                    <i class="{{ $icon }} me-1" aria-hidden="true"></i>{{ $title }}
                </div>
                <div class="h4 mb-0 font-weight-bold text-gray-800">
                    {{ $value }}
                </div>
                @if($subtitle)
                    <small class="text-muted">{{ $subtitle }}</small>
                @endif
            </div>
            <div class="col-auto">
                <i class="{{ $icon }} fa-3x text-{{ $color }} opacity-25" aria-hidden="true"></i>
            </div>
        </div>
    </div>

    @if($href)
        <div class="card-footer bg-transparent border-top-0">
            <a href="{{ $href }}" class="small text-{{ $color }} text-decoration-none">
                {{ $footerText }} <i class="fas fa-arrow-right ms-1" aria-hidden="true"></i>
            </a>
        </div>
    @endif
</div>
