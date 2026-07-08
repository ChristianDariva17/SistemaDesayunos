@extends('layouts.app')

@section('title', 'Pedido #' . $pedido->numero_pedido)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.pedidos.index') }}">Pedidos</a></li>
    <li class="breadcrumb-item active">Pedido #{{ $pedido->numero_pedido }}</li>
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
    ALERTA - INFORMACIÓN FALTANTE
    ========================================== --}}
@if((!$pedido->cliente_id || !$pedido->cliente) || (!$pedido->empleado_id || !$pedido->empleado))
    <div class="alert alert-warning alert-dismissible fade show shadow-sm animate__animated animate__fadeInDown" role="alert">
        <div class="d-flex align-items-start">
            <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
            <div class="flex-grow-1">
                <h5 class="alert-heading mb-2">
                    <i class="fas fa-info-circle me-2"></i> Este pedido tiene información faltante
                </h5>
                <ul class="mb-0">
                    @if(!$pedido->cliente_id || !$pedido->cliente)
                        <li>El cliente asociado no existe o fue eliminado.</li>
                    @endif
                    @if(!$pedido->empleado_id || !$pedido->empleado)
                        <li>El empleado asignado no existe o fue eliminado.</li>
                    @endif
                </ul>
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
            <div class="d-flex align-items-center mb-2">
                <h1 class="page-title mb-0 me-3">
                    <i class="fas fa-file-invoice text-primary"></i> Pedido
                </h1>
                <span class="badge bg-primary-soft text-primary fs-5">
                    #{{ $pedido->numero_pedido }}
                </span>
                {{-- Badge de Estado --}}
                @switch($pedido->estado)
                    @case('pendiente')
                        <span class="badge bg-warning ms-2 fs-6">
                            <i class="fas fa-clock me-1"></i> Pendiente
                        </span>
                        @break
                    @case('procesando')
                        <span class="badge bg-info ms-2 fs-6">
                            <i class="fas fa-spinner me-1"></i> Procesando
                        </span>
                        @break
                    @case('completado')
                        <span class="badge bg-success ms-2 fs-6">
                            <i class="fas fa-check-circle me-1"></i> Completado
                        </span>
                        @break
                    @case('cancelado')
                        <span class="badge bg-danger ms-2 fs-6">
                            <i class="fas fa-times-circle me-1"></i> Cancelado
                        </span>
                        @break
                @endswitch
            </div>
            <p class="page-subtitle text-muted mb-0">
                <i class="fas fa-calendar me-1"></i> 
                Creado el {{ $pedido->fecha->format('d/m/Y') }} a las {{ \Carbon\Carbon::parse($pedido->fecha->format('Y-m-d') . ' ' . $pedido->hora)->format('H:i') }}
            </p>
        </div>
        <div class="col-auto">
            <div class="btn-group" role="group">
                <a href="{{ route('admin.pedidos.edit', $pedido->id) }}" class="btn btn-warning">
                    <i class="fas fa-edit me-2"></i> Editar
                </a>
                <button type="button" class="btn btn-info" onclick="window.print()">
                    <i class="fas fa-print me-2"></i> Imprimir
                </button>
                <button type="button" class="btn btn-danger" id="btnEliminar">
                    <i class="fas fa-trash me-2"></i> Eliminar
                </button>
            </div>
            <a href="{{ route('admin.pedidos.index') }}" class="btn btn-outline-secondary ms-2">
                <i class="fas fa-arrow-left me-2"></i> Volver
            </a>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- ==========================================
        COLUMNA IZQUIERDA - INFORMACIÓN PRINCIPAL
        ========================================== --}}
    <div class="col-lg-8">
        
        {{-- CLIENTE --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0">
                    <i class="fas fa-user text-primary me-2"></i> Cliente
                </h5>
            </div>
            <div class="card-body">
                @if($pedido->cliente)
                    {{-- ✅ CASO 1: Cliente existe --}}
                    <div class="d-flex align-items-center">
                        <div class="avatar-circle me-3 {{ 'avatar-' . strtolower(substr($pedido->cliente->nombre, 0, 1)) }}">
                            <span class="avatar-initials">
                                {{ strtoupper(substr($pedido->cliente->nombre, 0, 1)) }}{{ strtoupper(substr($pedido->cliente->apellido ?? '', 0, 1)) }}
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="mb-1">{{ trim($pedido->cliente->nombre . ' ' . ($pedido->cliente->apellido ?? '')) }}</h5>
                            <div class="text-muted">
                                @if($pedido->cliente->email)
                                    <div class="mb-1">
                                        <i class="fas fa-envelope me-2"></i>
                                        <a href="mailto:{{ $pedido->cliente->email }}">{{ $pedido->cliente->email }}</a>
                                    </div>
                                @endif
                                @if($pedido->cliente->telefono)
                                    <div class="mb-1">
                                        <i class="fas fa-phone me-2"></i>
                                        <a href="tel:{{ $pedido->cliente->telefono }}">{{ $pedido->cliente->telefono }}</a>
                                    </div>
                                @endif
                                <div>
                                    <i class="fas fa-id-badge me-2"></i>
                                    ID: #{{ $pedido->cliente->id }}
                                </div>
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('admin.clientes.show', $pedido->cliente->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye me-1"></i> Ver Cliente
                            </a>
                        </div>
                    </div>
                @elseif($pedido->cliente_id)
                    {{-- ❌ CASO 2: cliente_id existe pero el cliente fue eliminado --}}
                    <div class="alert alert-danger mb-0">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-user-slash fa-2x me-3"></i>
                            <div>
                                <h6 class="mb-1">Cliente Eliminado</h6>
                                <p class="mb-0 small">El cliente asociado a este pedido fue eliminado de la base de datos</p>
                                <p class="mb-0 small text-muted">ID original: #{{ $pedido->cliente_id }}</p>
                            </div>
                        </div>
                    </div>
                @else
                    {{-- ⚠️ CASO 3: cliente_id es NULL --}}
                    <div class="alert alert-warning mb-0">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                            <div>
                                <h6 class="mb-1">Sin Cliente Asignado</h6>
                                <p class="mb-0 small">Este pedido no tiene un cliente asociado (cliente_id es NULL)</p>
                                <p class="mb-0 small text-muted">Esto puede ocurrir si se creó el pedido sin seleccionar cliente</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- EMPLEADO --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0">
                    <i class="fas fa-user-tie text-primary me-2"></i> Empleado Asignado
                </h5>
            </div>
            <div class="card-body">
                @if($pedido->empleado)
                    {{-- ✅ CASO 1: Empleado existe --}}
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="avatar-circle-sm me-3 bg-info">
                                <i class="fas fa-user-tie text-white"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">{{ $pedido->empleado->nombre }}</h6>
                                <div class="text-muted small">
                                    @if(isset($pedido->empleado->rol_operativo))
                                        <span class="badge bg-secondary me-1">{{ ucfirst($pedido->empleado->rol_operativo) }}</span>
                                    @endif
                                    @if(isset($pedido->empleado->estado))
                                        <span class="badge bg-success">{{ ucfirst($pedido->empleado->estado) }}</span>
                                    @endif
                                    <div class="mt-1">
                                        <i class="fas fa-id-badge me-1"></i> ID: #{{ $pedido->empleado->id }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('admin.empleados.show', $pedido->empleado->id) }}" class="btn btn-sm btn-outline-info">
                                <i class="fas fa-eye me-1"></i> Ver Empleado
                            </a>
                        </div>
                    </div>
                @elseif($pedido->empleado_id)
                    {{-- ❌ CASO 2: empleado_id existe pero el empleado fue eliminado --}}
                    <div class="alert alert-danger mb-0">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-user-slash fa-2x me-3"></i>
                            <div>
                                <h6 class="mb-1">Empleado Eliminado</h6>
                                <p class="mb-0 small">El empleado asignado fue eliminado de la base de datos</p>
                                <p class="mb-0 small text-muted">ID original: #{{ $pedido->empleado_id }}</p>
                            </div>
                        </div>
                    </div>
                @else
                    {{-- ⚠️ CASO 3: empleado_id es NULL --}}
                    <div class="alert alert-info mb-0">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-info-circle fa-2x me-3"></i>
                            <div>
                                <h6 class="mb-1">Sin Empleado Asignado</h6>
                                <p class="mb-0 small">Este pedido no tiene un empleado asociado</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- PRODUCTOS --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-shopping-cart text-success me-2"></i> Productos del Pedido
                        <span class="badge bg-primary-soft text-primary ms-2">{{ $pedido->productos->count() }}</span>
                    </h5>
                </div>
            </div>
            <div class="card-body p-0">
                @if($pedido->productos->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50%">Producto</th>
                                    <th style="width: 15%" class="text-center">Cantidad</th>
                                    <th style="width: 15%" class="text-end">Precio Unit.</th>
                                    <th style="width: 20%" class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pedido->productos as $producto)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="product-icon me-2">
                                                <i class="fas fa-box text-primary"></i>
                                            </div>
                                            <strong>{{ $producto->nombre }}</strong>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary-soft text-secondary px-3 py-2">
                                            <i class="fas fa-times me-1"></i>
                                            {{ $producto->pivot->cantidad }} 
                                            {{ $producto->pivot->cantidad == 1 ? 'unidad' : 'unidades' }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-success fw-bold">S/ {{ number_format($producto->pivot->precio_unitario, 2) }}</span>
                                        <small class="text-muted d-block">c/u</small>
                                    </td>
                                    <td class="text-end">
                                        <span class="fs-5 fw-bold text-primary">
                                            S/ {{ number_format($producto->pivot->subtotal, 2) }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="3" class="text-end">
                                        <strong class="fs-5">TOTAL:</strong>
                                    </td>
                                    <td class="text-end">
                                        <span class="fs-4 fw-bold text-success">
                                            S/ {{ number_format($pedido->total, 2) }}
                                        </span>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <div class="empty-state py-5">
                        <div class="text-center">
                            <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted mb-2">No hay productos en este pedido</h5>
                            <p class="text-muted">Este pedido no tiene productos asociados</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- OBSERVACIONES --}}
        @if($pedido->observaciones)
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0">
                    <i class="fas fa-sticky-note text-warning me-2"></i> Observaciones
                </h5>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $pedido->observaciones }}</p>
            </div>
        </div>
        @endif

    </div>

    {{-- ==========================================
        COLUMNA DERECHA - SIDEBAR
        ========================================== --}}
    <div class="col-lg-4">
        <div class="pedido-side-panel">
        
        {{-- RESUMEN DEL PEDIDO --}}
        <div class="card shadow-sm border-0 mb-4 pedido-summary-card">
            <div class="card-header bg-gradient-primary text-white border-0">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i> Resumen del Pedido
                </h5>
            </div>
            <div class="card-body">
                {{-- Estado Actual --}}
                <div class="mb-3">
                    <small class="text-muted d-block mb-1">Estado Actual:</small>
                    @switch($pedido->estado)
                        @case('pendiente')
                            <span class="badge bg-warning fs-6 w-100 py-2">
                                <i class="fas fa-clock me-2"></i> Pendiente
                            </span>
                            @break
                        @case('procesando')
                            <span class="badge bg-info fs-6 w-100 py-2">
                                <i class="fas fa-spinner me-2"></i> Procesando
                            </span>
                            @break
                        @case('completado')
                            <span class="badge bg-success fs-6 w-100 py-2">
                                <i class="fas fa-check-circle me-2"></i> Completado
                            </span>
                            @break
                        @case('cancelado')
                            <span class="badge bg-danger fs-6 w-100 py-2">
                                <i class="fas fa-times-circle me-2"></i> Cancelado
                            </span>
                            @break
                    @endswitch
                </div>

                <hr>

                {{-- Fecha del Pedido --}}
                <div class="info-item mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">
                            <i class="fas fa-calendar-day me-2"></i> Fecha del Pedido
                        </span>
                    </div>
                    <div class="mt-1">
                        <strong class="d-block">{{ \Carbon\Carbon::parse($pedido->fecha)->format('d/m/Y') }}</strong>
                        <small class="text-muted">
                            <i class="fas fa-clock me-1"></i> {{ $pedido->hora }}
                        </small>
                    </div>
                </div>

                {{-- Método de Pago --}}
                @if($pedido->metodo_pago)
                <div class="info-item mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">
                            <i class="fas fa-money-bill-wave me-2"></i> Método de Pago
                        </span>
                    </div>
                    <div class="mt-1">
                        @php
                            $metodosIconos = [
                                'efectivo' => '💵',
                                'tarjeta' => '💳',
                                'transferencia' => '🏦',
                                'otro' => '📱'
                            ];
                        @endphp
                        <strong>
                            {{ $metodosIconos[$pedido->metodo_pago] ?? '💰' }} 
                            {{ ucfirst($pedido->metodo_pago) }}
                        </strong>
                    </div>
                </div>
                @endif

                <hr>

                {{-- Total del Pedido --}}
                <div class="total-section">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="h5 mb-0">Total del Pedido:</span>
                        <span class="h3 mb-0 text-success">S/ {{ number_format($pedido->total, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- TIMELINE --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0">
                    <i class="fas fa-history text-primary me-2"></i> Historial
                </h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    {{-- Pedido Creado --}}
                    <div class="timeline-item">
                        <div class="timeline-marker bg-success">
                            <i class="fas fa-plus"></i>
                        </div>
                        <div class="timeline-content">
                            <h6 class="mb-1">Pedido Creado</h6>
                            <p class="mb-0 small text-muted">
                                {{ $pedido->fecha->format('d/m/Y') }} {{ \Carbon\Carbon::parse($pedido->fecha->format('Y-m-d') . ' ' . $pedido->hora)->format('H:i') }}
                            </p>
                            <p class="mb-0 small text-muted">
                                <i class="fas fa-clock me-1"></i> {{ \Carbon\Carbon::parse($pedido->fecha->format('Y-m-d') . ' ' . $pedido->hora)->diffForHumans() }}
                            </p>
                        </div>
                    </div>

                    {{-- Última Actualización --}}
                    @if($pedido->updated_at != $pedido->created_at)
                    <div class="timeline-item">
                        <div class="timeline-marker bg-info">
                            <i class="fas fa-edit"></i>
                        </div>
                        <div class="timeline-content">
                            <h6 class="mb-1">Última Actualización</h6>
                            <p class="mb-0 small text-muted">
                                {{ $pedido->updated_at->format('d/m/Y H:i') }}
                            </p>
                            <p class="mb-0 small text-muted">
                                <i class="fas fa-clock me-1"></i> {{ $pedido->updated_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ACCIONES RÁPIDAS --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0">
                    <i class="fas fa-bolt text-warning me-2"></i> Acciones Rápidas
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.pedidos.edit', $pedido->id) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-2"></i> Editar Pedido
                    </a>
                    <button type="button" class="btn btn-info" onclick="window.print()">
                        <i class="fas fa-print me-2"></i> Imprimir Pedido
                    </button>
                    <button type="button" class="btn btn-danger" id="btnEliminarSidebar">
                        <i class="fas fa-trash me-2"></i> Eliminar Pedido
                    </button>
                </div>
            </div>
        </div>

        </div>

    </div>
</div>

{{-- Form oculto para eliminar --}}
<form id="delete-form" action="{{ route('admin.pedidos.destroy', $pedido->id) }}" method="POST" class="d-none">
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

    /* Gradient Header */
    .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    /* Avatar Circles */
    .avatar-circle {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .avatar-initials {
        font-size: 20px;
        font-weight: 700;
        color: white;
    }

    .avatar-circle-sm {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
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

    /* Product Icon */
    .product-icon {
        width: 35px;
        height: 35px;
        background: #f8f9fa;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

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
    }

    .table tfoot td {
        padding: 20px 12px;
        font-weight: 600;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }

    /* Timeline */
    .timeline {
        position: relative;
        padding-left: 30px;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 10px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e9ecef;
    }

    .timeline-item {
        position: relative;
        padding-bottom: 20px;
    }

    .timeline-item:last-child {
        padding-bottom: 0;
    }

    .timeline-marker {
        position: absolute;
        left: -26px;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 10px;
        z-index: 1;
    }

    .timeline-content {
        background: #f8f9fa;
        padding: 12px;
        border-radius: 8px;
    }

    /* Info Items */
    .info-item {
        padding: 12px;
        background: #f8f9fa;
        border-radius: 8px;
    }

    .total-section {
        padding: 15px;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 8px;
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

    /* Sticky right-side panel */
    .pedido-side-panel {
        position: sticky;
        top: calc(var(--header-height, 70px) + 20px);
        z-index: 1;
        align-self: start;
        max-height: calc(100vh - var(--header-height, 70px) - 40px);
        overflow-y: auto;
        overscroll-behavior: contain;
    }

    .pedido-summary-card {
        z-index: auto;
    }

    /* Badges */
    .bg-primary-soft {
        background-color: rgba(255, 107, 53, 0.1);
    }

    .bg-secondary-soft {
        background-color: rgba(108, 117, 125, 0.1);
    }

    /* Animations */
    .animate__animated {
        animation-duration: 0.5s;
    }

    /* Responsive */
    @media (max-width: 991.98px) {
        .page-title {
            font-size: 22px;
        }

        .pedido-side-panel {
            position: static;
            top: auto;
            max-height: none;
            overflow: visible;
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
            width: 100%;
        }

        .btn-group .btn {
            margin-bottom: 5px;
        }
    }

    /* Print Styles */
    @media print {
        .page-header .btn-group,
        .page-header .btn,
        .card:last-child,
        .sidebar,
        .no-print {
            display: none !important;
        }

        .card {
            border: 1px solid #dee2e6 !important;
            box-shadow: none !important;
            page-break-inside: avoid;
        }

        .table {
            font-size: 12px;
        }

        body {
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
        $('#btnEliminar, #btnEliminarSidebar').on('click', function() {
            Swal.fire({
                title: '¿Estás seguro?',
                html: `
                    <div class="text-start">
                        <p class="mb-2">Estás a punto de eliminar el pedido:</p>
                        <div class="alert alert-danger mb-3">
                            <i class="fas fa-shopping-bag me-2"></i>
                            <strong>#{{ $pedido->numero_pedido }}</strong>
                        </div>
                        <p class="text-muted small mb-2">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            Esta acción no se puede deshacer.
                        </p>
                        <p class="text-muted small mb-0">
                            <i class="fas fa-box me-1"></i>
                            El stock de los productos será restaurado automáticamente.
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
                        title: 'Eliminando pedido...',
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
