<div class="bg-gradient-to-br from-{{ $color }}-500 to-{{ $color }}-600 rounded-2xl shadow-xl p-6 text-white transform hover:scale-105 transition-all duration-300">
    <div class="flex items-center justify-between mb-4">
        <div class="w-14 h-14 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
            <i class="fas fa-{{ $icon }} text-3xl"></i>
        </div>
    </div>
    <h3 class="text-3xl font-black mb-2">{{ $value }}</h3>
    <p class="text-{{ $color }}-100 font-medium">{{ $title }}</p>
    <p class="text-xs text-{{ $color }}-200 mt-1">{{ $subtitle }}</p>
    
    @if($route)
        <a href="{{ $route }}" class="text-xs text-{{ $color }}-100 hover:text-white mt-2 inline-flex items-center">
            Ver detalles <i class="fas fa-arrow-right ml-1"></i>
        </a>
    @endif
</div>
