@extends('layouts.app')

@section('title', 'Pedidos')

@section('breadcrumb')
    <li class="breadcrumb-item active">Pedidos</li>
@endsection

@section('content')

{{-- ==========================================
    ALERTAS
    ========================================== --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm animate__animated animate__fadeInDown" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-check-circle fa-2x me-3"></i>
            <div class="flex-grow-1">
                <strong>¡Éxito!</strong>
                <p class="mb-0">{{ session('success') }}</p>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show shadow-sm animate__animated animate__fadeInDown" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-exclamation-circle fa-2x me-3"></i>
            <div class="flex-grow-1">
                <strong>¡Error!</strong>
                <p class="mb-0">{{ session('error') }}</p>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
@endif

{{-- ==========================================
    PAGE HEADER
    ========================================== --}}
<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="page-title mb-1">
                <i class="fas fa-shopping-bag text-primary"></i> Gestión de Pedidos
            </h1>
            <p class="page-subtitle text-muted mb-0">
                <i class="fas fa-info-circle me-1"></i> Administra todos los pedidos de desayunos y caldos
            </p>
        </div>
        <div class="col-auto">
            <a href="{{ route('admin.pedidos.create') }}" class="btn btn-primary btn-lg shadow-sm">
                <i class="fas fa-plus-circle me-2"></i> Nuevo Pedido
            </a>
        </div>
    </div>
</div>

{{-- ==========================================
    KPIs - ESTADÍSTICAS
    ========================================== --}}
<div class="row g-3 mb-4">
    {{-- Total Pedidos --}}
    <div class="col-xl col-md-6">
        <div class="card kpi-card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="kpi-icon bg-primary-soft">
                        <i class="fas fa-shopping-cart text-primary"></i>
                    </div>
                    <div class="ms-3 flex-grow-1">
                        <p class="kpi-label mb-1">Total Pedidos</p>
                        <h3 class="kpi-value mb-0">{{ $estadisticas['total_pedidos'] }}</h3>
                        <small class="text-muted">Registrados</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Pendientes --}}
    <div class="col-xl col-md-6">
        <div class="card kpi-card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="kpi-icon bg-warning-soft">
                        <i class="fas fa-clock text-warning"></i>
                    </div>
                    <div class="ms-3 flex-grow-1">
                        <p class="kpi-label mb-1">Pendientes</p>
                        <h3 class="kpi-value mb-0">{{ $estadisticas['pendientes'] }}</h3>
                        <small class="text-muted">Por atender</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Completados --}}
    <div class="col-xl col-md-6">
        <div class="card kpi-card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="kpi-icon bg-success-soft">
                        <i class="fas fa-check-circle text-success"></i>
                    </div>
                    <div class="ms-3 flex-grow-1">
                        <p class="kpi-label mb-1">Completados</p>
                        <h3 class="kpi-value mb-0">{{ $estadisticas['completados'] }}</h3>
                        <small class="text-muted">Finalizados</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Ventas Hoy --}}
    <div class="col-xl col-md-6">
        <div class="card kpi-card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="kpi-icon bg-info-soft">
                        <i class="fas fa-calendar-day text-info"></i>
                    </div>
                    <div class="ms-3 flex-grow-1">
                        <p class="kpi-label mb-1">Ventas Hoy</p>
                        <h3 class="kpi-value mb-0">S/ {{ number_format($estadisticas['ventas_hoy'], 2) }}</h3>
                        <small class="text-muted">{{ date('d/m/Y') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Ventas del Mes --}}
    <div class="col-xl col-md-6">
        <div class="card kpi-card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="kpi-icon bg-purple-soft">
                        <i class="fas fa-calendar-alt text-purple"></i>
                    </div>
                    <div class="ms-3 flex-grow-1">
                        <p class="kpi-label mb-1">Ventas del Mes</p>
                        <h3 class="kpi-value mb-0">S/ {{ number_format($estadisticas['ventas_mes'], 2) }}</h3>
                        <small class="text-muted">{{ \Carbon\Carbon::now()->format('F Y') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ==========================================
    FILTROS
    ========================================== --}}
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white border-bottom">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-filter text-primary me-2"></i> Filtros de Búsqueda
                @if(request()->hasAny(['search', 'estado', 'fecha_desde', 'fecha_hasta', 'empleado_id']))
                    <span class="badge bg-primary ms-2">Activos</span>
                @endif
            </h5>
            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#filtrosCollapse">
                <i class="fas fa-chevron-down me-1"></i> <span class="d-none d-md-inline">Mostrar/Ocultar</span>
            </button>
        </div>
    </div>
    <div class="collapse show" id="filtrosCollapse">
        <div class="card-body">
            <form action="{{ route('admin.pedidos.index') }}" method="GET" id="filtrosForm">
                <div class="row g-3">
                    {{-- Búsqueda General --}}
                    <div class="col-md-4">
                        <label for="search" class="form-label fw-semibold">
                            <i class="fas fa-search text-muted"></i> Búsqueda General
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="search" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Buscar por N° pedido, cliente...">
                    </div>

                    {{-- Estado --}}
                    <div class="col-md-4">
                        <label for="estado" class="form-label fw-semibold">
                            <i class="fas fa-flag text-muted"></i> Estado
                        </label>
                        <select class="form-select" id="estado" name="estado">
                            <option value="">Todos los estados</option>
                            <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>
                                Pendiente
                            </option>
                            <option value="procesando" {{ request('estado') == 'procesando' ? 'selected' : '' }}>
                                Procesando
                            </option>
                            <option value="completado" {{ request('estado') == 'completado' ? 'selected' : '' }}>
                                Completado
                            </option>
                            <option value="cancelado" {{ request('estado') == 'cancelado' ? 'selected' : '' }}>
                                Cancelado
                            </option>
                        </select>
                    </div>

                    {{-- Empleado --}}
                    <div class="col-md-4">
                        <label for="empleado_id" class="form-label fw-semibold">
                            <i class="fas fa-user-tie text-muted"></i> Empleado
                        </label>
                        <select class="form-select" id="empleado_id" name="empleado_id">
                            <option value="">Todos los empleados</option>
                            @foreach($empleados ?? [] as $empleado)
                                <option value="{{ $empleado->id }}" {{ request('empleado_id') == $empleado->id ? 'selected' : '' }}>
                                    {{ $empleado->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Fecha Desde --}}
                    <div class="col-md-3">
                        <label for="fecha_desde" class="form-label fw-semibold">
                            <i class="fas fa-calendar text-muted"></i> Fecha Desde
                        </label>
                        <input type="date" 
                               class="form-control" 
                               id="fecha_desde" 
                               name="fecha_desde" 
                               value="{{ request('fecha_desde') }}">
                    </div>

                    {{-- Fecha Hasta --}}
                    <div class="col-md-3">
                        <label for="fecha_hasta" class="form-label fw-semibold">
                            <i class="fas fa-calendar text-muted"></i> Fecha Hasta
                        </label>
                        <input type="date" 
                               class="form-control" 
                               id="fecha_hasta" 
                               name="fecha_hasta" 
                               value="{{ request('fecha_hasta') }}">
                    </div>

                    {{-- Botones --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold d-block">&nbsp;</label>
                        <div class="btn-group w-100">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i> Buscar
                            </button>
                            @if(request()->hasAny(['search', 'estado', 'fecha_desde', 'fecha_hasta', 'empleado_id']))
                                <a href="{{ route('admin.pedidos.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-2"></i> Limpiar Filtros
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ==========================================
    TABLA DE PEDIDOS
    ========================================== --}}
