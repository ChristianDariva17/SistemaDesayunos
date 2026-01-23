@extends('layouts.app')

@section('title', $empleado->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.empleados.index') }}">Empleados</a></li>
    <li class="breadcrumb-item active">{{ $empleado->name }}</li>
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
    HEADER CON AVATAR Y ACCIONES
    ========================================== --}}
<div class="card shadow-sm border-0 mb-4 profile-header">
    <div class="card-body">
        <div class="row align-items-center">
            {{-- Avatar y Nombre --}}
            <div class="col-md-8">
                <div class="d-flex align-items-center">
                    {{-- Avatar Grande --}}
                    <div class="avatar-circle-xl me-4">
                        <span class="avatar-initials-xl">
                            @php
                                $words = explode(' ', $empleado->name);
                                if (count($words) >= 2) {
                                    echo strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
                                } else {
                                    echo strtoupper(substr($empleado->name, 0, 2));
                                }
                            @endphp
                        </span>
                    </div>
                    
                    {{-- Info --}}
                    <div>
                        <h2 class="mb-1">{{ $empleado->name }}</h2>
                        <div class="mb-2">
                            @php
                                $roleConfig = [
                                    'mesero' => ['emoji' => '👨‍🍳', 'bg' => 'bg-info', 'text' => 'Mesero'],
                                    'cajero' => ['emoji' => '💰', 'bg' => 'bg-success', 'text' => 'Cajero'],
                                    'cocinero' => ['emoji' => '🍳', 'bg' => 'bg-warning', 'text' => 'Cocinero'],
                                    'chef' => ['emoji' => '👨‍🍳', 'bg' => 'bg-danger', 'text' => 'Chef'],
                                    'ayudante' => ['emoji' => '🤝', 'bg' => 'bg-secondary', 'text' => 'Ayudante'],
                                ];
                                $config = $roleConfig[$empleado->role] ?? ['emoji' => '👤', 'bg' => 'bg-secondary', 'text' => 'Sin rol'];
                            @endphp
                            <span class="badge {{ $config['bg'] }} px-3 py-2 me-2">
                                {{ $config['emoji'] }} {{ $config['text'] }}
                            </span>
                            <span class="badge {{ $empleado->estado == 'activo' ? 'bg-success' : 'bg-danger' }} px-3 py-2">
                                <i class="fas fa-{{ $empleado->estado == 'activo' ? 'check' : 'times' }}-circle me-1"></i>
                                {{ ucfirst($empleado->estado) }}
                            </span>
                        </div>
                        <p class="text-muted mb-0">
                            <i class="fas fa-id-badge me-1"></i> ID: <strong>{{ $empleado->id }}</strong>
                            <span class="mx-2">•</span>
                            <i class="fas fa-calendar-check me-1"></i> 
                            Registrado hace <strong>{{ $empleado->created_at->diffForHumans() }}</strong>
                        </p>
                    </div>
                </div>
            </div>

            {{-- Botones de Acción --}}
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <div class="btn-group" role="group">
                    <a href="{{ route('admin.empleados.edit', $empleado) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-1"></i> Editar
                    </a>
                    <button type="button" class="btn btn-danger" id="btnDelete">
                        <i class="fas fa-trash me-1"></i> Eliminar
                    </button>
                </div>
                <a href="{{ route('admin.empleados.index') }}" class="btn btn-outline-secondary ms-2">
                    <i class="fas fa-arrow-left me-1"></i> Volver
                </a>
            </div>
        </div>
    </div>
</div>

{{-- ==========================================
    ESTADÍSTICAS RÁPIDAS
    ========================================== --}}
