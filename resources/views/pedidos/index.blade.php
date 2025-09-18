@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Header Section -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div class="mb-4 sm:mb-0">
            <h1 class="text-3xl font-bold bg-gradient-to-r from-orange-600 via-red-600 to-pink-600 bg-clip-text text-transparent">
                Gestión de Pedidos
            </h1>
            <p class="text-gray-600 mt-1 flex items-center">
                <i class="fas fa-shopping-cart mr-2 text-orange-500"></i>
                Administra todos los pedidos de desayunos y caldos
            </p>
        </div>
        <div class="flex space-x-3">
            <button class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-gray-100 to-gray-200 hover:from-gray-200 hover:to-gray-300 text-gray-700 font-medium rounded-lg shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200">
                <i class="fas fa-download mr-2"></i>
                Exportar
            </button>
            <a href="{{ route('pedidos.create') }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white font-medium rounded-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                <i class="fas fa-plus mr-2"></i>
                Nuevo Pedido
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl p-6 shadow-lg border border-gray-100 hover:shadow-xl transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Pedidos Hoy</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $pedidos->where('fecha', today())->count() }}</p>
                    <p class="text-sm text-orange-600 font-medium">
                        <i class="fas fa-calendar-day mr-1"></i>
                        {{ today()->format('d M, Y') }}
                    </p>
                </div>
                <div class="h-12 w-12 bg-gradient-to-r from-blue-100 to-cyan-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-shopping-cart text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl p-6 shadow-lg border border-gray-100 hover:shadow-xl transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Pendientes</p>
                    <p class="text-2xl font-bold text-amber-600">{{ $pedidos->where('estado', 'pendiente')->count() }}</p>
                    <p class="text-sm text-amber-600 font-medium">
                        <i class="fas fa-clock mr-1"></i>
                        Requieren atención
                    </p>
                </div>
                <div class="h-12 w-12 bg-gradient-to-r from-amber-100 to-orange-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-clock text-amber-600 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl p-6 shadow-lg border border-gray-100 hover:shadow-xl transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Completados</p>
                    <p class="text-2xl font-bold text-green-600">{{ $pedidos->where('estado', 'completado')->count() }}</p>
                    <p class="text-sm text-green-600 font-medium">
                        <i class="fas fa-check-circle mr-1"></i>
                        Este mes
                    </p>
                </div>
                <div class="h-12 w-12 bg-gradient-to-r from-green-100 to-emerald-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl p-6 shadow-lg border border-gray-100 hover:shadow-xl transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Ventas</p>
                    <p class="text-2xl font-bold bg-gradient-to-r from-orange-600 to-red-600 bg-clip-text text-transparent">
                        ${{ number_format($pedidos->sum('total'), 0) }}
                    </p>
                    <p class="text-sm text-orange-600 font-medium">
                        <i class="fas fa-trending-up mr-1"></i>
                        +15% vs anterior
                    </p>
                </div>
                <div class="h-12 w-12 bg-gradient-to-r from-orange-100 to-red-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-dollar-sign text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 mb-6 overflow-hidden">
        <div class="bg-gradient-to-r from-orange-50 to-red-50 px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <i class="fas fa-filter mr-2 text-orange-600"></i>
                Filtros de Búsqueda
            </h3>
        </div>
        <div class="p-6">
            <form method="GET" action="{{ route('pedidos.index') }}">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Buscar pedido</label>
                        <div class="relative">
                            <input type="text" 
                                   name="search"
                                   value="{{ request('search') }}"
                                   placeholder="N° de pedido, cliente..." 
                                   class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors duration-200 bg-white">
                            <i class="fas fa-search absolute left-3 top-3.5 text-gray-400"></i>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                        <select name="estado" class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors duration-200 bg-white">
                            <option value="">Todos los estados</option>
                            <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                            <option value="procesando" {{ request('estado') == 'procesando' ? 'selected' : '' }}>Procesando</option>
                            <option value="completado" {{ request('estado') == 'completado' ? 'selected' : '' }}>Completado</option>
                            <option value="cancelado" {{ request('estado') == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fecha desde</label>
                        <input type="date" 
                               name="fecha_desde"
                               value="{{ request('fecha_desde') }}"
                               class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors duration-200 bg-white">
                    </div>
                    
                    <div class="flex items-end space-x-2">
                        <button type="submit" class="flex-1 bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white font-medium py-3 px-4 rounded-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                            <i class="fas fa-filter mr-2"></i>
                            Filtrar
                        </button>
                        <a href="{{ route('pedidos.index') }}" class="px-4 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors duration-200">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Table Section -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gradient-to-r from-gray-50 to-slate-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            N° Pedido
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Cliente
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Empleado
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Fecha & Hora
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Total
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Estado
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($pedidos as $pedido)
                        <tr class="hover:bg-gradient-to-r hover:from-orange-50/30 hover:to-red-50/30 transition-all duration-200">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-bold text-orange-600">{{ $pedido->numero_pedido }}</div>
                                <div class="text-xs text-gray-500">
                                    <i class="fas fa-calendar mr-1"></i>
                                    {{ $pedido->created_at->diffForHumans() }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 bg-gradient-to-r from-orange-400 to-red-500 rounded-full flex items-center justify-center mr-3 shadow-md">
                                        <span class="text-white text-sm font-bold">{{ substr($pedido->cliente->name, 0, 1) }}</span>
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $pedido->cliente->name }}</div>
                                        <div class="text-sm text-gray-500 flex items-center">
                                            <i class="fas fa-envelope mr-1"></i>
                                            {{ $pedido->cliente->email }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <i class="fas fa-user-tie mr-2 text-blue-500"></i>
                                    <span class="text-sm font-medium text-gray-900">{{ $pedido->empleado->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 font-medium">
                                    <i class="fas fa-calendar-alt mr-1 text-orange-500"></i>
                                    {{ $pedido->fecha->format('d/m/Y') }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    <i class="fas fa-clock mr-1 text-gray-400"></i>
                                    {{ $pedido->hora->format('H:i') }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-lg font-bold bg-gradient-to-r from-green-600 to-emerald-600 bg-clip-text text-transparent">
                                    ${{ number_format($pedido->total, 2) }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $pedido->detalles->count() }} productos
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusConfig = [
                                        'pendiente' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-800', 'border' => 'border-amber-200', 'icon' => 'fa-clock'],
                                        'procesando' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'border' => 'border-blue-200', 'icon' => 'fa-cogs'],
                                        'completado' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'border' => 'border-green-200', 'icon' => 'fa-check-circle'],
                                        'cancelado' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'border' => 'border-red-200', 'icon' => 'fa-times-circle']
                                    ];
                                    $config = $statusConfig[$pedido->estado] ?? $statusConfig['pendiente'];
                                @endphp
                                <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full border {{ $config['bg'] }} {{ $config['text'] }} {{ $config['border'] }}">
                                    <i class="fas {{ $config['icon'] }} mr-1"></i>
                                    {{ ucfirst($pedido->estado) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('pedidos.show', $pedido) }}" 
                                       class="p-2 text-blue-600 bg-blue-100 rounded-lg hover:bg-blue-200 hover:scale-110 transition-all duration-200 shadow-sm"
                                       title="Ver detalles">
                                        <i class="fas fa-eye text-sm"></i>
                                    </a>
                                    <a href="{{ route('pedidos.edit', $pedido) }}" 
                                       class="p-2 text-amber-600 bg-amber-100 rounded-lg hover:bg-amber-200 hover:scale-110 transition-all duration-200 shadow-sm"
                                       title="Editar pedido">
                                        <i class="fas fa-edit text-sm"></i>
                                    </a>
                                    <button onclick="deletePedido({{ $pedido->id }})" 
                                            class="p-2 text-red-600 bg-red-100 rounded-lg hover:bg-red-200 hover:scale-110 transition-all duration-200 shadow-sm"
                                            title="Eliminar pedido">
                                        <i class="fas fa-trash text-sm"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="h-24 w-24 bg-gradient-to-r from-orange-100 to-red-100 rounded-full flex items-center justify-center mb-4">
                                        <i class="fas fa-shopping-cart text-orange-400 text-3xl"></i>
                                    </div>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">No hay pedidos registrados</h3>
                                    <p class="text-gray-500 mb-6 max-w-sm">Comienza creando tu primer pedido de desayunos o caldos para tus clientes</p>
                                    <a href="{{ route('pedidos.create') }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white font-medium rounded-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                                        <i class="fas fa-plus mr-2"></i>
                                        Crear Primer Pedido
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($pedidos->hasPages())
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Mostrando {{ $pedidos->firstItem() }} a {{ $pedidos->lastItem() }} de {{ $pedidos->total() }} resultados
                    </div>
                    {{ $pedidos->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Delete Modal -->
<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 max-w-md mx-4 shadow-2xl">
        <div class="flex items-center justify-center w-16 h-16 bg-gradient-to-r from-red-100 to-pink-100 rounded-full mx-auto mb-4">
            <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
        </div>
        <h3 class="text-xl font-semibold text-gray-900 text-center mb-2">¿Eliminar pedido?</h3>
        <p class="text-gray-600 text-center mb-6">Esta acción no se puede deshacer. El pedido y todos sus detalles serán eliminados permanentemente.</p>
        <div class="flex space-x-3">
            <button id="cancelDelete" class="flex-1 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition-colors duration-200">
                Cancelar
            </button>
            <form id="deleteForm" method="POST" class="flex-1">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full px-4 py-2 bg-gradient-to-r from-red-500 to-pink-600 hover:from-red-600 hover:to-pink-700 text-white font-medium rounded-lg transition-all duration-200 transform hover:-translate-y-0.5">
                    Confirmar Eliminar
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    function deletePedido(id) {
        const modal = document.getElementById('deleteModal');
        const form = document.getElementById('deleteForm');
        
        form.action = `/pedidos/${id}`;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    document.getElementById('cancelDelete').addEventListener('click', function() {
        const modal = document.getElementById('deleteModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    });

    // Close modal when clicking outside
    document.getElementById('deleteModal').addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.add('hidden');
            this.classList.remove('flex');
        }
    });
</script>
@endsection
