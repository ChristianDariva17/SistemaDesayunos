@extends('layouts.admin')

@section('title', 'Dashboard - Caldos & Desayunos')
@section('document_title', 'Dashboard - Caldos & Desayunos')

@push('styles')
    <style>
        /* ============================================
   ESTILOS ADICIONALES PARA EL DASHBOARD
============================================= */

        /* Bordes de colores para las tarjetas */
        .border-left-primary {
            border-left: 0.35rem solid #4e73df !important;
        }

        .border-left-success {
            border-left: 0.35rem solid #1cc88a !important;
        }

        .border-left-info {
            border-left: 0.35rem solid #36b9cc !important;
        }

        .border-left-warning {
            border-left: 0.35rem solid #f6c23e !important;
        }

        .border-left-danger {
            border-left: 0.35rem solid #e74a3b !important;
        }

        .border-left-secondary {
            border-left: 0.35rem solid #858796 !important;
        }

        /* Colores de texto */
        .text-gray-800 {
            color: #5a5c69 !important;
        }

        .text-gray-300 {
            color: #dddfeb !important;
        }

        .text-xs {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        /* Efectos hover en tarjetas */
        .hover-shadow {
            transition: all 0.3s ease;
        }

        .hover-shadow:hover {
            transform: translateY(-5px);
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175) !important;
        }

        /* Botones de acceso rápido */
        .btn-quick-access {
            transition: all 0.3s ease;
            border-width: 2px;
            text-decoration: none !important;
        }

        .btn-quick-access:hover {
            transform: scale(1.05);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        /* Animaciones de opacidad */
        .opacity-25 {
            opacity: 0.25;
        }
    </style>
@endpush

@section('admin_content')
                <div class="container-fluid py-4">

                    {{-- Encabezado del Dashboard --}}
                    <div class="d-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeInDown">
                        <div>
                            <h1 class="h3 mb-1 text-gray-800">
                                <i class="fas fa-tachometer-alt text-primary me-2"></i>
                                <strong>Dashboard - Panel de Control</strong>
                            </h1>
                            <p class="text-muted mb-0">
                                <i class="far fa-chart-bar me-1"></i>
                                Resumen general del sistema
                            </p>
                        </div>
                        <div class="text-end">
                            <p class="mb-0 text-muted">
                                <i class="far fa-calendar-alt me-2"></i>
                                {{ now()->translatedFormat('l, d \d\e F \d\e Y') }}
                            </p>
                            <p class="mb-0 text-muted small">
                                <i class="far fa-clock me-1"></i>
                                {{ now()->format('h:i A') }}
                            </p>
                        </div>
                    </div>

                    {{-- Bienvenida --}}
                    <div class="alert alert-info alert-dismissible fade show mb-4 animate__animated animate__fadeIn" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-info-circle fa-2x me-3"></i>
                            <div>
                                <h5 class="alert-heading mb-1">
                                    ¡Bienvenido, {{ Auth::user()->name ?? 'Usuario' }}!
                                </h5>
                                <p class="mb-0">
                                    Este es el panel de control del sistema de gestión <strong>Caldos & Desayunos</strong>
                                </p>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>

                    {{-- ============================================
        TARJETAS DE ESTADÍSTICAS - FILA 1
    ============================================= --}}
                    <div class="row g-4 mb-4">

                        {{-- Total Productos --}}
                        <div class="col-xl-3 col-md-6 animate__animated animate__fadeInUp">
                            <div class="card border-left-primary shadow-sm h-100 hover-shadow">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-2">
                                                <i class="fas fa-box me-1"></i>Total Productos
                                            </div>
                                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                                {{ $totalProductos ?? 0 }}
                                            </div>
                                            <small class="text-muted">
                                                Activos: {{ $productosActivos ?? 0 }}
                                            </small>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-box fa-3x text-primary opacity-25"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent border-top-0">
                                    <a href="{{ route('admin.productos.index') }}" class="small text-primary text-decoration-none">
                                        Ver todos <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- Total Clientes --}}
                        <div class="col-xl-3 col-md-6 animate__animated animate__fadeInUp" style="animation-delay: 0.1s;">
                            <div class="card border-left-success shadow-sm h-100 hover-shadow">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-2">
                                                <i class="fas fa-users me-1"></i>Total Clientes
                                            </div>
                                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                                {{ $totalClientes ?? 0 }}
                                            </div>
                                            <small class="text-muted">
                                                Activos: {{ $clientesActivos ?? 0 }}
                                            </small>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-users fa-3x text-success opacity-25"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent border-top-0">
                                    <a href="{{ route('admin.clientes.index') }}" class="small text-success text-decoration-none">
                                        Ver todos <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- Pedidos Pendientes --}}
                        <div class="col-xl-3 col-md-6 animate__animated animate__fadeInUp" style="animation-delay: 0.2s;">
                            <div class="card border-left-warning shadow-sm h-100 hover-shadow">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-2">
                                                <i class="fas fa-clock me-1"></i>Pedidos Pendientes
                                            </div>
                                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                                {{ $pedidosPendientes ?? 0 }}
                                            </div>
                                            <small class="text-muted">
                                                Total: {{ $totalPedidos ?? 0 }}
                                            </small>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-clock fa-3x text-warning opacity-25"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent border-top-0">
                                    <a href="{{ route('admin.pedidos.index') }}" class="small text-warning text-decoration-none">
                                        Ver todos <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- Stock Bajo --}}
                        <div class="col-xl-3 col-md-6 animate__animated animate__fadeInUp" style="animation-delay: 0.3s;">
                            <div class="card border-left-danger shadow-sm h-100 hover-shadow">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-2">
                                                <i class="fas fa-exclamation-triangle me-1"></i>Stock Bajo
                                            </div>
                                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                                {{ $stockBajo ?? 0 }}
                                            </div>
                                            <small class="text-muted">
                                                Productos críticos
                                            </small>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-exclamation-triangle fa-3x text-danger opacity-25"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent border-top-0">
                                    <a href="{{ route('admin.productos.index') }}?stock=bajo" class="small text-danger text-decoration-none">
                                        Ver productos <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- ============================================
        TARJETAS DE ESTADÍSTICAS - FILA 2
    ============================================= --}}
                    <div class="row g-4 mb-4">

                        {{-- Total Ventas --}}
                        <div class="col-xl-3 col-md-6 animate__animated animate__fadeInUp" style="animation-delay: 0.4s;">
                            <div class="card border-left-info shadow-sm h-100 hover-shadow">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-2">
                                                <i class="fas fa-dollar-sign me-1"></i>Total Ventas
                                            </div>
                                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                                S/ {{ number_format($totalVentas ?? 0, 2) }}
                                            </div>
                                            <small class="text-muted">
                                                Pedidos completados
                                            </small>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-dollar-sign fa-3x text-info opacity-25"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Ventas del Mes --}}
                        <div class="col-xl-3 col-md-6 animate__animated animate__fadeInUp" style="animation-delay: 0.5s;">
                            <div class="card border-left-primary shadow-sm h-100 hover-shadow">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-2">
                                                <i class="fas fa-calendar me-1"></i>Ventas del Mes
                                            </div>
                                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                                S/ {{ number_format($ventasMes ?? 0, 2) }}
                                            </div>
                                            <small class="text-muted">
                                                {{ now()->translatedFormat('F Y') }}
                                            </small>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-calendar fa-3x text-primary opacity-25"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Pedidos Completados --}}
                        <div class="col-xl-3 col-md-6 animate__animated animate__fadeInUp" style="animation-delay: 0.6s;">
                            <div class="card border-left-success shadow-sm h-100 hover-shadow">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-2">
                                                <i class="fas fa-check-circle me-1"></i>Pedidos Completados
                                            </div>
                                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                                {{ $pedidosCompletados ?? 0 }}
                                            </div>
                                            <small class="text-muted">
                                                Entregados
                                            </small>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-check-circle fa-3x text-success opacity-25"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Total Empleados --}}
                        <div class="col-xl-3 col-md-6 animate__animated animate__fadeInUp" style="animation-delay: 0.7s;">
                            <div class="card border-left-secondary shadow-sm h-100 hover-shadow">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-2">
                                                <i class="fas fa-user-tie me-1"></i>Total Empleados
                                            </div>
                                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                                {{ $totalEmpleados ?? 0 }}
                                            </div>
                                            <small class="text-muted">
                                                Personal activo
                                            </small>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-user-tie fa-3x text-secondary opacity-25"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
                                                        <span class="badge bg-primary">{{ $producto->total_vendido ?? 0 }}</span>
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
                                    <div class="text-center py-5">
                                        <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                        <p class="text-muted mb-0">No hay datos de ventas disponibles</p>
                                    </div>
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
                                                        <span class="badge bg-success">
                                                            <i class="fas fa-check me-1"></i>Completado
                                                        </span>
                                                        @elseif($pedido->estado == 'pendiente')
                                                        <span class="badge bg-warning">
                                                            <i class="fas fa-clock me-1"></i>Pendiente
                                                        </span>
                                                        @else
                                                        <span class="badge bg-danger">
                                                            <i class="fas fa-times me-1"></i>Cancelado
                                                        </span>
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
                                    <div class="text-center py-5">
                                        <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                                        <p class="text-muted mb-0">No hay pedidos recientes</p>
                                    </div>
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
