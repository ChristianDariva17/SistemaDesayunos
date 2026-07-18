@extends('layouts.app')

@section('title', 'Empleados')

@section('breadcrumb')
    <li class="breadcrumb-item active">Empleados</li>
@endsection

@section('content')

{{-- ==========================================
    ALERTAS DE SESIÓN
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
            <h1 class="page-title mb-2">
                <i class="fas fa-users text-primary"></i> Gestión de Empleados
            </h1>
            <p class="page-subtitle text-muted mb-0">
                <i class="fas fa-info-circle me-1"></i> Administra el personal de tu restaurante
            </p>
        </div>
        <div class="col-auto">
            <a href="{{ route('admin.empleados.create') }}" class="btn btn-primary btn-lg shadow-sm">
                <i class="fas fa-plus-circle me-2"></i> Nuevo Empleado
            </a>
        </div>
    </div>
</div>

{{-- ==========================================
    TARJETAS DE ESTADÍSTICAS
    ========================================== --}}
<div class="row g-3 mb-4">
    {{-- Total Empleados --}}
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card stat-card-primary shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="stat-label mb-1">Total Empleados</p>
                        <h3 class="stat-value mb-0">{{ $empleados->total() }}</h3>
                        <small class="text-muted">
                            <i class="fas fa-users me-1"></i> Registrados
                        </small>
                    </div>
                    <div class="stat-icon bg-primary">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Activos --}}
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card stat-card-success shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="stat-label mb-1">Activos</p>
                        <h3 class="stat-value mb-0">{{ $empleados->where('estado', 'activo')->count() }}</h3>
                        <small class="text-muted">
                            <i class="fas fa-check-circle me-1"></i> Trabajando
                        </small>
                    </div>
                    <div class="stat-icon bg-success">
                        <i class="fas fa-user-check"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Inactivos --}}
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card stat-card-warning shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="stat-label mb-1">Inactivos</p>
                        <h3 class="stat-value mb-0">{{ $empleados->where('estado', 'inactivo')->count() }}</h3>
                        <small class="text-muted">
                            <i class="fas fa-user-times me-1"></i> No disponibles
                        </small>
                    </div>
                    <div class="stat-icon bg-warning">
                        <i class="fas fa-user-slash"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Meseros --}}
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card stat-card-info shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="stat-label mb-1">Meseros</p>
                        <h3 class="stat-value mb-0">{{ $empleados->where('rol_operativo', 'mesero')->count() }}</h3>
                        <small class="text-muted">
                            <i class="fas fa-concierge-bell me-1"></i> En servicio
                        </small>
                    </div>
                    <div class="stat-icon bg-info">
                        <i class="fas fa-concierge-bell"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ==========================================
    FILTROS Y BÚSQUEDA
    ========================================== --}}
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white border-bottom">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-filter text-primary me-2"></i> Filtros de Búsqueda
            </h5>
            @if(request()->hasAny(['search', 'rol_operativo', 'estado', 'sort', 'direction', 'per_page']))
                <a href="{{ route('admin.empleados.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-times me-1"></i> Limpiar Filtros
                </a>
            @endif
        </div>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.empleados.index') }}" method="GET" id="filterForm">
            <div class="row g-3">
                {{-- Búsqueda --}}
                <div class="col-lg-4">
                    <label for="search" class="form-label fw-semibold">
                        <i class="fas fa-search text-muted"></i> Buscar
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" 
                               class="form-control" 
                               id="search" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Buscar por nombre o rol operativo...">
                        @if(request('search'))
                            <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('search').value=''; document.getElementById('filterForm').submit();">
                                <i class="fas fa-times"></i>
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Filtro por Rol --}}
                <div class="col-lg-3">
                    <label for="rol_operativo" class="form-label fw-semibold">
                        <i class="fas fa-user-tag text-muted"></i> Rol
                    </label>
                    <select class="form-select" id="rol_operativo" name="rol_operativo" onchange="document.getElementById('filterForm').submit();">
                        <option value="">Todos los roles</option>
                        <option value="mesero" {{ request('rol_operativo') == 'mesero' ? 'selected' : '' }}>
                            👨‍🍳 Mesero
                        </option>
                        <option value="cajero" {{ request('rol_operativo') == 'cajero' ? 'selected' : '' }}>
                            💰 Cajero
                        </option>
                        <option value="cocinero" {{ request('rol_operativo') == 'cocinero' ? 'selected' : '' }}>
                            🍳 Cocinero
                        </option>
                        <option value="chef" {{ request('rol_operativo') == 'chef' ? 'selected' : '' }}>
                            👨‍🍳 Chef
                        </option>
                        <option value="ayudante" {{ request('rol_operativo') == 'ayudante' ? 'selected' : '' }}>
                            🤝 Ayudante
                        </option>
                        <option value="otros" {{ request('rol_operativo') == 'otros' ? 'selected' : '' }}>
                            🧩 Otros
                        </option>
                    </select>
                </div>

                {{-- Filtro por Estado --}}
                <div class="col-lg-3">
                    <label for="estado" class="form-label fw-semibold">
                        <i class="fas fa-toggle-on text-muted"></i> Estado
                    </label>
                    <select class="form-select" id="estado" name="estado" onchange="document.getElementById('filterForm').submit();">
                        <option value="">Todos los estados</option>
                        <option value="activo" {{ request('estado') == 'activo' ? 'selected' : '' }}>
                            ✅ Activo
                        </option>
                        <option value="inactivo" {{ request('estado') == 'inactivo' ? 'selected' : '' }}>
                            ❌ Inactivo
                        </option>
                    </select>
                </div>

                {{-- Registros por página --}}
                <div class="col-lg-2">
                    <label for="per_page" class="form-label fw-semibold">
                        <i class="fas fa-list-ol text-muted"></i> Mostrar
                    </label>
                    <select class="form-select" id="per_page" name="per_page" onchange="document.getElementById('filterForm').submit();">
                        <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>
            </div>

            {{-- Hidden inputs para mantener el orden --}}
            <input type="hidden" name="sort" value="{{ request('sort') }}">
            <input type="hidden" name="direction" value="{{ request('direction') }}">
        </form>
    </div>
