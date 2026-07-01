@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">

    {{-- ==========================================
        HEADER DEL MÓDULO CON BREADCRUMB
        ========================================== --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-2 text-gray-800">
                <i class="fas fa-chart-line text-success"></i> Centro de Reportes
            </h1>
            <p class="text-muted mb-0">Genera y descarga reportes en formato PDF con información actualizada</p>
        </div>
        <div class="text-end">
            <div class="badge bg-light text-dark border px-3 py-2">
                <i class="far fa-calendar-alt text-primary"></i> 
                <strong>Fecha:</strong> {{ now()->format('d/m/Y') }}
            </div>
            <div class="badge bg-light text-dark border px-3 py-2 mt-1">
                <i class="far fa-clock text-success"></i> 
                <strong>Hora:</strong> {{ now()->format('h:i A') }}
            </div>
        </div>
    </div>

    {{-- ==========================================
        SISTEMA DE ALERTAS
        ========================================== --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <strong>¡Éxito!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <strong>¡Error!</strong> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Información:</strong> {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>¡Advertencia!</strong> {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    {{-- ==========================================
        ESTADÍSTICAS RÁPIDAS (KPIs)
        ========================================== --}}
    @php
        // Calcular estadísticas con caché para optimizar performance
        $totalProductos = \App\Models\Producto::count();
        $stockBajo = \App\Models\Producto::stockBajo()->count();
        $pedidosMes = \App\Models\Pedido::whereMonth('fecha', now()->month)
            ->whereYear('fecha', now()->year)
            ->count();
        $ventasMes = \App\Models\Pedido::whereMonth('fecha', now()->month)
            ->whereYear('fecha', now()->year)
            ->sum('total');
    @endphp

    <div class="row mb-4">
        {{-- Total Productos --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow-sm h-100 py-2 hover-lift">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Productos
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($totalProductos) }}
                            </div>
                            <div class="text-xs text-muted mt-1">
                                <i class="fas fa-box"></i> En inventario
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-boxes fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Stock Bajo --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow-sm h-100 py-2 hover-lift">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Stock Crítico
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stockBajo) }}
                            </div>
                            <div class="text-xs text-muted mt-1">
                                <i class="fas fa-exclamation-triangle"></i> Menos de 10 unidades
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pedidos del Mes --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow-sm h-100 py-2 hover-lift">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Pedidos {{ now()->format('F') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($pedidosMes) }}
                            </div>
                            <div class="text-xs text-muted mt-1">
                                <i class="fas fa-calendar-alt"></i> Este mes
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Ventas del Mes --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow-sm h-100 py-2 hover-lift">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Ventas {{ now()->format('F') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                S/ {{ number_format($ventasMes, 2) }}
                            </div>
                            <div class="text-xs text-muted mt-1">
                                <i class="fas fa-dollar-sign"></i> Ingresos del mes
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ==========================================
        TÍTULO DE SECCIÓN
        ========================================== --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">
            <i class="fas fa-file-pdf text-danger"></i> Reportes Disponibles
        </h4>
        <span class="badge bg-secondary">3 reportes</span>
    </div>

    {{-- ==========================================
        TARJETAS DE REPORTES
        ========================================== --}}
    <div class="row">

        {{-- ====================================
            1. REPORTE DE INVENTARIO
            ==================================== --}}
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm h-100 card-hover">
                {{-- Header --}}
                <div class="card-header bg-gradient-primary text-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-warehouse"></i> Inventario Completo
                        </h5>
                        <span class="badge bg-light text-primary">PDF</span>
                    </div>
                </div>

                {{-- Body --}}
                <div class="card-body d-flex flex-column">
                    <p class="text-muted mb-3">
                        <i class="fas fa-info-circle text-primary"></i>
                        Listado completo de todos los productos con stock, precios unitarios y valor total del inventario.
                    </p>

                    {{-- Estadísticas --}}
                    <div class="row text-center mb-3">
                        <div class="col-6">
                            <div class="border rounded p-2 bg-light">
                                <small class="text-muted d-block mb-1">
                                    <i class="fas fa-box"></i> Productos
                                </small>
                                <strong class="text-primary h5">
                                    {{ number_format($totalProductos) }}
                                </strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2 bg-light">
                                <small class="text-muted d-block mb-1">
                                    <i class="fas fa-dollar-sign"></i> Valor Total
                                </small>
                                @php
                                    $valorInventario = \Illuminate\Support\Facades\DB::table('productos')
                                        ->selectRaw('SUM(precio * stock) as total')
                                        ->value('total') ?? 0;
                                @endphp
                                <strong class="text-success h5">
                                    S/ {{ number_format($valorInventario, 0) }}
                                </strong>
                            </div>
                        </div>
                    </div>

                    {{-- Formulario --}}
                    <form action="{{ route('admin.reportes.inventario') }}" method="GET" target="_blank" class="mt-auto">
                        <div class="d-grid gap-2">
                            <button type="submit" name="accion" value="ver" class="btn btn-primary btn-lg">
                                <i class="fas fa-eye"></i> Ver PDF
                            </button>
                            <button type="submit" name="accion" value="descargar" class="btn btn-outline-primary">
                                <i class="fas fa-download"></i> Descargar PDF
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Footer --}}
                <div class="card-footer bg-light text-muted small">
                    <i class="far fa-clock"></i> Generado en tiempo real
                    <span class="float-end text-success">
                        <i class="fas fa-circle pulse"></i> Disponible
                    </span>
                </div>
            </div>
        </div>

        {{-- ====================================
            2. REPORTE DE STOCK BAJO
            ==================================== --}}
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm h-100 card-hover">
                {{-- Header --}}
                <div class="card-header bg-gradient-warning text-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-exclamation-triangle"></i> Stock Bajo
                        </h5>
                        @if($stockBajo > 0)
                            <span class="badge bg-danger">
                                <i class="fas fa-bell"></i> {{ $stockBajo }} Alertas
                            </span>
                        @else
                            <span class="badge bg-light text-warning">OK</span>
                        @endif
                    </div>
                </div>

                {{-- Body --}}
                <div class="card-body d-flex flex-column">
                    <p class="text-muted mb-3">
                        <i class="fas fa-info-circle text-warning"></i>
                        Productos críticos con 10 unidades o menos en inventario para reabastecimiento urgente.
                    </p>

                    {{-- Estadísticas --}}
                    <div class="row text-center mb-3">
                        <div class="col-6">
                            <div class="border rounded p-2 bg-light">
                                <small class="text-muted d-block mb-1">
                                    <i class="fas fa-exclamation-triangle"></i> Críticos
                                </small>
                                <strong class="text-warning h5">
                                    {{ number_format($stockBajo) }}
                                </strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2 bg-light">
                                <small class="text-muted d-block mb-1">
                                    <i class="fas fa-layer-group"></i> Mínimo
                                </small>
                                <strong class="text-danger h5">10 unid</strong>
                            </div>
                        </div>
                    </div>

                    @if($stockBajo > 0)
                        <div class="alert alert-warning py-2 mb-3">
                            <small>
                                <i class="fas fa-exclamation-circle"></i>
                                <strong>¡Atención!</strong> Hay productos que requieren reabastecimiento inmediato.
                            </small>
                        </div>
                    @endif

                    {{-- Formulario --}}
                    <form action="{{ route('admin.reportes.stock-bajo') }}" method="GET" target="_blank" class="mt-auto">
                        <div class="d-grid gap-2">
                            <button type="submit" name="accion" value="ver" class="btn btn-warning text-white btn-lg">
                                <i class="fas fa-eye"></i> Ver PDF
                            </button>
                            <button type="submit" name="accion" value="descargar" class="btn btn-outline-warning">
                                <i class="fas fa-download"></i> Descargar PDF
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Footer --}}
                <div class="card-footer bg-light text-muted small">
                    <i class="fas fa-sync-alt"></i> Actualización automática
                    <span class="float-end">
                        @if($stockBajo > 0)
                            <span class="text-danger">
                                <i class="fas fa-circle pulse"></i> Requiere atención
                            </span>
                        @else
                            <span class="text-success">
                                <i class="fas fa-check-circle"></i> Todo OK
                            </span>
                        @endif
                    </span>
                </div>
            </div>
        </div>

        {{-- ====================================
            3. REPORTE DE VENTAS
            ==================================== --}}
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm h-100 card-hover">
                {{-- Header --}}
                <div class="card-header bg-gradient-success text-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-line"></i> Análisis de Ventas
                        </h5>
                        <span class="badge bg-light text-success">
                            <i class="fas fa-sliders-h"></i> Personalizado
                        </span>
                    </div>
                </div>

                {{-- Body --}}
                <div class="card-body d-flex flex-column">
                    <p class="text-muted mb-3">
                        <i class="fas fa-info-circle text-success"></i>
                        Informe detallado de ventas por rango de fechas con totales, cantidad de pedidos y análisis por cliente.
                    </p>

                    {{-- Formulario con fechas --}}
                    <form action="{{ route('admin.reportes.ventas') }}" method="GET" target="_blank" id="formVentas" class="flex-grow-1">
                        
                        {{-- Rango de fechas --}}
                        <div class="mb-3">
                            <label class="form-label small fw-bold">
                                <i class="far fa-calendar-alt"></i> Período a Analizar
                            </label>
                            
                            <div class="row g-2">
                                <div class="col-6">
                                    <label for="fecha_inicio" class="form-label small text-muted">Fecha Inicio</label>
                                    <input 
                                        type="date" 
                                        id="fecha_inicio"
                                        name="fecha_inicio" 
                                        class="form-control form-control-sm" 
                                        value="{{ now()->startOfMonth()->format('Y-m-d') }}"
                                        max="{{ now()->format('Y-m-d') }}"
                                        required
                                        aria-label="Fecha de inicio del reporte"
                                    >
                                </div>
                                <div class="col-6">
                                    <label for="fecha_fin" class="form-label small text-muted">Fecha Fin</label>
                                    <input 
                                        type="date" 
                                        id="fecha_fin"
                                        name="fecha_fin" 
                                        class="form-control form-control-sm" 
                                        value="{{ now()->format('Y-m-d') }}"
                                        max="{{ now()->format('Y-m-d') }}"
                                        required
                                        aria-label="Fecha de fin del reporte"
                                    >
                                </div>
                            </div>
                            <div id="fecha-error" class="text-danger small mt-1" style="display: none;">
                                <i class="fas fa-exclamation-circle"></i> La fecha de fin debe ser mayor o igual a la fecha de inicio
                            </div>
                        </div>

                        {{-- Botones de acción --}}
                        <div class="d-grid gap-2">
                            <button type="submit" name="accion" value="ver" class="btn btn-success btn-lg">
                                <i class="fas fa-eye"></i> Ver PDF
                            </button>
                            <button type="submit" name="accion" value="descargar" class="btn btn-outline-success">
                                <i class="fas fa-download"></i> Descargar PDF
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Footer --}}
                <div class="card-footer bg-light text-muted small">
                    <i class="fas fa-filter"></i> Filtrado por fechas
                    <span class="float-end text-success">
                        <i class="fas fa-circle pulse"></i> Disponible
                    </span>
                </div>
            </div>
        </div>

    </div>

    {{-- ==========================================
        ACCESOS RÁPIDOS
        ========================================== --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt text-warning"></i> Accesos Rápidos a Reportes de Ventas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        
                        {{-- Mes Actual --}}
                        <div class="col-md-3">
                            <form action="{{ route('admin.reportes.ventas') }}" method="GET" target="_blank">
                                <input type="hidden" name="fecha_inicio" value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                                <input type="hidden" name="fecha_fin" value="{{ now()->format('Y-m-d') }}">
                                <button type="submit" name="accion" value="ver" class="btn btn-outline-primary w-100 py-3">
                                    <i class="fas fa-calendar-check d-block mb-2" style="font-size: 24px;"></i>
                                    <strong>Mes Actual</strong>
                                    <small class="d-block text-muted">{{ now()->format('F Y') }}</small>
                                </button>
                            </form>
                        </div>

                        {{-- Mes Anterior --}}
                        <div class="col-md-3">
                            <form action="{{ route('admin.reportes.ventas') }}" method="GET" target="_blank">
                                <input type="hidden" name="fecha_inicio" value="{{ now()->subMonth()->startOfMonth()->format('Y-m-d') }}">
                                <input type="hidden" name="fecha_fin" value="{{ now()->subMonth()->endOfMonth()->format('Y-m-d') }}">
                                <button type="submit" name="accion" value="ver" class="btn btn-outline-secondary w-100 py-3">
                                    <i class="fas fa-calendar-minus d-block mb-2" style="font-size: 24px;"></i>
                                    <strong>Mes Anterior</strong>
                                    <small class="d-block text-muted">{{ now()->subMonth()->format('F Y') }}</small>
                                </button>
                            </form>
                        </div>

                        {{-- Últimos 7 días --}}
                        <div class="col-md-3">
                            <form action="{{ route('admin.reportes.ventas') }}" method="GET" target="_blank">
                                <input type="hidden" name="fecha_inicio" value="{{ now()->subDays(7)->format('Y-m-d') }}">
                                <input type="hidden" name="fecha_fin" value="{{ now()->format('Y-m-d') }}">
                                <button type="submit" name="accion" value="ver" class="btn btn-outline-info w-100 py-3">
                                    <i class="fas fa-calendar-week d-block mb-2" style="font-size: 24px;"></i>
                                    <strong>Últimos 7 Días</strong>
                                    <small class="d-block text-muted">Semana reciente</small>
                                </button>
                            </form>
                        </div>

                        {{-- Hoy --}}
                        <div class="col-md-3">
                            <form action="{{ route('admin.reportes.ventas') }}" method="GET" target="_blank">
                                <input type="hidden" name="fecha_inicio" value="{{ now()->format('Y-m-d') }}">
                                <input type="hidden" name="fecha_fin" value="{{ now()->format('Y-m-d') }}">
                                <button type="submit" name="accion" value="ver" class="btn btn-outline-success w-100 py-3">
                                    <i class="fas fa-calendar-day d-block mb-2" style="font-size: 24px;"></i>
                                    <strong>Hoy</strong>
                                    <small class="d-block text-muted">{{ now()->format('d/m/Y') }}</small>
                                </button>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ==========================================
        SECCIÓN INFORMATIVA
        ========================================== --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-left-info shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <i class="fas fa-info-circle fa-3x text-info"></i>
                        </div>
                        <div class="col">
                            <h5 class="text-info mb-2">
                                <i class="fas fa-lightbulb"></i> Información sobre los Reportes
                            </h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <ul class="mb-0 small">
                                        <li><strong>Formato PDF:</strong> Todos los reportes se generan optimizados para impresión en formato A4.</li>
                                        <li><strong>Ver en navegador:</strong> Abre el reporte en una nueva pestaña para visualización rápida sin descargar.</li>
                                        <li><strong>Descargar archivo:</strong> Guarda el PDF directamente en tu dispositivo para uso offline.</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <ul class="mb-0 small">
                                        <li><strong>Datos en tiempo real:</strong> Los reportes contienen la información más actualizada de la base de datos.</li>
                                        <li><strong>Fechas personalizadas:</strong> El reporte de ventas permite seleccionar cualquier rango de fechas.</li>
                                        <li><strong>Accesos rápidos:</strong> Usa los botones de acceso rápido para períodos comunes sin configurar fechas.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ==========================================
    ESTILOS PERSONALIZADOS
    ========================================== --}}
