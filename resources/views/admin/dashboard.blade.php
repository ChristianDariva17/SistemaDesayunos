@extends('layouts.admin')

@section('title', 'Dashboard - Caldos & Desayunos')
@section('document_title', 'Dashboard - Caldos & Desayunos')

@section('admin_content')
                <div class="container-fluid py-4">

                    {{-- Encabezado del Dashboard --}}
                    <x-page-header
                        title="Dashboard - Panel de Control"
                        subtitle="Resumen general del sistema"
                        icon="fas fa-tachometer-alt"
                        subtitle-icon="far fa-chart-bar"
                        class="animate__animated animate__fadeInDown"
                    />

                    {{-- Bienvenida --}}
                    <x-alert type="info" :title="'¡Bienvenido, '.(Auth::user()->name ?? 'Usuario').'!'" class="mb-4 animate__animated animate__fadeIn">
                        Este es el panel de control del sistema de gestión <strong>Caldos & Desayunos</strong>
                    </x-alert>

                    {{-- ============================================
        TARJETAS DE ESTADÍSTICAS - FILA 1
    ============================================= --}}
                    <div class="row g-4 mb-4">

                        {{-- Total Productos --}}
                        <div class="col-xl-3 col-md-6 animate__animated animate__fadeInUp">
                            <x-stat-card title="Total Productos" :value="$totalProductos ?? 0" subtitle="Activos: {{ $productosActivos ?? 0 }}" icon="fas fa-box" color="primary" :href="route('admin.productos.index')" />
                        </div>

                        {{-- Total Clientes --}}
                        <div class="col-xl-3 col-md-6 animate__animated animate__fadeInUp" style="animation-delay: 0.1s;">
                            <x-stat-card title="Total Clientes" :value="$totalClientes ?? 0" subtitle="Activos: {{ $clientesActivos ?? 0 }}" icon="fas fa-users" color="success" :href="route('admin.clientes.index')" />
                        </div>

                        {{-- Pedidos Pendientes --}}
                        <div class="col-xl-3 col-md-6 animate__animated animate__fadeInUp" style="animation-delay: 0.2s;">
                            <x-stat-card title="Pedidos Pendientes" :value="$pedidosPendientes ?? 0" subtitle="Total: {{ $totalPedidos ?? 0 }}" icon="fas fa-clock" color="warning" :href="route('admin.pedidos.index', ['estado' => 'pendiente'])" />
                        </div>

                        {{-- Stock Bajo --}}
                        <div class="col-xl-3 col-md-6 animate__animated animate__fadeInUp" style="animation-delay: 0.3s;">
                            <x-stat-card title="Stock Bajo" :value="$stockBajo ?? 0" subtitle="Productos críticos" icon="fas fa-exclamation-triangle" color="danger" :href="route('admin.productos.index') . '?stock=bajo'" footer-text="Ver productos" />
                        </div>

                    </div>

                    {{-- ============================================
        TARJETAS DE ESTADÍSTICAS - FILA 2
    ============================================= --}}
                    <div class="row g-4 mb-4">

                        {{-- Total Ventas --}}
                        <div class="col-xl-3 col-md-6 animate__animated animate__fadeInUp" style="animation-delay: 0.4s;">
                            <x-stat-card title="Total Ventas" value="S/ {{ number_format($totalVentas ?? 0, 2) }}" subtitle="Pedidos completados" icon="fas fa-dollar-sign" color="info" />
                        </div>

                        {{-- Ventas del Mes --}}
                        <div class="col-xl-3 col-md-6 animate__animated animate__fadeInUp" style="animation-delay: 0.5s;">
                            <x-stat-card title="Ventas del Mes" value="S/ {{ number_format($ventasMes ?? 0, 2) }}" subtitle="{{ now()->translatedFormat('F Y') }}" icon="fas fa-calendar" color="primary" />
                        </div>

                        {{-- Pedidos Completados --}}
                        <div class="col-xl-3 col-md-6 animate__animated animate__fadeInUp" style="animation-delay: 0.6s;">
                            <x-stat-card title="Pedidos Completados" :value="$pedidosCompletados ?? 0" subtitle="Entregados" icon="fas fa-check-circle" color="success" />
                        </div>

                        {{-- Total Empleados --}}
                        <div class="col-xl-3 col-md-6 animate__animated animate__fadeInUp" style="animation-delay: 0.7s;">
                            <x-stat-card title="Total Empleados" :value="$totalEmpleados ?? 0" subtitle="Personal activo" icon="fas fa-user-tie" color="secondary" />
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
                                            <a href="{{ route('admin.productos.create') }}"
                                                class="btn btn-outline-primary w-100 py-4 btn-quick-access">
                                                <i class="fas fa-plus-circle fa-3x mb-3 d-block"></i>
                                                <strong>Nuevo Producto</strong>
                                                <br>
                                                <small class="text-muted">Agregar al inventario</small>
                                            </a>
                                        </div>
                                        <div class="col-lg-3 col-md-6">
                                            <a href="{{ route('admin.clientes.create') }}"
                                                class="btn btn-outline-success w-100 py-4 btn-quick-access">
                                                <i class="fas fa-user-plus fa-3x mb-3 d-block"></i>
                                                <strong>Nuevo Cliente</strong>
                                                <br>
                                                <small class="text-muted">Registrar cliente</small>
                                            </a>
                                        </div>
                                        <div class="col-lg-3 col-md-6">
                                            <a href="{{ route('admin.pedidos.create') }}"
                                                class="btn btn-outline-warning w-100 py-4 btn-quick-access">
                                                <i class="fas fa-cart-plus fa-3x mb-3 d-block"></i>
                                                <strong>Nuevo Pedido</strong>
                                                <br>
                                                <small class="text-muted">Crear pedido</small>
                                            </a>
                                        </div>
                                        <div class="col-lg-3 col-md-6">
                                            <a href="{{ route('admin.reportes.index') }}"
                                                class="btn btn-outline-info w-100 py-4 btn-quick-access">
                                                <i class="fas fa-chart-line fa-3x mb-3 d-block"></i>
                                                <strong>Ver Reportes</strong>
                                                <br>
                                                <small class="text-muted">Análisis y estadísticas</small>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
@endsection
