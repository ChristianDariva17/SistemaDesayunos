<!-- Productos Section con Tailwind CSS -->
<div class="space-y-6">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-orange-600 mb-2 flex items-center">
            <i class="fas fa-utensils mr-3"></i>
            Gestión de Productos
        </h1>
        <p class="text-gray-600 flex items-center">
            <i class="fas fa-info-circle mr-2 text-orange-500"></i>
            Administra la información de todos tus productos
        </p>
    </div>

    <!-- Botones superiores -->
    <div class="flex justify-end mb-6 space-x-3">
        <button class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-3 rounded-lg flex items-center transition-colors shadow-sm font-medium">
            <i class="fas fa-download mr-2"></i>
            Exportar
        </button>
        <button class="bg-orange-600 hover:bg-orange-700 text-white px-6 py-3 rounded-lg flex items-center transition-colors shadow-md font-medium">
            <i class="fas fa-plus mr-2"></i>
            Nuevo Producto
        </button>
    </div>

    <!-- Métricas (4 Cards) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <!-- Total Productos -->
        <div class="bg-white rounded-xl p-6 shadow-lg hover:shadow-xl transition-shadow duration-300">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <p class="text-gray-600 text-sm font-medium mb-2">Total Productos</p>
                    <h3 class="text-4xl font-bold text-gray-900">{{ count($productos ?? []) }}</h3>
                </div>
                <div class="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-boxes text-blue-600 text-2xl"></i>
                </div>
            </div>
            <p class="text-orange-600 text-sm font-medium flex items-center">
                <i class="fas fa-box mr-1"></i> Registrados
            </p>
        </div>

        <!-- Productos Activos -->
        <div class="bg-white rounded-xl p-6 shadow-lg hover:shadow-xl transition-shadow duration-300">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <p class="text-gray-600 text-sm font-medium mb-2">Productos Activos</p>
                    <h3 class="text-4xl font-bold text-gray-900">{{ count($productos ?? []) }}</h3>
                </div>
                <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                </div>
            </div>
            <p class="text-green-600 text-sm font-medium flex items-center">
                <i class="fas fa-circle mr-1 text-xs"></i> En operación
            </p>
        </div>

        <!-- Nuevos Este Mes -->
        <div class="bg-white rounded-xl p-6 shadow-lg hover:shadow-xl transition-shadow duration-300">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <p class="text-gray-600 text-sm font-medium mb-2">Nuevos Este Mes</p>
                    <h3 class="text-4xl font-bold text-gray-900">2</h3>
                </div>
                <div class="w-14 h-14 bg-purple-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-plus-circle text-purple-600 text-2xl"></i>
                </div>
            </div>
            <p class="text-purple-600 text-sm font-medium flex items-center">
                <i class="fas fa-calendar mr-1"></i> Oct 2025
            </p>
        </div>

        <!-- Con Pedidos -->
        <div class="bg-white rounded-xl p-6 shadow-lg hover:shadow-xl transition-shadow duration-300">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <p class="text-gray-600 text-sm font-medium mb-2">Con Pedidos</p>
                    <h3 class="text-4xl font-bold text-gray-900">{{ count($productos ?? []) }}</h3>
                </div>
                <div class="w-14 h-14 bg-orange-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-shopping-cart text-orange-600 text-2xl"></i>
                </div>
            </div>
            <p class="text-orange-600 text-sm font-medium flex items-center">
                <i class="fas fa-lock mr-1"></i> Activos en ventas
            </p>
        </div>
    </div>

    <!-- Filtros de Búsqueda -->
    <div class="bg-gradient-to-r from-orange-50 to-orange-100 rounded-xl p-6 shadow-md mb-6 border border-orange-200">
        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-search mr-2 text-orange-600"></i>
            Filtros de Búsqueda
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Buscar producto</label>
                <div class="relative">
                    <input type="text" 
                           id="searchProduct"
                           placeholder="Nombre, descripción..." 
                           class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all outline-none">
                    <i class="fas fa-search absolute left-3 top-4 text-gray-400"></i>
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Estado</label>
                <select class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all outline-none">
                    <option>Todos los estados</option>
                    <option>Activo</option>
                    <option>Inactivo</option>
                    <option>Agotado</option>
                </select>
            </div>
        </div>
        
        <div class="flex justify-end space-x-3">
            <button class="bg-orange-600 hover:bg-orange-700 text-white px-8 py-3 rounded-lg flex items-center transition-colors shadow-md font-medium">
                <i class="fas fa-search mr-2"></i>
                Buscar
            </button>
            <button class="bg-white hover:bg-gray-50 text-gray-700 px-6 py-3 rounded-lg flex items-center transition-colors border-2 border-gray-300 font-medium">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <!-- Lista de Productos (Cards estilo Gestión de Clientes) -->
    <div class="space-y-4" id="productList">
        @forelse($productos ?? [] as $producto)
        <div class="bg-gradient-to-r from-orange-400 to-orange-600 rounded-xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:scale-[1.01] product-card">
            <!-- Header del Card -->
            <div class="p-6 text-white">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <!-- Avatar circular -->
                        <div class="w-16 h-16 bg-white bg-opacity-30 backdrop-blur-sm rounded-full flex items-center justify-center text-3xl font-bold shadow-lg">
                            {{ strtoupper(substr($producto['nombre'], 0, 1)) }}
                        </div>
                        
                        <!-- Nombre y fecha -->
                        <div>
                            <h3 class="text-2xl font-bold mb-1">{{ $producto['nombre'] }}</h3>
                            <p class="text-orange-100 text-sm flex items-center">
                                <i class="fas fa-calendar mr-2"></i>
                                Producto desde {{ $producto['fecha_registro'] ?? 'Sep 2025' }}
                            </p>
                        </div>
                    </div>

                    <!-- Badge Estado -->
                    <div>
                        <span class="bg-green-100 text-green-800 px-5 py-2 rounded-full text-sm font-bold shadow-md">
                            Activo
                        </span>
                    </div>
                </div>
            </div>

            <!-- Detalles (Fondo blanco) -->
            <div class="bg-white p-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-6">
                    <!-- ID -->
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-hashtag text-blue-600"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 font-medium">ID</p>
                            <p class="text-lg font-bold text-gray-900">{{ $producto['id'] }}</p>
                        </div>
                    </div>

                    <!-- Nombre -->
                    <div class="flex items-center space-x-3 sm:col-span-2">
                        <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-tag text-purple-600"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-500 font-medium">Nombre</p>
                            <p class="text-sm font-bold text-gray-900 truncate">{{ $producto['nombre'] }}</p>
                        </div>
                    </div>

                    <!-- Descripción -->
                    <div class="flex items-center space-x-3 sm:col-span-2">
                        <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-align-left text-indigo-600"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-500 font-medium">Descripción</p>
                            <p class="text-sm font-semibold text-gray-700 truncate">{{ $producto['descripcion'] }}</p>
                        </div>
                    </div>

                    <!-- Precio -->
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-dollar-sign text-green-600"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 font-medium">Precio</p>
                            <p class="text-xl font-bold text-green-600">S/ {{ number_format($producto['precio'], 2) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="mt-6 flex flex-wrap gap-3 justify-end">
                    <button class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg flex items-center transition-colors shadow-md font-medium">
                        <i class="fas fa-eye mr-2"></i>
                        Ver
                    </button>
                    <button class="bg-yellow-500 hover:bg-yellow-600 text-white px-6 py-2 rounded-lg flex items-center transition-colors shadow-md font-medium">
                        <i class="fas fa-edit mr-2"></i>
                        Editar
                    </button>
                    <button class="bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded-lg flex items-center transition-colors shadow-md font-medium">
                        <i class="fas fa-trash mr-2"></i>
                        Eliminar
                    </button>
                </div>
            </div>
        </div>
        @empty
        <!-- Sin productos -->
        <div class="bg-white rounded-xl shadow-lg p-16 text-center">
            <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-box-open text-gray-400 text-4xl"></i>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-2">No hay productos registrados</h3>
            <p class="text-gray-600 mb-6">Comienza agregando tu primer producto al inventario</p>
            <button class="bg-orange-600 hover:bg-orange-700 text-white px-8 py-3 rounded-lg inline-flex items-center transition-colors shadow-md font-medium">
                <i class="fas fa-plus mr-2"></i>
                Agregar Primer Producto
            </button>
        </div>
        @endforelse
    </div>
</div>

<!-- Script de búsqueda -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchProduct');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const cards = document.querySelectorAll('.product-card');
            
            cards.forEach(card => {
                const text = card.textContent.toLowerCase();
                card.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    }
});
</script>