<style>
    /* Bordes de colores para las cards */
    .border-left-primary {
        border-left: 5px solid #4e73df !important;
    }
    
    .border-left-success {
        border-left: 5px solid #1cc88a !important;
    }
    
    .border-left-info {
        border-left: 5px solid #36b9cc !important;
    }
    
    .border-left-warning {
        border-left: 5px solid #f6c23e !important;
    }

    /* Gradientes para headers */
    .bg-gradient-primary {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%) !important;
    }
    
    .bg-gradient-success {
        background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%) !important;
    }
    
    .bg-gradient-warning {
        background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%) !important;
    }

    /* Efecto hover en las cards */
    .card-hover {
        transition: all 0.3s ease;
    }
    
    .card-hover:hover {
        transform: translateY(-8px);
        box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.2) !important;
    }

    .hover-lift {
        transition: all 0.3s ease;
    }

    .hover-lift:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }

    /* Botones con efecto */
    .btn {
        transition: all 0.3s ease;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .btn:active {
        transform: translateY(0);
    }

    /* Inputs con mejor estilo */
    .form-control:focus {
        border-color: #1cc88a;
        box-shadow: 0 0 0 0.25rem rgba(28, 200, 138, 0.25);
    }

    /* Animación de pulso */
    @keyframes pulse {
        0% {
            opacity: 1;
        }
        50% {
            opacity: 0.5;
        }
        100% {
            opacity: 1;
        }
    }

    .pulse {
        animation: pulse 2s infinite;
    }

    /* Mejora de badges */
    .badge {
        font-weight: 600;
        padding: 0.5em 0.8em;
    }

    /* Breadcrumb personalizado */
    .breadcrumb {
        background-color: #f8f9fa;
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
    }

    .breadcrumb-item + .breadcrumb-item::before {
        content: "›";
        font-size: 1.2em;
    }

    /* Alertas mejoradas */
    .alert {
        border-left: 5px solid;
    }

    .alert-success {
        border-left-color: #1cc88a;
    }

    .alert-danger {
        border-left-color: #e74a3b;
    }

    .alert-warning {
        border-left-color: #f6c23e;
    }

    .alert-info {
        border-left-color: #36b9cc;
    }
</style>

{{-- ==========================================
    SCRIPTS PERSONALIZADOS
    ========================================== --}}
@push('scripts')
<script>
    // ==========================================
    // VALIDACIÓN DE FORMULARIO DE VENTAS
    // ==========================================
    document.getElementById('formVentas').addEventListener('submit', function(e) {
        const fechaInicio = document.getElementById('fecha_inicio').value;
        const fechaFin = document.getElementById('fecha_fin').value;
        const errorDiv = document.getElementById('fecha-error');
        
        if (fechaFin < fechaInicio) {
            e.preventDefault();
            errorDiv.style.display = 'block';
            
            // Agregar clase de error a los inputs
            document.getElementById('fecha_inicio').classList.add('is-invalid');
            document.getElementById('fecha_fin').classList.add('is-invalid');
            
            // Mostrar alerta
            Swal.fire({
                icon: 'error',
                title: '¡Fechas inválidas!',
                text: 'La fecha de fin debe ser mayor o igual a la fecha de inicio',
                confirmButtonColor: '#e74a3b'
            });
            
            return false;
        } else {
            errorDiv.style.display = 'none';
            document.getElementById('fecha_inicio').classList.remove('is-invalid');
            document.getElementById('fecha_fin').classList.remove('is-invalid');
        }
    });

    // ==========================================
    // REMOVER CLASE DE ERROR AL CAMBIAR FECHAS
    // ==========================================
    document.getElementById('fecha_inicio').addEventListener('change', function() {
        const errorDiv = document.getElementById('fecha-error');
        errorDiv.style.display = 'none';
        this.classList.remove('is-invalid');
    });

    document.getElementById('fecha_fin').addEventListener('change', function() {
        const errorDiv = document.getElementById('fecha-error');
        errorDiv.style.display = 'none';
        this.classList.remove('is-invalid');
    });

    // ==========================================
    // AUTO-CERRAR ALERTAS DESPUÉS DE 5 SEGUNDOS
    // ==========================================
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);

    // ==========================================
    // CONFIRMAR GENERACIÓN DE REPORTES
    // ==========================================
    document.querySelectorAll('form[target="_blank"]').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            const button = e.submitter;
            const originalText = button.innerHTML;
            
            // Mostrar indicador de carga
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generando...';
            
            // Restaurar botón después de 2 segundos
            setTimeout(function() {
                button.disabled = false;
                button.innerHTML = originalText;
            }, 2000);
        });
    });

    // ==========================================
    // TOOLTIPS DE BOOTSTRAP
    // ==========================================
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
</script>
@endpush

@endsection
