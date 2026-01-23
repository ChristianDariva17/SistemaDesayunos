<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    
    {{-- CSRF Token --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    {{-- SEO Meta Tags --}}
    <meta name="description" content="Gestión de Clientes - Sistema Caldos & Desayunos - Trabajador">
    <meta name="keywords" content="clientes, gestión, base de datos, trabajador">
    <meta name="author" content="Caldos & Desayunos">
    
    {{-- Favicon --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    
    {{-- Title --}}
    <title>Clientes - Caldos & Desayunos</title>
    
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
                <i class="fas fa-users text-success me-2"></i>
                <strong>Gestión de Clientes</strong>
            </h1>
            <p class="text-muted mb-0">
                <i class="far fa-chart-bar me-1"></i>
                Administra la base de datos de clientes
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
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- ============================================
        TARJETAS DE ESTADÍSTICAS - FILA 1
    ============================================= --}}
    <div class="row g-4 mb-4">
        
        {{-- Total Clientes --}}
        <div class="col-xl-3 col-md-6 animate-fade">
            <div class="card border-left-primary shadow-sm h-100 hover-shadow">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-primary mb-2">
                                <i class="fas fa-users me-1"></i>Total Clientes
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                {{ $totalClientes ?? 0 }}
                            </div>
                            <small class="text-muted">
                                Registrados
                            </small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-3x text-primary opacity-25"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-top-0">
                    <a href="{{ route('trabajador.dashboard') }}" class="small text-primary text-decoration-none">
                        Ver dashboard <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        {{-- Clientes Activos --}}
        <div class="col-xl-3 col-md-6 animate-fade">
            <div class="card border-left-success shadow-sm h-100 hover-shadow">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-success mb-2">
                                <i class="fas fa-user-check me-1"></i>Activos
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                {{ $clientesActivos ?? 0 }}
                            </div>
                            <small class="text-muted">
                                Estado activo
                            </small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-check fa-3x text-success opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Clientes Nuevos --}}
        <div class="col-xl-3 col-md-6 animate-fade">
            <div class="card border-left-info shadow-sm h-100 hover-shadow">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-info mb-2">
                                <i class="fas fa-user-plus me-1"></i>Nuevos (Mes)
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                {{ $clientesNuevos ?? 0 }}
                            </div>
                            <small class="text-muted">
                                {{ now()->format('M Y') }}
                            </small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-plus fa-3x text-info opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Clientes Inactivos --}}
        <div class="col-xl-3 col-md-6 animate-fade">
            <div class="card border-left-warning shadow-sm h-100 hover-shadow">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-warning mb-2">
                                <i class="fas fa-user-times me-1"></i>Inactivos
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                {{ $clientesInactivos ?? 0 }}
                            </div>
                            <small class="text-muted">
                                Estado inactivo
                            </small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-times fa-3x text-warning opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ============================================
        TARJETA PRINCIPAL - FILTROS Y TABLA
    ============================================= --}}
    <div class="row">
        <div class="col-12 animate-fade">
            <div class="card shadow-sm">
                
                {{-- HEADER CON FILTROS --}}
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-filter me-2"></i>Filtros de Búsqueda
                    </h6>
                    <i class="fas fa-search"></i>
                </div>

                <div class="card-body">
                    <div class="row align-items-center g-3 mb-3">
                        
                        {{-- BARRA DE BÚSQUEDA --}}
                        <div class="col-lg-6">
                            <form action="{{ route('trabajador.clientes.index') }}" method="GET" id="searchForm">
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="fas fa-search text-muted"></i>
                                    </span>
                                    <input type="text" 
                                           name="search" 
                                           class="form-control border-start-0 ps-0" 
                                           placeholder="Buscar por nombre, email o teléfono..." 
                                           value="{{ request('search') }}"
                                           autocomplete="off">
                                    
                                    @if(request()->hasAny(['search', 'estado']))
                                        <a href="{{ route('trabajador.clientes.index') }}" 
                                           class="btn btn-outline-secondary" 
                                           title="Limpiar filtros">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    @endif
                                </div>
                                <input type="hidden" name="estado" value="{{ request('estado') }}">
                            </form>
                        </div>

                        {{-- FILTRO POR ESTADO --}}
                        <div class="col-lg-4">
                            <select name="estado" class="form-select" id="filterEstado">
                                <option value="">Todos los estados</option>
                                <option value="activo" {{ request('estado') == 'activo' ? 'selected' : '' }}>Activo</option>
                                <option value="inactivo" {{ request('estado') == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- TABLA DE CLIENTES --}}
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">ID</th>
                                    <th>Cliente</th>
                                    <th width="15%">Teléfono</th>
                                    <th width="15%">Ciudad</th>
                                    <th class="text-center" width="10%">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($clientes as $cliente)
                                <tr>
                                    {{-- ID --}}
                                    <td>
                                        <strong class="text-primary">#{{ $cliente->id }}</strong>
                                    </td>

                                    {{-- CLIENTE --}}
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3"
                                                 style="width: 40px; height: 40px;">
                                                <i class="fas fa-user text-success"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark">{{ $cliente->nombre }}</div>
                                                <small class="text-muted">
                                                    <i class="fas fa-envelope me-1"></i>{{ $cliente->email }}
                                                </small>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- TELÉFONO --}}
                                    <td>
                                        @if($cliente->telefono)
                                            <i class="fas fa-phone text-success me-1"></i>
                                            {{ $cliente->telefono }}
                                        @else
                                            <span class="text-muted fst-italic">No registrado</span>
                                        @endif
                                    </td>

                                    {{-- CIUDAD --}}
                                    <td>
                                        @if($cliente->ciudad)
                                            <i class="fas fa-map-marker-alt text-danger me-1"></i>
                                            {{ $cliente->ciudad }}
                                        @else
                                            <span class="text-muted fst-italic">No especificado</span>
                                        @endif
                                    </td>

                                    {{-- ESTADO --}}
                                    <td class="text-center">
                                        @if($cliente->estado == 'activo')
                                            <span class="badge bg-success">
                                                <i class="fas fa-check me-1"></i>Activo
                                            </span>
                                        @else
                                            <span class="badge bg-danger">
                                                <i class="fas fa-times me-1"></i>Inactivo
                                            </span>
                                        @endif
                                    </td>
                                </tr>

                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="fas fa-inbox fa-4x mb-3 d-block opacity-25"></i>
                                            @if(request()->hasAny(['search', 'estado']))
                                                <h5 class="fw-bold mb-2">No hay clientes que coincidan</h5>
                                                <p class="mb-3">Intenta con otros criterios de búsqueda</p>
                                                <a href="{{ route('trabajador.clientes.index') }}" class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-undo me-2"></i>Limpiar filtros
                                                </a>
                                            @else
                                                <h5 class="fw-bold mb-2">No hay clientes registrados</h5>
                                                <p class="mb-0">La base de datos está vacía</p>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- PAGINACIÓN --}}
                @if($clientes->hasPages())
                <div class="card-footer bg-white border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Mostrando {{ $clientes->firstItem() }} a {{ $clientes->lastItem() }} de {{ $clientes->total() }} clientes
                        </div>
                        <div>
                            {{ $clientes->links() }}
                        </div>
                    </div>
                </div>
                @endif

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
        
        // FILTROS AUTOMÁTICOS
        const filterEstado = document.getElementById('filterEstado');

        filterEstado.addEventListener('change', aplicarFiltros);

        function aplicarFiltros() {
            const form = document.getElementById('searchForm');
            const searchValue = form.querySelector('input[name="search"]').value;
            const estadoValue = filterEstado.value;

            // Construir URL con parámetros
            const params = new URLSearchParams();
            if (searchValue) params.append('search', searchValue);
            if (estadoValue) params.append('estado', estadoValue);

            // Redirigir con filtros
            window.location.href = "{{ route('trabajador.clientes.index') }}?" + params.toString();
        }

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