<div class="row g-4 mb-4">
    {{-- Pedidos Asignados --}}
    <div class="col-lg-4 col-md-6">
        <div class="card stat-card stat-card-primary shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="stat-label mb-1">Pedidos Asignados</p>
                        <h3 class="stat-value mb-0">{{ $empleado->pedidos_count ?? 0 }}</h3>
                        <small class="text-muted">
                            <i class="fas fa-receipt me-1"></i> Total registrados
                        </small>
                    </div>
                    <div class="stat-icon bg-primary">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Días en el Equipo --}}
    <div class="col-lg-4 col-md-6">
        <div class="card stat-card stat-card-success shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="stat-label mb-1">Días en el Equipo</p>
                        <h3 class="stat-value mb-0">{{ $empleado->created_at->diffInDays(now()) }}</h3>
                        <small class="text-muted">
                            <i class="fas fa-calendar me-1"></i> Desde {{ $empleado->created_at->format('d/m/Y') }}
                        </small>
                    </div>
                    <div class="stat-icon bg-success">
                        <i class="fas fa-user-clock"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Última Actualización --}}
    <div class="col-lg-4 col-md-6">
        <div class="card stat-card stat-card-warning shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="stat-label mb-1">Última Actualización</p>
                        <h3 class="stat-value mb-0" style="font-size: 20px;">
                            {{ $empleado->updated_at->diffForHumans() }}
                        </h3>
                        <small class="text-muted">
                            <i class="fas fa-clock me-1"></i> {{ $empleado->updated_at->format('d/m/Y h:i A') }}
                        </small>
                    </div>
                    <div class="stat-icon bg-warning">
                        <i class="fas fa-sync-alt"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- ==========================================
        COLUMNA IZQUIERDA - INFORMACIÓN DETALLADA
        ========================================== --}}
    <div class="col-lg-8">
        
        {{-- Información Personal --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0">
                    <i class="fas fa-user text-primary me-2"></i> Información Personal
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    {{-- ID --}}
                    <div class="col-md-6">
                        <div class="info-item">
                            <label class="info-label">
                                <i class="fas fa-hashtag text-muted me-2"></i> ID del Empleado
                            </label>
                            <div class="info-value">
                                <span class="badge bg-light text-dark px-3 py-2">
                                    # {{ $empleado->id }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Nombre --}}
                    <div class="col-md-6">
                        <div class="info-item">
                            <label class="info-label">
                                <i class="fas fa-user text-muted me-2"></i> Nombre Completo
                            </label>
                            <div class="info-value">{{ $empleado->name }}</div>
                        </div>
                    </div>

                    {{-- Rol --}}
                    <div class="col-md-6">
                        <div class="info-item">
                            <label class="info-label">
                                <i class="fas fa-briefcase text-muted me-2"></i> Rol / Cargo
                            </label>
                            <div class="info-value">
                                <span class="badge {{ $config['bg'] }} px-3 py-2">
                                    {{ $config['emoji'] }} {{ $config['text'] }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Estado --}}
                    <div class="col-md-6">
                        <div class="info-item">
                            <label class="info-label">
                                <i class="fas fa-toggle-on text-muted me-2"></i> Estado
                            </label>
                            <div class="info-value">
                                @if($empleado->estado == 'activo')
                                    <span class="badge bg-success-soft text-success px-3 py-2">
                                        <i class="fas fa-check-circle me-1"></i> Activo
                                    </span>
                                    <small class="text-muted d-block mt-1">Disponible para trabajar</small>
                                @else
                                    <span class="badge bg-danger-soft text-danger px-3 py-2">
                                        <i class="fas fa-times-circle me-1"></i> Inactivo
                                    </span>
                                    <small class="text-muted d-block mt-1">No disponible</small>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Últimos Pedidos --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-receipt text-primary me-2"></i> Últimos Pedidos
                        <span class="badge bg-primary-soft text-primary ms-2">
                            {{ $empleado->pedidos->count() }}
                        </span>
                    </h5>
                    @if($empleado->pedidos->count() > 0)
                        <small class="text-muted">Últimos 5 pedidos</small>
                    @endif
                </div>
            </div>
            <div class="card-body">
                @if($empleado->pedidos->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 25%">Nº Pedido</th>
                                    <th style="width: 30%">Fecha y Hora</th>
                                    <th style="width: 25%" class="text-end">Total</th>
                                    <th style="width: 20%" class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($empleado->pedidos as $pedido)
                                <tr>
                                    <td>
                                        <strong class="text-primary">
                                            <i class="fas fa-hashtag me-1"></i>{{ $pedido->numero_pedido }}
                                        </strong>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $pedido->fecha }}</strong>
                                            <small class="text-muted d-block">
                                                <i class="far fa-clock me-1"></i> {{ $pedido->hora }}
                                            </small>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <strong class="text-success">
                                            S/ {{ number_format($pedido->total, 2) }}
                                        </strong>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.pedidos.show', $pedido) }}" 
                                           class="btn btn-sm btn-outline-info"
                                           data-bs-toggle="tooltip"
                                           title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    @if($empleado->pedidos_count > 5)
                        <div class="text-center mt-3">
                            <a href="{{ route('admin.pedidos.index', ['empleado_id' => $empleado->id]) }}" 
                               class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-list me-1"></i> 
                                Ver todos los pedidos ({{ $empleado->pedidos_count }})
                            </a>
                        </div>
                    @endif
                @else
                    <div class="empty-state text-center py-4">
                        <div class="empty-icon mb-3">
                            <i class="fas fa-receipt fa-3x text-muted"></i>
                        </div>
                        <h5 class="text-muted mb-2">Sin pedidos asignados</h5>
                        <p class="text-muted mb-0">
                            Este empleado aún no tiene pedidos registrados
                        </p>
                    </div>
                @endif
            </div>
        </div>

    </div>

    {{-- ==========================================
        COLUMNA DERECHA - TIMELINE Y ACCIONES
        ========================================== --}}
    <div class="col-lg-4">
        
        {{-- Timeline de Actividad --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0">
                    <i class="fas fa-history text-primary me-2"></i> Historial
                </h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    {{-- Última actualización --}}
                    <div class="timeline-item">
                        <div class="timeline-marker bg-warning"></div>
                        <div class="timeline-content">
                            <div class="timeline-header">
                                <i class="fas fa-sync-alt text-warning me-1"></i>
                                <strong>Última Actualización</strong>
                            </div>
                            <p class="timeline-text mb-1">
                                Los datos del empleado fueron actualizados
                            </p>
                            <small class="text-muted">
                                <i class="far fa-clock me-1"></i>
                                {{ $empleado->updated_at->format('d/m/Y \a \l\a\s h:i A') }}
                            </small>
                        </div>
                    </div>

                    {{-- Registro --}}
                    <div class="timeline-item">
                        <div class="timeline-marker bg-success"></div>
                        <div class="timeline-content">
                            <div class="timeline-header">
                                <i class="fas fa-user-plus text-success me-1"></i>
                                <strong>Registro en el Sistema</strong>
                            </div>
                            <p class="timeline-text mb-1">
                                El empleado fue registrado en el sistema
                            </p>
                            <small class="text-muted">
                                <i class="far fa-clock me-1"></i>
                                {{ $empleado->created_at->format('d/m/Y \a \l\a\s h:i A') }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Acciones Rápidas --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0">
                    <i class="fas fa-bolt text-primary me-2"></i> Acciones Rápidas
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.empleados.edit', $empleado) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-2"></i> Editar Empleado
                    </a>
                    <a href="{{ route('admin.pedidos.create') }}?empleado_id={{ $empleado->id }}" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-2"></i> Nuevo Pedido
                    </a>
                    <a href="{{ route('admin.empleados.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i> Volver al Listado
                    </a>
                </div>
            </div>
        </div>

        {{-- Información Adicional --}}
        <div class="card shadow-sm border-0 mb-4 bg-light">
            <div class="card-body">
                <h6 class="mb-3">
                    <i class="fas fa-info-circle text-primary me-2"></i> Información
                </h6>
                <p class="small text-muted mb-3">
                    Desde aquí puedes ver toda la información del empleado. 
                    Usa las acciones rápidas para editar o gestionar este registro.
                </p>
                <hr>
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        <i class="fas fa-calendar-alt me-1"></i> Creado
                    </small>
                    <strong class="small">{{ $empleado->created_at->format('d/m/Y') }}</strong>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-2">
                    <small class="text-muted">
                        <i class="fas fa-clock me-1"></i> Actualizado
                    </small>
                    <strong class="small">{{ $empleado->updated_at->format('d/m/Y') }}</strong>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- Form oculto para eliminar --}}
<form id="delete-form" action="{{ route('admin.empleados.destroy', $empleado) }}" method="POST" class="d-none">
    @csrf
    @method('DELETE')
</form>

{{-- ==========================================
    ESTILOS PERSONALIZADOS
    ========================================== --}}
@push('styles')
<style>
    /* Profile Header */
    .profile-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        background-size: cover;
        position: relative;
    }

    .profile-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.9) 0%, rgba(118, 75, 162, 0.9) 100%);
    }

    .profile-header .card-body {
        position: relative;
        z-index: 1;
    }

    .profile-header h2,
    .profile-header p,
    .profile-header .text-muted {
        color: white !important;
    }

    /* Avatar XL */
    .avatar-circle-xl {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: white;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        flex-shrink: 0;
    }

    .avatar-initials-xl {
        font-size: 36px;
        font-weight: 700;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
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

    /* Info Items */
    .info-item {
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
        height: 100%;
    }

    .info-label {
        font-size: 13px;
        font-weight: 600;
        color: #6c757d;
        display: block;
        margin-bottom: 8px;
    }

    .info-value {
        font-size: 16px;
        font-weight: 600;
        color: #2d3748;
    }

    /* Timeline */
    .timeline {
        position: relative;
        padding-left: 30px;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 8px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e9ecef;
    }

    .timeline-item {
        position: relative;
        padding-bottom: 25px;
    }

    .timeline-item:last-child {
        padding-bottom: 0;
    }

    .timeline-marker {
        position: absolute;
        left: -26px;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        border: 3px solid white;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }

    .timeline-content {
        background: #f8f9fa;
        padding: 12px 15px;
        border-radius: 8px;
        border-left: 3px solid #dee2e6;
    }

    .timeline-header {
        font-size: 14px;
        margin-bottom: 5px;
    }

    .timeline-text {
        font-size: 13px;
        color: #6c757d;
    }

    /* Table */
    .table thead th {
        font-weight: 600;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6c757d;
        border-bottom: 2px solid #dee2e6;
        padding: 12px;
    }

    .table tbody td {
        padding: 12px;
        vertical-align: middle;
    }

    .table tbody tr {
        transition: all 0.3s ease;
    }

    .table tbody tr:hover {
        background-color: #f8f9fa;
        transform: scale(1.01);
    }

    /* Empty State */
    .empty-state {
        padding: 40px 20px;
    }

    .empty-icon {
        opacity: 0.3;
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

    /* Responsive */
    @media (max-width: 768px) {
        .avatar-circle-xl {
            width: 80px;
            height: 80px;
        }

        .avatar-initials-xl {
            font-size: 28px;
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
        $('#btnDelete').on('click', function() {
            const empleadoName = '{{ $empleado->name }}';
            const pedidosCount = {{ $empleado->pedidos_count ?? 0 }};

            let warningHtml = `
                <div class="text-start">
                    <p class="mb-2">Estás a punto de eliminar al empleado:</p>
                    <div class="alert alert-danger mb-3">
                        <i class="fas fa-user me-2"></i>
                        <strong>${empleadoName}</strong>
                    </div>
            `;

            if (pedidosCount > 0) {
                warningHtml += `
                    <div class="alert alert-warning mb-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>¡Atención!</strong> Este empleado tiene <strong>${pedidosCount}</strong> pedido(s) asignado(s).
                        <br><small>No podrá ser eliminado hasta que se reasignen o eliminen estos pedidos.</small>
                    </div>
                `;
            }

            warningHtml += `
                    <p class="text-muted small mb-2">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        Esta acción no se puede deshacer.
                    </p>
                    ${pedidosCount === 0 ? '<p class="text-muted small mb-0"><i class="fas fa-check-circle me-1"></i> El empleado no tiene pedidos asignados.</p>' : ''}
                </div>
            `;

            Swal.fire({
                title: pedidosCount > 0 ? '¡No se puede eliminar!' : '¿Estás seguro?',
                html: warningHtml,
                icon: pedidosCount > 0 ? 'error' : 'warning',
                showCancelButton: true,
                confirmButtonColor: pedidosCount > 0 ? '#6c757d' : '#e74a3b',
                cancelButtonColor: '#6c757d',
                confirmButtonText: pedidosCount > 0 ? '<i class="fas fa-times me-2"></i> Entendido' : '<i class="fas fa-trash me-2"></i> Sí, eliminar',
                cancelButtonText: '<i class="fas fa-times me-2"></i> Cancelar',
                reverseButtons: true,
                showConfirmButton: pedidosCount === 0,
                showCancelButton: true
            }).then((result) => {
                if (result.isConfirmed && pedidosCount === 0) {
                    Swal.fire({
                        title: 'Eliminando empleado...',
                        text: 'Por favor espera',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $('#delete-form').submit();
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
    });
</script>
@endpush

@endsection
