<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    
    {{-- CSRF Token --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    {{-- SEO Meta Tags --}}
    <meta name="description" content="Panel de Control - Sistema de Gestión Caldos & Desayunos - Trabajador">
    <meta name="keywords" content="dashboard, panel, control, trabajador, gestión, restaurante">
    <meta name="author" content="Caldos & Desayunos">
    
    {{-- Favicon --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    
    {{-- Title --}}
    <title>Dashboard Trabajador - Caldos & Desayunos</title>
    
    {{-- ============================================
        ESTILOS CSS
    ============================================= --}}
    
    {{-- Bootstrap 5.3 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    {{-- Font Awesome 6 --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    {{-- Animate.css --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    {{-- ============================================
        ESTILOS PERSONALIZADOS
    ============================================= --}}
    <style>
        /* ============================================
           VARIABLES CSS
        ============================================= */
        :root {
            --primary-color: #4e73df;
            --secondary-color: #858796;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --light-color: #f8f9fc;
            --dark-color: #5a5c69;
            --font-family: 'Poppins', sans-serif;
        }

        /* ============================================
           ESTILOS GENERALES
        ============================================= */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-family);
            background-color: var(--light-color);
            color: var(--dark-color);
            font-size: 14px;
            line-height: 1.6;
        }

        /* ============================================
           NAVBAR SUPERIOR
        ============================================= */
        .navbar-top {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 1rem 1.5rem;
        }

        .navbar-brand {
            color: white !important;
            font-weight: 700;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .navbar-user {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .navbar-user .user-name {
            color: white;
            font-weight: 500;
        }

        .navbar-user .badge-role {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        /* ============================================
           TARJETAS DE ESTADÍSTICAS
        ============================================= */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }

        .border-left-primary {
            border-left: 4px solid var(--primary-color) !important;
        }

        .border-left-success {
            border-left: 4px solid var(--success-color) !important;
        }

        .border-left-info {
            border-left: 4px solid var(--info-color) !important;
        }

        .border-left-warning {
            border-left: 4px solid var(--warning-color) !important;
        }

        .border-left-danger {
            border-left: 4px solid var(--danger-color) !important;
        }

        .border-left-secondary {
            border-left: 4px solid var(--secondary-color) !important;
        }

        /* ============================================
           COLORES DE TEXTO
        ============================================= */
        .text-gray-800 {
            color: #5a5c69 !important;
        }

        .text-xs {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        /* ============================================
           EFECTOS Y ANIMACIONES
        ============================================= */
        .hover-shadow {
            transition: all 0.3s ease;
        }

        .hover-shadow:hover {
            transform: translateY(-5px);
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175) !important;
        }

        .opacity-25 {
            opacity: 0.25;
        }

        /* ============================================
           BOTONES DE ACCESO RÁPIDO
        ============================================= */
        .btn-quick-access {
            transition: all 0.3s ease;
            border-width: 2px;
            text-decoration: none !important;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            border-radius: 12px;
        }

        .btn-quick-access:hover {
            transform: scale(1.05);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .btn-quick-access i {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        /* ============================================
           ALERTAS
        ============================================= */
        .alert {
            border-radius: 10px;
            border: none;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        /* ============================================
           TABLAS
        ============================================= */
        .table {
            border-radius: 10px;
            overflow: hidden;
        }

        .table thead {
            background-color: var(--light-color);
        }

        .table-hover tbody tr:hover {
            background-color: rgba(78, 115, 223, 0.05);
        }

        /* ============================================
           BADGES
        ============================================= */
        .badge {
            padding: 0.5rem 0.8rem;
            font-weight: 600;
            border-radius: 8px;
        }

        /* ============================================
           RESPONSIVO
        ============================================= */
        @media (max-width: 768px) {
            .navbar-brand {
                font-size: 1rem;
            }

            .btn-quick-access i {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>

{{-- ============================================
    NAVBAR SUPERIOR
============================================= --}}
<nav class="navbar navbar-top mb-4">
    <div class="container-fluid">
        <span class="navbar-brand mb-0 h1">
            <i class="fas fa-utensils"></i>
            Caldos & Desayunos - Panel Trabajador
        </span>
        <div class="navbar-user">
            <span class="user-name">
                <i class="fas fa-user me-1"></i>
                {{ Auth::user()->name ?? 'Trabajador' }}
            </span>
            <span class="badge-role">
                <i class="fas fa-id-badge me-1"></i>
                Trabajador
            </span>
            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-light btn-sm">
                    <i class="fas fa-sign-out-alt me-1"></i>
                    Cerrar Sesión
                </button>
            </form>
        </div>
    </div>
</nav>

{{-- ============================================
    CONTENIDO PRINCIPAL
============================================= --}}
<div class="container-fluid py-4">
    
    {{-- ============================================
        ENCABEZADO DEL DASHBOARD
    ============================================= --}}
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

    {{-- ============================================
        ALERTA DE BIENVENIDA
    ============================================= --}}
    <div class="alert alert-info alert-dismissible fade show mb-4 animate__animated animate__fadeIn" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-info-circle fa-2x me-3"></i>
            <div>
                <h5 class="alert-heading mb-1">
                    ¡Bienvenido, {{ Auth::user()->name ?? 'Usuario' }}!
                </h5>
                <p class="mb-0">
                    Este es tu panel de control del sistema de gestión <strong>Caldos & Desayunos</strong>
                </p>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    {{-- ============================================
        ALERTAS DE SESIÓN
    ============================================= --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <strong>¡Éxito!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <strong>Error!</strong> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

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
                            <div class="text-xs font-weight-bold text-primary mb-2">
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
                    <a href="{{ route('trabajador.productos.index') }}" class="small text-primary text-decoration-none">
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
                            <div class="text-xs font-weight-bold text-success mb-2">
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
                    <a href="{{ route('trabajador.clientes.index') }}" class="small text-success text-decoration-none">
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
                            <div class="text-xs font-weight-bold text-warning mb-2">
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
                    <a href="{{ route('trabajador.pedidos.index') }}" class="small text-warning text-decoration-none">
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
                            <div class="text-xs font-weight-bold text-danger mb-2">
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
                    <a href="{{ route('trabajador.productos.index') }}?stock=bajo" class="small text-danger text-decoration-none">
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
        <div class="col-xl-6 col-md-6 animate__animated animate__fadeInUp" style="animation-delay: 0.4s;">
            <div class="card border-left-info shadow-sm h-100 hover-shadow">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-info mb-2">
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
        <div class="col-xl-6 col-md-6 animate__animated animate__fadeInUp" style="animation-delay: 0.5s;">
            <div class="card border-left-primary shadow-sm h-100 hover-shadow">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-primary mb-2">
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

</div>

{{-- ============================================
    SCRIPTS JAVASCRIPT
============================================= --}}

{{-- Bootstrap JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

{{-- Script para auto-cerrar alertas --}}
<script>
    // Auto-cerrar alertas después de 5 segundos
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
</script>

</body>
</html>