</div>

{{-- ==========================================
    TABLA DE EMPLEADOS
    ========================================== --}}
<div class="card shadow-sm border-0">
    <div class="card-header bg-white border-bottom">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-list text-primary me-2"></i> Lista de Empleados
                <span class="badge bg-primary-soft text-primary ms-2">{{ $empleados->total() }}</span>
            </h5>
            <span class="text-muted small">
                Mostrando {{ $empleados->firstItem() ?? 0 }} - {{ $empleados->lastItem() ?? 0 }} de {{ $empleados->total() }}
            </span>
        </div>
    </div>
    <div class="card-body p-0">
        @if($empleados->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 responsive-card-table">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 5%">#</th>
                            <th style="width: 30%">
                                <a href="{{ route('admin.empleados.index', array_merge(request()->all(), ['sort' => 'nombre', 'direction' => request('sort') == 'nombre' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}"
                                   class="text-decoration-none text-dark d-flex align-items-center">
                                    Empleado
                                    @if(request('sort') == 'nombre')
                                        <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1 text-primary"></i>
                                    @else
                                        <i class="fas fa-sort ms-1 text-muted"></i>
                                    @endif
                                </a>
                            </th>
                            <th style="width: 20%">
                                <a href="{{ route('admin.empleados.index', array_merge(request()->all(), ['sort' => 'rol_operativo', 'direction' => request('sort') == 'rol_operativo' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}"
                                   class="text-decoration-none text-dark d-flex align-items-center">
                                    Rol
                                    @if(request('sort') == 'rol_operativo')
                                        <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1 text-primary"></i>
                                    @else
                                        <i class="fas fa-sort ms-1 text-muted"></i>
                                    @endif
                                </a>
                            </th>
                            <th style="width: 15%" class="text-center">
                                <a href="{{ route('admin.empleados.index', array_merge(request()->all(), ['sort' => 'estado', 'direction' => request('sort') == 'estado' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" 
                                   class="text-decoration-none text-dark d-flex align-items-center justify-content-center">
                                    Estado
                                    @if(request('sort') == 'estado')
                                        <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1 text-primary"></i>
                                    @else
                                        <i class="fas fa-sort ms-1 text-muted"></i>
                                    @endif
                                </a>
                            </th>
                            <th style="width: 15%">
                                <a href="{{ route('admin.empleados.index', array_merge(request()->all(), ['sort' => 'created_at', 'direction' => request('sort') == 'created_at' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" 
                                   class="text-decoration-none text-dark d-flex align-items-center">
                                    Fecha Registro
                                    @if(request('sort') == 'created_at')
                                        <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1 text-primary"></i>
                                    @else
                                        <i class="fas fa-sort ms-1 text-muted"></i>
                                    @endif
                                </a>
                            </th>
                            <th style="width: 15%" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($empleados as $empleado)
                        <tr>
                            {{-- # --}}
                            <td data-label="#">
                                <span class="text-muted fw-semibold">
                                    {{ $loop->iteration + ($empleados->currentPage() - 1) * $empleados->perPage() }}
                                </span>
                            </td>

                            {{-- Empleado --}}
                            <td data-label="Empleado">
                                <div class="d-flex align-items-center">
                                    {{-- Avatar --}}
                                    <div class="avatar-circle me-3 {{ 'avatar-' . strtolower(substr($empleado->nombre, 0, 1)) }}">
                                        <span class="avatar-initials">
                                            {{ strtoupper(substr($empleado->nombre, 0, 1)) }}
                                        </span>
                                    </div>
                                    {{-- Info --}}
                                    <div>
                                        <strong class="d-block">{{ $empleado->nombre }}</strong>
                                        <small class="text-muted">
                                            <i class="fas fa-id-badge me-1"></i> ID: {{ $empleado->id }}
                                        </small>
                                    </div>
                                </div>
                            </td>

                            {{-- Rol --}}
                            <td data-label="Rol">
                                @php
                                    $roleConfig = [
                                        'mesero' => ['emoji' => '👨‍🍳', 'bg' => 'bg-info', 'text' => 'Mesero'],
                                        'cajero' => ['emoji' => '💰', 'bg' => 'bg-success', 'text' => 'Cajero'],
                                        'cocinero' => ['emoji' => '🍳', 'bg' => 'bg-warning', 'text' => 'Cocinero'],
                                        'chef' => ['emoji' => '👨‍🍳', 'bg' => 'bg-danger', 'text' => 'Chef'],
                                        'ayudante' => ['emoji' => '🤝', 'bg' => 'bg-secondary', 'text' => 'Ayudante'],
                                        'otros' => ['emoji' => '🧩', 'bg' => 'bg-secondary', 'text' => 'Otros'],
                                    ];
                                    $config = $roleConfig[$empleado->rol_operativo] ?? ['emoji' => '👤', 'bg' => 'bg-secondary', 'text' => 'Sin rol'];
                                @endphp
                                <span class="badge {{ $config['bg'] }} px-3 py-2">
                                    {{ $config['emoji'] }} {{ $config['text'] }}
                                </span>
                            </td>

                            {{-- Estado --}}
                            <td class="text-center" data-label="Estado">
                                @if($empleado->estado === 'activo')
                                    <span class="badge bg-success-soft text-success px-3 py-2">
                                        <i class="fas fa-check-circle me-1"></i> Activo
                                    </span>
                                @else
                                    <span class="badge bg-danger-soft text-danger px-3 py-2">
                                        <i class="fas fa-times-circle me-1"></i> Inactivo
                                    </span>
                                @endif
                            </td>

                            {{-- Fecha --}}
                            <td data-label="Fecha Registro">
                                <div>
                                    <strong class="d-block">{{ $empleado->created_at->format('d/m/Y') }}</strong>
                                    <small class="text-muted">
                                        <i class="far fa-clock me-1"></i> {{ $empleado->created_at->format('h:i A') }}
                                    </small>
                                </div>
                            </td>

                            {{-- Acciones --}}
                            <td class="text-center" data-label="Acciones">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.empleados.show', $empleado->id) }}" 
                                       class="btn btn-sm btn-outline-info"
                                       data-bs-toggle="tooltip"
                                       title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.empleados.edit', $empleado->id) }}" 
                                       class="btn btn-sm btn-outline-warning"
                                       data-bs-toggle="tooltip"
                                       title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-danger btn-delete"
                                            data-id="{{ $empleado->id }}"
                                            data-name="{{ $empleado->nombre }}"
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
            @if($empleados->hasPages())
                <div class="card-footer bg-white border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Mostrando {{ $empleados->firstItem() }} - {{ $empleados->lastItem() }} de {{ $empleados->total() }} empleados
                        </div>
                        <div>
                            {{ $empleados->links() }}
                        </div>
                    </div>
                </div>
            @endif
        @else
            {{-- Empty State --}}
            <div class="empty-state py-5">
                <div class="text-center">
                    <div class="empty-icon mb-4">
                        <i class="fas fa-users fa-4x text-muted"></i>
                    </div>
                    <h4 class="text-muted mb-3">
                        @if(request()->hasAny(['search', 'rol_operativo', 'estado']))
                            No hay empleados que coincidan con tu búsqueda
                        @else
                            Aún no has registrado ningún empleado
                        @endif
                    </h4>
                    <p class="text-muted mb-4">
                        @if(request()->hasAny(['search', 'rol_operativo', 'estado']))
                            Intenta con otros criterios de búsqueda o filtros
                        @else
                            Comienza agregando tu primer empleado al sistema
                        @endif
                    </p>
                    @if(request()->hasAny(['search', 'rol_operativo', 'estado']))
                        <a href="{{ route('admin.empleados.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-times me-2"></i> Limpiar filtros
                        </a>
                    @else
                        <a href="{{ route('admin.empleados.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus-circle me-2"></i> Registrar primer empleado
                        </a>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

{{-- Forms ocultos para eliminar --}}
<form id="delete-form" method="POST" class="d-none">
    @csrf
    @method('DELETE')
</form>

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

    /* Stat Cards */
    .stat-card {
        transition: all 0.3s ease;
        overflow: hidden;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }

    .stat-card-primary { border-left: 4px solid var(--primary-color); }
    .stat-card-success { border-left: 4px solid #28a745; }
    .stat-card-warning { border-left: 4px solid #ffc107; }
    .stat-card-info { border-left: 4px solid #17a2b8; }

    .stat-label {
        font-size: 13px;
        font-weight: 600;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-value {
        font-size: 32px;
        font-weight: 700;
        color: #2d3748;
        line-height: 1;
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: white;
        opacity: 0.9;
    }

    /* Avatar Circles */
    .avatar-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .avatar-initials {
        font-size: 16px;
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
    .bg-primary-soft {
        background-color: rgba(255, 107, 53, 0.1);
    }

    .bg-success-soft {
        background-color: rgba(40, 167, 69, 0.1);
    }

    .bg-danger-soft {
        background-color: rgba(220, 53, 69, 0.1);
    }

    /* Empty State */
    .empty-state {
        padding: 80px 20px;
    }

    .empty-icon {
        opacity: 0.3;
    }

    /* Cards */
    .card {
        transition: all 0.3s ease;
    }

    .card:hover {
        box-shadow: 0 8px 16px rgba(0,0,0,0.1) !important;
    }

    /* Form Controls */
    .form-control:focus,
    .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.25rem rgba(255, 107, 53, 0.25);
    }

    /* Animations */
    .animate__animated {
        animation-duration: 0.5s;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .page-title {
            font-size: 22px;
        }

        .stat-value {
            font-size: 24px;
        }

        .stat-icon {
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

        .btn-group {
            display: flex;
            flex-direction: column;
        }

        .btn-group .btn {
            margin-bottom: 5px;
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
        // ELIMINAR EMPLEADO
        // ==========================================
        $('.btn-delete').on('click', function() {
            const empleadoId = $(this).data('id');
            const empleadoName = $(this).data('name');
            const deleteUrl = "{{ route('admin.empleados.destroy', ':id') }}".replace(':id', empleadoId);

            Swal.fire({
                title: '¿Estás seguro?',
                html: `
                    <div class="text-start">
                        <p class="mb-2">Estás a punto de eliminar al empleado:</p>
                        <div class="alert alert-danger mb-3">
                            <i class="fas fa-user me-2"></i>
                            <strong>${empleadoName}</strong>
                        </div>
                        <p class="text-muted small mb-2">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            Esta acción no se puede deshacer.
                        </p>
                        <p class="text-muted small mb-0">
                            <i class="fas fa-info-circle me-1"></i>
                            Si el empleado tiene pedidos asignados, no podrá ser eliminado.
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
                    Swal.fire({
                        title: 'Eliminando empleado...',
                        text: 'Por favor espera',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    const form = $('#delete-form');
                    form.attr('action', deleteUrl);
                    form.submit();
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
        // SUBMIT ON ENTER IN SEARCH
        // ==========================================
        $('#search').on('keypress', function(e) {
            if (e.which === 13) {
                $('#filterForm').submit();
            }
        });
    });
</script>
@endpush

@endsection