<div class="card shadow-sm border-0">
    <div class="card-header bg-white border-bottom">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-list text-primary me-2"></i> 
                Listado de Pedidos
                <span class="badge bg-primary-soft text-primary ms-2">{{ $pedidos->total() }}</span>
            </h5>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                    <i class="fas fa-print me-1"></i> <span class="d-none d-md-inline">Imprimir</span>
                </button>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-download me-1"></i> <span class="d-none d-md-inline">Exportar</span>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-file-excel me-2"></i> Excel</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-file-pdf me-2"></i> PDF</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-file-csv me-2"></i> CSV</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        @if($pedidos->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>N° Pedido</th>
                            <th>Cliente</th>
                            <th>Empleado</th>
                            <th>Fecha & Hora</th>
                            <th class="text-end">Total</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pedidos as $pedido)
                        <tr>
                            {{-- N° Pedido --}}
                            <td>
                                <div class="d-flex flex-column">
                                    <strong class="text-primary">#{{ $pedido->numero_pedido }}</strong>
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>
                                        {{ \Carbon\Carbon::parse($pedido->fecha->format('Y-m-d') . ' ' . $pedido->hora)->diffForHumans() }}
                                    </small>
                                </div>
                            </td>

                            {{-- Cliente --}}
                            <td>
                                @if($pedido->cliente)
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle-sm me-2 {{ 'avatar-' . strtolower(substr($pedido->cliente->nombre, 0, 1)) }}">
                                            <span class="avatar-initials-sm">
                                                {{ strtoupper(substr($pedido->cliente->nombre, 0, 1)) }}{{ strtoupper(substr($pedido->cliente->apellido ?? '', 0, 1)) }}
                                            </span>
                                        </div>
                                        <div>
                                            <strong class="d-block">{{ trim($pedido->cliente->nombre . ' ' . ($pedido->cliente->apellido ?? '')) }}</strong>
                                            @if($pedido->cliente->email)
                                                <small class="text-muted">{{ $pedido->cliente->email }}</small>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted">Sin cliente</span>
                                @endif
                            </td>

                            {{-- Empleado --}}
                            <td>
                                @if($pedido->empleado)
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-user-tie text-muted me-2"></i>
                                        <span>{{ $pedido->empleado->nombre }}</span>
                                    </div>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-user-slash me-1"></i> Sin asignar
                                    </span>
                                @endif
                            </td>

                            {{-- Fecha & Hora --}}
                            <td>
                                <div class="d-flex flex-column">
                                    <span>
                                        <i class="fas fa-calendar text-muted me-1"></i>
                                        {{ \Carbon\Carbon::parse($pedido->fecha)->format('d/m/Y') }}
                                    </span>
                                    <small class="text-muted">
                                        <i class="fas fa-clock text-muted me-1"></i>
                                        {{ \Carbon\Carbon::parse($pedido->hora)->format('H:i') }}
                                    </small>
                                </div>
                            </td>

                            {{-- Total --}}
                            <td class="text-end">
                                <div class="d-flex flex-column align-items-end">
                                    <strong class="text-success fs-5">S/ {{ number_format($pedido->total, 2) }}</strong>
                                    <small class="text-muted">
                                        {{ $pedido->productos->count() }} productos
                                    </small>
                                </div>
                            </td>

                            {{-- Estado --}}
                            <td class="text-center">
                                @switch($pedido->estado)
                                    @case('pendiente')
                                        <span class="badge bg-warning">
                                            <i class="fas fa-clock me-1"></i> Pendiente
                                        </span>
                                        @break
                                    @case('procesando')
                                        <span class="badge bg-info">
                                            <i class="fas fa-spinner me-1"></i> Procesando
                                        </span>
                                        @break
                                    @case('completado')
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle me-1"></i> Completado
                                        </span>
                                        @break
                                    @case('cancelado')
                                        <span class="badge bg-danger">
                                            <i class="fas fa-times-circle me-1"></i> Cancelado
                                        </span>
                                        @break
                                    @default
                                        <span class="badge bg-secondary">
                                            {{ ucfirst($pedido->estado) }}
                                        </span>
                                @endswitch
                            </td>

                            {{-- Acciones --}}
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.pedidos.show', $pedido->id) }}" 
                                       class="btn btn-sm btn-outline-info"
                                       data-bs-toggle="tooltip"
                                       title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.pedidos.edit', $pedido->id) }}" 
                                       class="btn btn-sm btn-outline-warning"
                                       data-bs-toggle="tooltip"
                                       title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-danger btn-delete"
                                            data-id="{{ $pedido->id }}"
                                            data-numero="{{ $pedido->numero_pedido }}"
                                            data-bs-toggle="tooltip"
                                            title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Paginación --}}
            <div class="card-footer bg-white border-top">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <p class="text-muted mb-md-0 mb-2">
                            Mostrando 
                            <strong>{{ $pedidos->firstItem() }}</strong> a 
                            <strong>{{ $pedidos->lastItem() }}</strong> de 
                            <strong>{{ $pedidos->total() }}</strong> pedidos
                        </p>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-md-end justify-content-center">
                            {{ $pedidos->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            </div>
        @else
            {{-- Empty State --}}
            <div class="empty-state py-5">
                <div class="text-center">
                    <i class="fas fa-shopping-bag fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted mb-2">No hay pedidos registrados</h4>
                    @if(request()->hasAny(['search', 'estado', 'fecha_desde', 'fecha_hasta', 'empleado_id']))
                        <p class="text-muted mb-4">No se encontraron pedidos con los filtros aplicados</p>
                        <a href="{{ route('admin.pedidos.index') }}" class="btn btn-outline-secondary me-2">
                            <i class="fas fa-times me-2"></i> Limpiar Filtros
                        </a>
                    @else
                        <p class="text-muted mb-4">Comienza creando tu primer pedido de desayunos o caldos</p>
                    @endif
                    <a href="{{ route('admin.pedidos.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i> Crear Primer Pedido
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>

{{-- Forms ocultos para eliminar --}}
@foreach($pedidos as $pedido)
    <form id="delete-form-{{ $pedido->id }}" action="{{ route('admin.pedidos.destroy', $pedido->id) }}" method="POST" class="d-none">
        @csrf
        @method('DELETE')
    </form>
@endforeach

{{-- ==========================================
    ESTILOS PERSONALIZADOS
    ========================================== --}}
@push('styles')
<style>
    /* Page Title */
    .page-title {
        font-size: 28px;
        font-weight: 700;
        color: #2d3748;
    }

    .page-subtitle {
        font-size: 14px;
    }

    /* KPI Cards */
    .kpi-card {
        transition: all 0.3s ease;
        overflow: hidden;
    }

    .kpi-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }

    .kpi-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
    }

    .kpi-label {
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        color: #6c757d;
        letter-spacing: 0.5px;
    }

    .kpi-value {
        font-size: 28px;
        font-weight: 700;
        color: #2d3748;
        line-height: 1;
    }

    /* Soft Backgrounds */
    .bg-primary-soft {
        background-color: rgba(255, 107, 53, 0.1);
    }

    .bg-success-soft {
        background-color: rgba(28, 200, 138, 0.1);
    }

    .bg-info-soft {
        background-color: rgba(54, 185, 204, 0.1);
    }

    .bg-warning-soft {
        background-color: rgba(246, 194, 62, 0.1);
    }

    .bg-purple-soft {
        background-color: rgba(134, 107, 255, 0.1);
    }

    .text-purple {
        color: #866bff;
    }

    /* Avatar Circles */
    .avatar-circle-sm {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .avatar-initials-sm {
        font-size: 14px;
        font-weight: 700;
        color: white;
    }

    /* Avatar Colors */
    .avatar-a, .avatar-b, .avatar-c { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    .avatar-d, .avatar-e, .avatar-f { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
    .avatar-g, .avatar-h, .avatar-i { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
    .avatar-j, .avatar-k, .avatar-l { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
    .avatar-m, .avatar-n, .avatar-o { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
    .avatar-p, .avatar-q, .avatar-r { background: linear-gradient(135deg, #30cfd0 0%, #330867 100%); }
    .avatar-s, .avatar-t, .avatar-u { background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); }
    .avatar-v, .avatar-w, .avatar-x { background: linear-gradient(135deg, #ff9a56 0%, #ff6a88 100%); }
    .avatar-y, .avatar-z { background: linear-gradient(135deg, #ffc3a0 0%, #ffafbd 100%); }

    /* Table */
    .table thead th {
        font-weight: 600;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6c757d;
        border-bottom: 2px solid #dee2e6;
        padding: 16px 12px;
    }

    .table tbody td {
        padding: 16px 12px;
        vertical-align: middle;
    }

    .table tbody tr {
        transition: all 0.3s ease;
    }

    .table tbody tr:hover {
        background-color: #f8f9fa;
        transform: scale(1.01);
    }

    /* Badges */
    .badge {
        font-size: 12px;
        font-weight: 600;
        padding: 6px 12px;
    }

    /* Empty State */
    .empty-state {
        padding: 60px 20px;
    }

    .empty-state i {
        opacity: 0.5;
    }

    /* Cards */
    .card {
        transition: all 0.3s ease;
    }

    .card:hover {
        box-shadow: 0 8px 16px rgba(0,0,0,0.1) !important;
    }

    /* Animations */
    .animate__animated {
        animation-duration: 0.5s;
    }

    /* Buttons */
    .btn-group .btn {
        transition: all 0.3s ease;
    }

    .btn-group .btn:hover {
        transform: translateY(-2px);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .page-title {
            font-size: 22px;
        }

        .kpi-value {
            font-size: 22px;
        }

        .kpi-icon {
            width: 50px;
            height: 50px;
            font-size: 20px;
        }

        .table {
            font-size: 14px;
        }

        .table thead th,
        .table tbody td {
            padding: 12px 8px;
        }
    }

    /* Print Styles */
    @media print {
        .page-header,
        .card-header,
        .card-footer,
        .btn,
        .alert {
            display: none !important;
        }

        .card {
            border: none !important;
            box-shadow: none !important;
        }

        .table {
            font-size: 12px;
        }
    }
</style>
@endpush

{{-- ==========================================
    SCRIPTS PERSONALIZADOS
    ========================================== --}}
@push('scripts')
<script>
    $(document).ready(function() {
        // ==========================================
        // ELIMINAR PEDIDO
        // ==========================================
        $('.btn-delete').on('click', function() {
            const pedidoId = $(this).data('id');
            const numeroPedido = $(this).data('numero');

            Swal.fire({
                title: '¿Estás seguro?',
                html: `
                    <div class="text-start">
                        <p class="mb-2">Estás a punto de eliminar el pedido:</p>
                        <div class="alert alert-danger mb-3">
                            <i class="fas fa-shopping-bag me-2"></i>
                            <strong>#${numeroPedido}</strong>
                        </div>
                        <p class="text-muted small mb-0">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            Esta acción no se puede deshacer. El stock de los productos será restaurado.
                        </p>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74a3b',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-trash me-2"></i> Sí, eliminar',
                cancelButtonText: '<i class="fas fa-times me-2"></i> Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Mostrar loading
                    Swal.fire({
                        title: 'Eliminando pedido...',
                        text: 'Por favor espera',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Enviar formulario
                    $('#delete-form-' + pedidoId).submit();
                }
            });
        });

        // ==========================================
        // AUTO-CLOSE ALERTS
        // ==========================================
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);

        // ==========================================
        // TOOLTIPS
        // ==========================================
        $('[data-bs-toggle="tooltip"]').tooltip();

        // ==========================================
        // FILTROS - ENTER SUBMIT
        // ==========================================
        $('#filtrosForm input').on('keypress', function(e) {
            if (e.which === 13) {
                $('#filtrosForm').submit();
            }
        });

        // ==========================================
        // HIGHLIGHT ROW ON HOVER
        // ==========================================
        $('.table tbody tr').on('mouseenter', function() {
            $(this).find('.btn-group').addClass('show-actions');
        }).on('mouseleave', function() {
            $(this).find('.btn-group').removeClass('show-actions');
        });
    });
</script>
@endpush

@endsection
