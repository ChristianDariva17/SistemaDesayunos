<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    
    {{-- CSRF Token --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    {{-- SEO Meta Tags --}}
    <meta name="description" content="Detalle de Pedido #{{ $pedido->numero_pedido }} - Sistema Caldos & Desayunos">
    <meta name="keywords" content="pedido, detalle, {{ $pedido->numero_pedido }}">
    <meta name="author" content="Caldos & Desayunos">
    
    {{-- Favicon --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    
    {{-- Title --}}
    <title>Pedido #{{ $pedido->numero_pedido }} - Caldos & Desayunos</title>
    
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
                <i class="fas fa-receipt text-warning me-2"></i>
                <strong>Pedido #{{ $pedido->numero_pedido }}</strong>
            </h1>
            <p class="text-muted mb-0">
                <i class="far fa-calendar me-1"></i>
                Creado el {{ $pedido->fecha->format('d/m/Y') }} a las {{ \Carbon\Carbon::parse($pedido->fecha->format('Y-m-d') . ' ' . $pedido->hora)->format('H:i') }}
            </p>
        </div>
        <div>
            <a href="{{ route('trabajador.pedidos.index') }}" class="btn btn-secondary">
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

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4 animate-fade" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-circle fa-2x me-3"></i>
                <div>
                    <h5 class="alert-heading mb-1">Error!</h5>
                    <p class="mb-0">{{ session('error') }}</p>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ============================================
        TARJETAS DE INFORMACIÓN RÁPIDA
    ============================================= --}}
    <div class="row g-4 mb-4">
        
        {{-- Número de Pedido --}}
        <div class="col-xl-3 col-md-6 animate-fade">
            <div class="card border-left-primary shadow-sm h-100 hover-shadow">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-primary mb-2">
                                <i class="fas fa-hashtag me-1"></i>Número de Pedido
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                #{{ $pedido->numero_pedido }}
                            </div>
                            <small class="text-muted">
                                ID: {{ $pedido->id }}
                            </small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-receipt fa-3x text-primary opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Total del Pedido --}}
        <div class="col-xl-3 col-md-6 animate-fade">
            <div class="card border-left-success shadow-sm h-100 hover-shadow">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-success mb-2">
                                <i class="fas fa-dollar-sign me-1"></i>Total
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                S/ {{ number_format($pedido->total, 2) }}
                            </div>
                            <small class="text-muted">
                                Monto total
                            </small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-money-bill-wave fa-3x text-success opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Cantidad de Productos --}}
        <div class="col-xl-3 col-md-6 animate-fade">
            <div class="card border-left-info shadow-sm h-100 hover-shadow">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-info mb-2">
                                <i class="fas fa-boxes me-1"></i>Productos
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                {{ $pedido->productos->count() }}
                            </div>
                            <small class="text-muted">
                                {{ $pedido->productos->count() == 1 ? 'Producto' : 'Productos' }}
                            </small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-boxes fa-3x text-info opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Estado del Pedido --}}
        <div class="col-xl-3 col-md-6 animate-fade">
            <div class="card border-left-{{ $pedido->estado == 'completado' ? 'success' : ($pedido->estado == 'pendiente' ? 'warning' : ($pedido->estado == 'cancelado' ? 'danger' : 'info')) }} shadow-sm h-100 hover-shadow">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-{{ $pedido->estado == 'completado' ? 'success' : ($pedido->estado == 'pendiente' ? 'warning' : ($pedido->estado == 'cancelado' ? 'danger' : 'info')) }} mb-2">
                                <i class="fas fa-flag me-1"></i>Estado
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                {{ ucfirst($pedido->estado) }}
                            </div>
                            <small class="text-muted">
                                Estado actual
                            </small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-{{ $pedido->estado == 'completado' ? 'check-circle' : ($pedido->estado == 'pendiente' ? 'clock' : ($pedido->estado == 'cancelado' ? 'times-circle' : 'spinner')) }} fa-3x text-{{ $pedido->estado == 'completado' ? 'success' : ($pedido->estado == 'pendiente' ? 'warning' : ($pedido->estado == 'cancelado' ? 'danger' : 'info')) }} opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ============================================
        INFORMACIÓN PRINCIPAL
    ============================================= --}}
    <div class="row g-4">
        
        {{-- COLUMNA IZQUIERDA - INFORMACIÓN DEL PEDIDO --}}
        <div class="col-lg-6 animate-fade">
            
            {{-- INFORMACIÓN DEL CLIENTE --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-user me-2"></i>Información del Cliente
                    </h6>
                </div>
                <div class="card-body">
                    @if($pedido->cliente)
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-info bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3"
                                 style="width: 60px; height: 60px;">
                                <span class="h4 mb-0 text-info fw-bold">
                                    {{ strtoupper(substr($pedido->cliente->nombre, 0, 1)) }}{{ strtoupper(substr($pedido->cliente->apellido ?? '', 0, 1)) }}
                                </span>
                            </div>
                            <div>
                                <h5 class="mb-1 text-gray-800">
                                    {{ trim($pedido->cliente->nombre . ' ' . ($pedido->cliente->apellido ?? '')) }}
                                </h5>
                                <p class="mb-0 text-muted small">Cliente #{{ $pedido->cliente->id }}</p>
                            </div>
                        </div>

                        <ul class="info-list">
                            @if($pedido->cliente->email)
                            <li>
                                <span class="label">
                                    <i class="fas fa-envelope me-2"></i>Email
                                </span>
                                <span class="value">{{ $pedido->cliente->email }}</span>
                            </li>
                            @endif

                            @if($pedido->cliente->telefono)
                            <li>
                                <span class="label">
                                    <i class="fas fa-phone me-2"></i>Teléfono
                                </span>
                                <span class="value">{{ $pedido->cliente->telefono }}</span>
                            </li>
                            @endif

                            @if($pedido->cliente->direccion)
                            <li>
                                <span class="label">
                                    <i class="fas fa-map-marker-alt me-2"></i>Dirección
                                </span>
                                <span class="value">{{ $pedido->cliente->direccion }}</span>
                            </li>
                            @endif
                        </ul>
                    @else
                        <div class="alert alert-warning mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Cliente no disponible</strong>
                            <p class="mb-0 small">El cliente asociado a este pedido fue eliminado de la base de datos.</p>
                            @if($pedido->cliente_id)
                                <p class="mb-0 small">ID original: #{{ $pedido->cliente_id }}</p>
                            @else
                                <p class="mb-0 small">Este pedido no tiene un cliente asociado (cliente_id es NULL).</p>
                                <p class="mb-0 small">Esto puede ocurrir si se creó el pedido sin seleccionar cliente.</p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            {{-- INFORMACIÓN DEL EMPLEADO --}}
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-user-tie me-2"></i>Empleado Asignado
                    </h6>
                </div>
                <div class="card-body">
                    @if($pedido->empleado)
                        <div class="d-flex align-items-center">
                            <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3"
                                 style="width: 50px; height: 50px;">
                                <i class="fas fa-user-tie fa-2x text-primary"></i>
                            </div>
                            <div>
                                <h6 class="mb-1 text-gray-800">{{ $pedido->empleado->nombre }}</h6>
                                <p class="mb-0 text-muted small">Empleado #{{ $pedido->empleado->id }}</p>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Empleado no disponible</strong>
                            <p class="mb-0 small">
                                @if($pedido->empleado_id)
                                    El empleado asignado fue eliminado de la base de datos. ID original: #{{ $pedido->empleado_id }}
                                @else
                                    Este pedido no tiene un empleado asociado.
                                @endif
                            </p>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        {{-- COLUMNA DERECHA - PRODUCTOS Y DETALLES --}}
        <div class="col-lg-6 animate-fade">
            
            {{-- PRODUCTOS DEL PEDIDO --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-shopping-basket me-2"></i>Productos del Pedido
                    </h6>
                </div>
                <div class="card-body p-0">
                    @if($pedido->productos->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Producto</th>
                                        <th class="text-center">Cantidad</th>
                                        <th class="text-end">Precio Unit.</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pedido->productos as $producto)
                                    <tr>
                                        <td>
                                            <strong>{{ $producto->nombre }}</strong>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info">
                                                {{ $producto->pivot->cantidad }} {{ $producto->pivot->cantidad == 1 ? 'unidad' : 'unidades' }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <small>S/ {{ number_format($producto->pivot->precio_unitario, 2) }} c/u</small>
                                        </td>
                                        <td class="text-end">
                                            <strong class="text-success">S/ {{ number_format($producto->pivot->subtotal, 2) }}</strong>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>TOTAL:</strong></td>
                                        <td class="text-end">
                                            <h5 class="mb-0 text-success">
                                                <strong>S/ {{ number_format($pedido->total, 2) }}</strong>
                                            </h5>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-inbox fa-3x mb-3 opacity-25"></i>
                            <p class="mb-0">Este pedido no tiene productos asociados</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- DETALLES ADICIONALES --}}
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-info-circle me-2"></i>Detalles Adicionales
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="info-list">
                        @if($pedido->observaciones)
                        <li>
                            <span class="label">
                                <i class="fas fa-comment me-2"></i>Observaciones
                            </span>
                            <span class="value">{{ $pedido->observaciones }}</span>
                        </li>
                        @endif

                        <li>
                            <span class="label">
                                <i class="far fa-calendar-plus me-2"></i>Fecha de Creación
                            </span>
                            <span class="value">
                                {{ $pedido->fecha->format('d/m/Y') }} {{ \Carbon\Carbon::parse($pedido->fecha->format('Y-m-d') . ' ' . $pedido->hora)->format('H:i') }}
                                <small class="text-muted">({{ \Carbon\Carbon::parse($pedido->fecha->format('Y-m-d') . ' ' . $pedido->hora)->diffForHumans() }})</small>
                            </span>
                        </li>

                        <li>
                            <span class="label">
                                <i class="far fa-calendar-check me-2"></i>Última Actualización
                            </span>
                            <span class="value">
                                {{ $pedido->updated_at->format('d/m/Y H:i') }}
                                <small class="text-muted">({{ $pedido->updated_at->diffForHumans() }})</small>
                            </span>
                        </li>
                    </ul>
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
