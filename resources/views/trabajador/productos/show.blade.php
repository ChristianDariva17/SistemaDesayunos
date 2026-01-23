<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    
    {{-- CSRF Token --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    {{-- SEO Meta Tags --}}
    <meta name="description" content="Detalle de Producto - {{ $producto->nombre }} - Sistema Caldos & Desayunos">
    <meta name="keywords" content="producto, detalle, {{ $producto->nombre }}">
    <meta name="author" content="Caldos & Desayunos">
    
    {{-- Favicon --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    
    {{-- Title --}}
    <title>{{ $producto->nombre }} - Caldos & Desayunos</title>
    
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
           TARJETAS
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
           IMAGEN DE PRODUCTO
        ============================================= */
        .product-image-container {
            position: relative;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        .product-image-container img {
            width: 100%;
            height: 400px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .product-image-container:hover img {
            transform: scale(1.05);
        }

        .product-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            z-index: 10;
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
           BADGES
        ============================================= */
        .badge {
            padding: 0.5rem 0.8rem;
            font-weight: 600;
            border-radius: 8px;
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
           ANIMACIONES
        ============================================= */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade {
            animation: fadeIn 0.5s ease;
        }

        /* ============================================
           LISTA DE INFORMACIÓN
        ============================================= */
        .info-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .info-list li {
            padding: 0.75rem 0;
            border-bottom: 1px solid #e3e6f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .info-list li:last-child {
            border-bottom: none;
        }

        .info-list .label {
            color: #858796;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .info-list .value {
            color: #5a5c69;
            font-weight: 500;
        }
    </style>
</head>
<body>

{{-- ============================================
    NAVBAR SUPERIOR
============================================= --}}
<nav class="navbar navbar-top mb-4">
    <div class="container-fluid">
        <a href="{{ route('trabajador.dashboard') }}" class="navbar-brand mb-0 h1">
            <i class="fas fa-utensils"></i>
            Caldos & Desayunos - Panel Trabajador
        </a>
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
        ENCABEZADO
    ============================================= --}}
    <div class="d-flex justify-content-between align-items-center mb-4 animate-fade">
        <div>
            <h1 class="h3 mb-1 text-gray-800">
                <i class="fas fa-box text-primary me-2"></i>
                <strong>Detalle del Producto</strong>
            </h1>
            <p class="text-muted mb-0">
                <i class="far fa-info-circle me-1"></i>
                Información completa del producto
            </p>
        </div>
        <div>
            <a href="{{ route('trabajador.productos.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Volver al listado
            </a>
        </div>
    </div>

    {{-- ============================================
        ALERTAS DE SESIÓN
    ============================================= --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4 animate-fade" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle fa-2x me-3"></i>
                <div>
                    <h5 class="alert-heading mb-1">¡Éxito!</h5>
                    <p class="mb-0">{{ session('success') }}</p>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ============================================
        TARJETAS DE ESTADÍSTICAS
    ============================================= --}}
    <div class="row g-4 mb-4">
        
        {{-- Precio del Producto --}}
        <div class="col-xl-3 col-md-6 animate-fade">
            <div class="card border-left-success shadow-sm h-100 hover-shadow">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-success mb-2">
                                <i class="fas fa-dollar-sign me-1"></i>Precio
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                S/ {{ number_format($producto->precio, 2) }}
                            </div>
                            <small class="text-muted">
                                Precio unitario
                            </small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-money-bill-wave fa-3x text-success opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Stock Disponible --}}
        <div class="col-xl-3 col-md-6 animate-fade">
            <div class="card border-left-{{ $producto->stock == 0 ? 'danger' : ($producto->stock < 10 ? 'warning' : 'info') }} shadow-sm h-100 hover-shadow">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-{{ $producto->stock == 0 ? 'danger' : ($producto->stock < 10 ? 'warning' : 'info') }} mb-2">
                                <i class="fas fa-boxes me-1"></i>Stock Disponible
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                {{ $producto->stock }} und.
                            </div>
                            <small class="text-muted">
                                @if($producto->stock == 0)
                                    Sin stock
                                @elseif($producto->stock < 10)
                                    Stock bajo
                                @else
                                    Stock normal
                                @endif
                            </small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-warehouse fa-3x text-{{ $producto->stock == 0 ? 'danger' : ($producto->stock < 10 ? 'warning' : 'info') }} opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Valor en Inventario --}}
        <div class="col-xl-3 col-md-6 animate-fade">
            <div class="card border-left-primary shadow-sm h-100 hover-shadow">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-primary mb-2">
                                <i class="fas fa-calculator me-1"></i>Valor en Inventario
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                S/ {{ number_format($producto->precio * $producto->stock, 2) }}
                            </div>
                            <small class="text-muted">
                                Precio × Stock
                            </small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-3x text-primary opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Estado del Producto --}}
        <div class="col-xl-3 col-md-6 animate-fade">
            <div class="card border-left-{{ $producto->estado == 'activo' ? 'success' : 'danger' }} shadow-sm h-100 hover-shadow">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-{{ $producto->estado == 'activo' ? 'success' : 'danger' }} mb-2">
                                <i class="fas fa-toggle-{{ $producto->estado == 'activo' ? 'on' : 'off' }} me-1"></i>Estado
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                {{ ucfirst($producto->estado) }}
                            </div>
                            <small class="text-muted">
                                {{ $producto->estado == 'activo' ? 'Disponible' : 'No disponible' }}
                            </small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-{{ $producto->estado == 'activo' ? 'check-circle' : 'times-circle' }} fa-3x text-{{ $producto->estado == 'activo' ? 'success' : 'danger' }} opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ============================================
        INFORMACIÓN DEL PRODUCTO
    ============================================= --}}
    <div class="row g-4">
        
        {{-- COLUMNA IZQUIERDA - IMAGEN Y DETALLES --}}
        <div class="col-lg-5 animate-fade">
            
            {{-- IMAGEN DEL PRODUCTO --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-image me-2"></i>Imagen del Producto
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="product-image-container">
                        @if($producto->imagen)
                            <img src="{{ asset('storage/' . $producto->imagen) }}" 
                                 alt="{{ $producto->nombre }}" 
                                 class="img-fluid">
                            
                            {{-- Badge de Estado --}}
                            <div class="product-badge">
                                @if($producto->estado == 'activo')
                                    <span class="badge bg-success fs-6">
                                        <i class="fas fa-check me-1"></i>Activo
                                    </span>
                                @else
                                    <span class="badge bg-danger fs-6">
                                        <i class="fas fa-times me-1"></i>Inactivo
                                    </span>
                                @endif
                            </div>
                        @else
                            <div class="text-center py-5 bg-light">
                                <i class="fas fa-image fa-5x text-secondary opacity-25 mb-3"></i>
                                <p class="text-muted">Sin imagen disponible</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- CATEGORÍA --}}
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-tag me-2"></i>Categoría
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center py-3">
                        @if($producto->categoria)
                            <span class="badge bg-info fs-5">
                                <i class="fas fa-folder-open me-2"></i>
                                {{ ucfirst($producto->categoria) }}
                            </span>
                        @else
                            <span class="badge bg-secondary fs-5">
                                <i class="fas fa-question me-2"></i>
                                Sin categoría
                            </span>
                        @endif
                    </div>
                </div>
            </div>

        </div>

        {{-- COLUMNA DERECHA - INFORMACIÓN DETALLADA --}}
        <div class="col-lg-7 animate-fade">
            
            {{-- INFORMACIÓN GENERAL --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-info-circle me-2"></i>Información General
                    </h6>
                </div>
                <div class="card-body">
                    <h3 class="text-gray-800 mb-3">
                        <strong>{{ $producto->nombre }}</strong>
                    </h3>
                    
                    <ul class="info-list">
                        <li>
                            <span class="label">
                                <i class="fas fa-hashtag me-2"></i>ID del Producto
                            </span>
                            <span class="value">
                                <strong>#{{ $producto->id }}</strong>
                            </span>
                        </li>

                        <li>
                            <span class="label">
                                <i class="fas fa-align-left me-2"></i>Descripción
                            </span>
                            <span class="value">
                                {{ $producto->descripcion ?? 'Sin descripción' }}
                            </span>
                        </li>

                        @if($producto->sku)
                        <li>
                            <span class="label">
                                <i class="fas fa-barcode me-2"></i>SKU
                            </span>
                            <span class="value">
                                <code class="bg-light px-2 py-1 rounded">{{ $producto->sku }}</code>
                            </span>
                        </li>
                        @endif

                        @if($producto->codigo_barras)
                        <li>
                            <span class="label">
                                <i class="fas fa-barcode me-2"></i>Código de Barras
                            </span>
                            <span class="value">
                                <code class="bg-light px-2 py-1 rounded">{{ $producto->codigo_barras }}</code>
                            </span>
                        </li>
                        @endif

                        <li>
                            <span class="label">
                                <i class="far fa-calendar-plus me-2"></i>Fecha de Creación
                            </span>
                            <span class="value">
                                {{ $producto->created_at->format('d/m/Y H:i') }}
                                <small class="text-muted">({{ $producto->created_at->diffForHumans() }})</small>
                            </span>
                        </li>

                        <li>
                            <span class="label">
                                <i class="far fa-calendar-check me-2"></i>Última Actualización
                            </span>
                            <span class="value">
                                {{ $producto->updated_at->format('d/m/Y H:i') }}
                                <small class="text-muted">({{ $producto->updated_at->diffForHumans() }})</small>
                            </span>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- HISTORIAL DE PEDIDOS (SI EXISTE) --}}
            @if(isset($producto->pedidos) && $producto->pedidos->count() > 0)
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-history me-2"></i>Últimos Pedidos
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Pedido</th>
                                    <th>Cliente</th>
                                    <th>Fecha</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($producto->pedidos->take(5) as $pedido)
                                <tr>
                                    <td><strong>#{{ $pedido->id }}</strong></td>
                                    <td>{{ $pedido->cliente->nombre ?? 'N/A' }}</td>
                                    <td>
                                        <small>{{ $pedido->created_at->format('d/m/Y') }}</small>
                                    </td>
                                    <td class="text-center">
                                        @if($pedido->estado == 'completado')
                                            <span class="badge bg-success">Completado</span>
                                        @elseif($pedido->estado == 'pendiente')
                                            <span class="badge bg-warning">Pendiente</span>
                                        @else
                                            <span class="badge bg-danger">Cancelado</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <strong>S/ {{ number_format($pedido->total, 2) }}</strong>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($producto->pedidos->count() > 5)
                <div class="card-footer text-center bg-transparent">
                    <small class="text-muted">
                        Mostrando 5 de {{ $producto->pedidos->count() }} pedidos
                    </small>
                </div>
                @endif
            </div>
            @endif

        </div>

    </div>

</div>

{{-- ============================================
    SCRIPTS JAVASCRIPT
============================================= --}}

{{-- Bootstrap JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

{{-- Scripts personalizados --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // AUTO-CERRAR ALERTAS DESPUÉS DE 5 SEGUNDOS
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

    });
</script>

</body>
</html>
