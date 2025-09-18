<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title . ' - ' : '' }}Caldos & Desayunos</title>

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-inter bg-gray-50">
    <div class="flex h-full">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-xl border-r border-gray-200 flex flex-col">
            <!-- Logo -->
            <div class="flex items-center h-16 px-6 bg-gradient-to-r from-orange-500 to-red-500 flex-shrink-0">
                <div class="flex items-center">
                    <div class="h-8 w-8 bg-white rounded-lg flex items-center justify-center mr-3">
                        <span class="text-orange-500 text-lg font-bold">🥣</span>
                    </div>
                    <h1 class="text-white font-bold text-lg">Caldos & Desayunos</h1>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 mt-8 px-4 overflow-y-auto">
                <div class="space-y-2">
                    <a href="{{ route('dashboard') }}" 
                       class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gradient-to-r hover:from-orange-50 hover:to-red-50 hover:text-orange-600 transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-gradient-to-r from-orange-50 to-red-50 text-orange-600 border-r-3 border-orange-500' : '' }}">
                        <i class="fas fa-chart-pie w-5 h-5 mr-3"></i>
                        <span class="font-medium">Dashboard</span>
                    </a>

                    <a href="{{ route('productos.index') }}" 
                       class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gradient-to-r hover:from-orange-50 hover:to-red-50 hover:text-orange-600 transition-all duration-200 {{ request()->routeIs('productos.*') ? 'bg-gradient-to-r from-orange-50 to-red-50 text-orange-600 border-r-3 border-orange-500' : '' }}">
                        <i class="fas fa-utensils w-5 h-5 mr-3"></i>
                        <span class="font-medium">Productos</span>
                    </a>

                    <a href="{{ route('clientes.index') }}" 
                       class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gradient-to-r hover:from-orange-50 hover:to-red-50 hover:text-orange-600 transition-all duration-200 {{ request()->routeIs('clientes.*') ? 'bg-gradient-to-r from-orange-50 to-red-50 text-orange-600 border-r-3 border-orange-500' : '' }}">
                        <i class="fas fa-users w-5 h-5 mr-3"></i>
                        <span class="font-medium">Clientes</span>
                    </a>

                    <a href="{{ route('pedidos.index') }}" 
                       class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gradient-to-r hover:from-orange-50 hover:to-red-50 hover:text-orange-600 transition-all duration-200 {{ request()->routeIs('pedidos.*') ? 'bg-gradient-to-r from-orange-50 to-red-50 text-orange-600 border-r-3 border-orange-500' : '' }}">
                        <i class="fas fa-shopping-cart w-5 h-5 mr-3"></i>
                        <span class="font-medium">Pedidos</span>
                    </a>

                    <a href="{{ route('empleados.index') }}" 
                       class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gradient-to-r hover:from-orange-50 hover:to-red-50 hover:text-orange-600 transition-all duration-200 {{ request()->routeIs('empleados.*') ? 'bg-gradient-to-r from-orange-50 to-red-50 text-orange-600 border-r-3 border-orange-500' : '' }}">
                        <i class="fas fa-user-tie w-5 h-5 mr-3"></i>
                        <span class="font-medium">Empleados</span>
                    </a>
                </div>

                <!-- Acciones Rápidas -->
                <div class="my-6">
                    <div class="border-t border-gray-200"></div>
                </div>
                <div class="space-y-2">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-4">Acciones Rápidas</p>
                    <a href="{{ route('productos.create') }}" class="flex items-center px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition-all duration-200">
                        <i class="fas fa-plus w-4 h-4 mr-3"></i>Nuevo Producto
                    </a>
                    <a href="{{ route('pedidos.create') }}" class="flex items-center px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-green-50 hover:text-green-600 transition-all duration-200">
                        <i class="fas fa-shopping-bag w-4 h-4 mr-3"></i>Nuevo Pedido
                    </a>
                    <a href="{{ route('clientes.create') }}" class="flex items-center px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-purple-50 hover:text-purple-600 transition-all duration-200">
                        <i class="fas fa-user-plus w-4 h-4 mr-3"></i>Nuevo Cliente
                    </a>
                </div>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <header class="bg-white shadow-sm border-b border-gray-200 flex-shrink-0">
                <div class="px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between items-center h-16">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">@yield('page-title', 'Dashboard')</h1>
                        </div>
                        <div class="flex items-center space-x-4">
                            <div class="text-sm text-gray-500">
                                <span>{{ now()->format('l, d M Y') }}</span>
                            </div>
                            @yield('header-actions')
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto">
                <div class="p-6">
                    @if(session('success'))
                        <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-6 py-4 rounded-lg">
                            <div class="flex items-center">
                                <i class="fas fa-check-circle mr-3 text-green-500"></i>
                                <span>{{ session('success') }}</span>
                            </div>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-6 py-4 rounded-lg">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-circle mr-3 text-red-500"></i>
                                <span>{{ session('error') }}</span>
                            </div>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>
    </div>
    <script>
        // Toggle mobile sidebar (opcional para móviles)
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const sidebar = document.getElementById('sidebar');
        
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('-translate-x-full');
            });
        }
</script>
</body>
</html>

