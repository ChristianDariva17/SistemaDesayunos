@extends('layouts.trabajador')

@section('title', 'Dashboard Trabajador - Caldos & Desayunos')

@section('content')

    
    {{-- ============================================
        ENCABEZADO DEL DASHBOARD
    ============================================= --}}
    <x-page-header
        title="Dashboard - Panel de Control"
        subtitle="Resumen general del sistema"
        icon="fas fa-tachometer-alt"
        subtitle-icon="far fa-chart-bar"
        class="animate__animated animate__fadeInDown"
    />

    {{-- ============================================
        ALERTA DE BIENVENIDA
    ============================================= --}}
    <x-alert type="info" :title="'¡Bienvenido, '.(Auth::user()->name ?? 'Usuario').'!'" class="mb-4 animate__animated animate__fadeIn">
        Este es tu panel de control del sistema de gestión <strong>Caldos & Desayunos</strong>
    </x-alert>

    {{-- ============================================
        ALERTAS DE SESIÓN
    ============================================= --}}
    @if(session('success'))
        <x-alert type="success" title="¡Éxito!" class="mb-4">{{ session('success') }}</x-alert>
    @endif

    @if(session('error'))
        <x-alert type="danger" title="Error!" class="mb-4">{{ session('error') }}</x-alert>
    @endif

    {{-- ============================================
        TARJETAS DE ESTADÍSTICAS - FILA 1
    ============================================= --}}
    <div class="row g-4 mb-4">
        
        {{-- Total Productos --}}
        <div class="col-xl-3 col-md-6 animate__animated animate__fadeInUp">
            <x-stat-card
                title="Total Productos"
                :value="$totalProductos ?? 0"
                subtitle="Activos: {{ $productosActivos ?? 0 }}"
                icon="fas fa-box"
                color="primary"
                :href="route('trabajador.productos.index')"
                :uppercase-title="false"
            />
        </div>

        {{-- Total Clientes --}}
        <div class="col-xl-3 col-md-6 animate__animated animate__fadeInUp" style="animation-delay: 0.1s;">
            <x-stat-card
                title="Total Clientes"
                :value="$totalClientes ?? 0"
                subtitle="Activos: {{ $clientesActivos ?? 0 }}"
                icon="fas fa-users"
                color="success"
                :href="route('trabajador.clientes.index')"
                :uppercase-title="false"
            />
        </div>

        {{-- Pedidos Pendientes --}}
        <div class="col-xl-3 col-md-6 animate__animated animate__fadeInUp" style="animation-delay: 0.2s;">
            <x-stat-card
                title="Pedidos Pendientes"
                :value="$pedidosPendientes ?? 0"
                subtitle="Total: {{ $totalPedidos ?? 0 }}"
                icon="fas fa-clock"
                color="warning"
                :href="route('trabajador.pedidos.index', ['estado' => 'pendiente'])"
                :uppercase-title="false"
            />
        </div>

        {{-- Stock Bajo --}}
        <div class="col-xl-3 col-md-6 animate__animated animate__fadeInUp" style="animation-delay: 0.3s;">
            <x-stat-card
                title="Stock Bajo"
                :value="$stockBajo ?? 0"
                subtitle="Productos críticos"
                icon="fas fa-exclamation-triangle"
                color="danger"
                :href="route('trabajador.productos.index') . '?stock=bajo'"
                footer-text="Ver productos"
                :uppercase-title="false"
            />
        </div>

    </div>

    {{-- ============================================
        TARJETAS DE ESTADÍSTICAS - FILA 2
    ============================================= --}}
    <div class="row g-4 mb-4">
        
        {{-- Total Ventas --}}
        <div class="col-xl-6 col-md-6 animate__animated animate__fadeInUp" style="animation-delay: 0.4s;">
            <x-stat-card
                title="Total Ventas"
                value="S/ {{ number_format($totalVentas ?? 0, 2) }}"
                subtitle="Pedidos completados"
                icon="fas fa-dollar-sign"
                color="info"
                :uppercase-title="false"
            />
        </div>

        {{-- Ventas del Mes --}}
        <div class="col-xl-6 col-md-6 animate__animated animate__fadeInUp" style="animation-delay: 0.5s;">
            <x-stat-card
                title="Ventas del Mes"
                value="S/ {{ number_format($ventasMes ?? 0, 2) }}"
                subtitle="{{ now()->translatedFormat('F Y') }}"
                icon="fas fa-calendar"
                color="primary"
                :uppercase-title="false"
            />
        </div>

    </div>

    {{-- ============================================
        GRÁFICOS Y TABLAS
    ============================================= --}}
    <div class="row g-4 mb-4">
        
        {{-- Productos más vendidos --}}
        <div class="col-lg-6 animate__animated animate__fadeInLeft">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-chart-bar me-2"></i>Top 5 - Productos Más Vendidos
                    </h6>
                    <i class="fas fa-star"></i>
                </div>
                <div class="card-body">
                    @if(isset($productosMasVendidos) && $productosMasVendidos->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th>Producto</th>
                                        <th class="text-center" width="20%">Cantidad</th>
                                        <th class="text-end" width="25%">Ingresos</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($productosMasVendidos as $index => $producto)
                                    <tr>
                                        <td class="text-muted">{{ $index + 1 }}</td>
                                        <td>
                                            <strong>{{ $producto->nombre }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $producto->categoria ?? 'Sin categoría' }}</small>
                                        </td>
                                        <td class="text-center">
                                            <x-badge type="primary">{{ $producto->total_vendido ?? 0 }}</x-badge>
                                        </td>
                                        <td class="text-end">
                                            <strong class="text-success">S/ {{ number_format($producto->ingresos ?? 0, 2) }}</strong>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <x-empty-state icon="fas fa-box-open" message="No hay datos de ventas disponibles" />
                    @endif
                </div>
            </div>
        </div>

        {{-- Últimos pedidos --}}
        <div class="col-lg-6 animate__animated animate__fadeInRight">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-shopping-cart me-2"></i>Últimos 5 Pedidos
                    </h6>
                    <i class="fas fa-history"></i>
                </div>
                <div class="card-body">
                    @if(isset($ultimosPedidos) && $ultimosPedidos->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="10%">ID</th>
                                        <th>Cliente</th>
                                        <th width="20%">Estado</th>
                                        <th class="text-end" width="20%">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($ultimosPedidos as $pedido)
                                    <tr>
                                        <td><strong>#{{ $pedido->id }}</strong></td>
                                        <td>
                                            {{ $pedido->cliente->nombre ?? 'Cliente no disponible' }}
                                            <br>
                                            <small class="text-muted">{{ $pedido->created_at->diffForHumans() }}</small>
                                        </td>
                                        <td>
                                            @if($pedido->estado == 'completado')
                                                <x-badge type="success" icon="fas fa-check">Completado</x-badge>
                                            @elseif($pedido->estado == 'pendiente')
                                                <x-badge type="warning" icon="fas fa-clock">Pendiente</x-badge>
                                            @else
                                                <x-badge type="danger" icon="fas fa-times">Cancelado</x-badge>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <strong class="text-success">S/ {{ number_format($pedido->total, 2) }}</strong>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <x-empty-state icon="fas fa-shopping-cart" message="No hay pedidos recientes" />
                    @endif
                </div>
            </div>
        </div>

    </div>

    {{-- ============================================
        ACCESOS RÁPIDOS
    ============================================= --}}
    <div class="row g-4">
        <div class="col-12 animate__animated animate__fadeInUp" style="animation-delay: 0.8s;">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-bolt me-2"></i>Accesos Rápidos
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('trabajador.pedidos.create') }}" 
                               class="btn btn-outline-primary w-100 py-4 btn-quick-access">
                                <i class="fas fa-cart-plus"></i>
                                <strong>Nuevo Pedido</strong>
                                <br>
                                <small class="text-muted">Crear pedido</small>
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('trabajador.pedidos.index') }}" 
                               class="btn btn-outline-success w-100 py-4 btn-quick-access">
                                <i class="fas fa-list"></i>
                                <strong>Ver Pedidos</strong>
                                <br>
                                <small class="text-muted">Listado completo</small>
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('trabajador.productos.index') }}" 
                               class="btn btn-outline-info w-100 py-4 btn-quick-access">
                                <i class="fas fa-box"></i>
                                <strong>Ver Productos</strong>
                                <br>
                                <small class="text-muted">Catálogo completo</small>
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('trabajador.clientes.index') }}" 
                               class="btn btn-outline-warning w-100 py-4 btn-quick-access">
                                <i class="fas fa-users"></i>
                                <strong>Ver Clientes</strong>
                                <br>
                                <small class="text-muted">Base de datos</small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
