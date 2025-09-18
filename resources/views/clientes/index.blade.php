@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Header Section -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div class="mb-4 sm:mb-0">
            <h1 class="text-3xl font-bold bg-gradient-to-r from-orange-600 via-red-600 to-pink-600 bg-clip-text text-transparent">
                Gestión de Clientes
            </h1>
            <p class="text-gray-600 mt-1 flex items-center">
                <i class="fas fa-users mr-2 text-orange-500"></i>
                Administra la información de todos tus clientes
            </p>
        </div>
        <div class="flex space-x-3">
            <button class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-gray-100 to-gray-200 hover:from-gray-200 hover:to-gray-300 text-gray-700 font-medium rounded-lg shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200">
                <i class="fas fa-download mr-2"></i>
                Exportar
            </button>
            <a href="{{ route('clientes.create') }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white font-medium rounded-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                <i class="fas fa-plus mr-2"></i>
                Nuevo Cliente
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl p-6 shadow-lg border border-gray-100 hover:shadow-xl transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Clientes</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $clientes->total() }}</p>
                    <p class="text-sm text-orange-600 font-medium">
                        <i class="fas fa-users mr-1"></i>
                        Registrados
                    </p>
                </div>
                <div class="h-12 w-12 bg-gradient-to-r from-blue-100 to-cyan-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-users text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl p-6 shadow-lg border border-gray-100 hover:shadow-xl transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Clientes Activos</p>
                    <p class="text-2xl font-bold text-green-600">{{ \App\Models\Cliente::where('estado', 'activo')->count() }}</p>
                    <p class="text-sm text-green-600 font-medium">
                        <i class="fas fa-check-circle mr-1"></i>
                        En operación
                    </p>
                </div>
                <div class="h-12 w-12 bg-gradient-to-r from-green-100 to-emerald-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-user-check text-green-600 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl p-6 shadow-lg border border-gray-100 hover:shadow-xl transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Nuevos Este Mes</p>
                    <p class="text-2xl font-bold text-purple-600">{{ \App\Models\Cliente::whereMonth('created_at', now()->month)->count() }}</p>
                    <p class="text-sm text-purple-600 font-medium">
                        <i class="fas fa-calendar mr-1"></i>
                        {{ now()->format('M Y') }}
                    </p>
                </div>
                <div class="h-12 w-12 bg-gradient-to-r from-purple-100 to-pink-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-user-plus text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl p-6 shadow-lg border border-gray-100 hover:shadow-xl transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Con Pedidos</p>
                    <p class="text-2xl font-bold bg-gradient-to-r from-orange-600 to-red-600 bg-clip-text text-transparent">
                        {{ \App\Models\Cliente::has('pedidos')->count() }}
                    </p>
                    <p class="text-sm text-orange-600 font-medium">
                        <i class="fas fa-shopping-bag mr-1"></i>
                        Activos en ventas
                    </p>
                </div>
                <div class="h-12 w-12 bg-gradient-to-r from-orange-100 to-red-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-shopping-cart text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 mb-6 overflow-hidden">
        <div class="bg-gradient-to-r from-orange-50 to-red-50 px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <i class="fas fa-search mr-2 text-orange-600"></i>
                Filtros de Búsqueda
            </h3>
        </div>
        <div class="p-6">
            <form method="GET" action="{{ route('clientes.index') }}">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Buscar cliente</label>
                        <div class="relative">
                            <input type="text" 
                                   name="search"
                                   value="{{ request('search') }}"
                                   placeholder="Nombre, apellido, email o teléfono..." 
                                   class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors duration-200 bg-white">
                            <i class="fas fa-search absolute left-3 top-3.5 text-gray-400"></i>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                        <select name="estado" class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors duration-200 bg-white">
                            <option value="">Todos los estados</option>
                            <option value="activo" {{ request('estado') == 'activo' ? 'selected' : '' }}>Activo</option>
                            <option value="inactivo" {{ request('estado') == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                        </select>
                    </div>
                    
                    <div class="flex items-end space-x-2">
                        <button type="submit" class="flex-1 bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white font-medium py-3 px-4 rounded-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                            <i class="fas fa-search mr-2"></i>
                            Buscar
                        </button>
                        <a href="{{ route('clientes.index') }}" class="px-4 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors duration-200">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Clientes Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        @forelse($clientes as $cliente)
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <!-- Header Card -->
                <div class="bg-gradient-to-r from-orange-500 to-red-500 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="h-12 w-12 bg-white/20 rounded-full flex items-center justify-center backdrop-blur-sm">
                                <span class="text-white text-lg font-bold">{{ substr($cliente->nombre, 0, 1) }}</span>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-white">{{ $cliente->nombre_completo }}</h3>
                                <p class="text-orange-100 text-sm">
                                    <i class="fas fa-calendar mr-1"></i>
                                    Cliente desde {{ $cliente->created_at->format('M Y') }}
                                </p>
                            </div>
                        </div>
                        <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $cliente->estado == 'activo' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ ucfirst($cliente->estado) }}
                        </span>
                    </div>
                </div>

                <!-- Card Body -->
                <div class="p-6">
                    <div class="space-y-3 mb-4">
                        @if($cliente->email)
                            <div class="flex items-center text-gray-600">
                                <i class="fas fa-envelope w-5 text-orange-500 mr-3"></i>
                                <span class="text-sm">{{ $cliente->email }}</span>
                            </div>
                        @endif
                        
                        @if($cliente->telefono)
                            <div class="flex items-center text-gray-600">
                                <i class="fas fa-phone w-5 text-orange-500 mr-3"></i>
                                <span class="text-sm">{{ $cliente->telefono }}</span>
                            </div>
                        @endif
                        
                        @if($cliente->direccion)
                            <div class="flex items-center text-gray-600">
                                <i class="fas fa-map-marker-alt w-5 text-orange-500 mr-3"></i>
                                <span class="text-sm">{{ Str::limit($cliente->direccion, 30) }}</span>
                            </div>
                        @endif
                    </div>

                    <!-- Stats -->
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div class="text-center p-3 bg-gradient-to-r from-blue-50 to-cyan-50 rounded-lg">
                            <p class="text-2xl font-bold text-blue-600">{{ $cliente->pedidos->count() }}</p>
                            <p class="text-xs text-blue-600">Pedidos</p>
                        </div>
                        <div class="text-center p-3 bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg">
                            <p class="text-2xl font-bold text-green-600">${{ number_format($cliente->pedidos->sum('total'), 0) }}</p>
                            <p class="text-xs text-green-600">Total Gastado</p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex space-x-2">
                        <a href="{{ route('clientes.show', $cliente) }}" 
                           class="flex-1 text-center py-2 px-3 bg-gradient-to-r from-blue-500 to-cyan-500 hover:from-blue-600 hover:to-cyan-600 text-white text-sm font-medium rounded-lg shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200">
                            <i class="fas fa-eye mr-1"></i>
                            Ver
                        </a>
                        <a href="{{ route('clientes.edit', $cliente) }}" 
                           class="flex-1 text-center py-2 px-3 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white text-sm font-medium rounded-lg shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200">
                            <i class="fas fa-edit mr-1"></i>
                            Editar
                        </a>
                        <button onclick="deleteCliente({{ $cliente->id }})" 
                                class="py-2 px-3 bg-gradient-to-r from-red-500 to-pink-500 hover:from-red-600 hover:to-pink-600 text-white text-sm font-medium rounded-lg shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <div class="text-center py-16">
                    <div class="h-24 w-24 bg-gradient-to-r from-orange-100 to-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-users text-orange-400 text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No hay clientes registrados</h3>
                    <p class="text-gray-500 mb-6 max-w-sm mx-auto">Comienza agregando tu primer cliente para poder gestionar mejor tus pedidos</p>
                    <a href="{{ route('clientes.create') }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white font-medium rounded-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                        <i class="fas fa-plus mr-2"></i>
                        Crear Primer Cliente
                    </a>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($clientes->hasPages())
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Mostrando {{ $clientes->firstItem() }} a {{ $clientes->lastItem() }} de {{ $clientes->total() }} resultados
                    </div>
                    {{ $clientes->links() }}
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Delete Modal -->
<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 max-w-md mx-4 shadow-2xl">
        <div class="flex items-center justify-center w-16 h-16 bg-gradient-to-r from-red-100 to-pink-100 rounded-full mx-auto mb-4">
            <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
        </div>
        <h3 class="text-xl font-semibold text-gray-900 text-center mb-2">¿Eliminar cliente?</h3>
        <p class="text-gray-600 text-center mb-6">Esta acción eliminará permanentemente al cliente y todos sus datos asociados.</p>
        <div class="flex space-x-3">
            <button id="cancelDelete" class="flex-1 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition-colors duration-200">
                Cancelar
            </button>
            <form id="deleteForm" method="POST" class="flex-1">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full px-4 py-2 bg-gradient-to-r from-red-500 to-pink-600 hover:from-red-600 hover:to-pink-700 text-white font-medium rounded-lg transition-all duration-200 transform hover:-translate-y-0.5">
                    Eliminar Cliente
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    function deleteCliente(id) {
        const modal = document.getElementById('deleteModal');
        const form = document.getElementById('deleteForm');
        
        form.action = `/clientes/${id}`;
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
