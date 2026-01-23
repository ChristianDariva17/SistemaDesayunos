@extends('layouts.app')

@section('title', 'Editar Pedido #' . $pedido->numero_pedido)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.pedidos.index') }}">Pedidos</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.pedidos.show', $pedido->id) }}">Pedido #{{ $pedido->numero_pedido }}</a></li>
    <li class="breadcrumb-item active">Editar</li>
@endsection

@section('content')

{{-- ==========================================
    ALERTAS DE VALIDACIÓN
    ========================================== --}}
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show shadow-sm animate__animated animate__fadeInDown" role="alert">
        <div class="d-flex align-items-start">
            <i class="fas fa-exclamation-circle fa-2x me-3"></i>
            <div class="flex-grow-1">
                <h5 class="alert-heading mb-2">
                    <i class="fas fa-exclamation-triangle me-2"></i> Errores de Validación
                </h5>
                <p class="mb-2">Por favor corrige los siguientes errores:</p>
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
@endif

{{-- ==========================================
    ALERTA - CLIENTE ELIMINADO
    ========================================== --}}
@if(!$pedido->cliente_id || !$pedido->cliente)
    <div class="alert alert-warning alert-dismissible fade show shadow-sm animate__animated animate__fadeInDown" role="alert">
        <div class="d-flex align-items-start">
            <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
            <div class="flex-grow-1">
                <h5 class="alert-heading mb-2">
                    <i class="fas fa-user-slash me-2"></i> Cliente No Disponible
                </h5>
                <p class="mb-2">
                    @if(!$pedido->cliente_id)
                        <strong>No hay cliente asignado</strong> (cliente_id es NULL)
                    @else
                        El cliente <strong>ID #{{ $pedido->cliente_id }}</strong> fue eliminado del sistema
                    @endif
                </p>
                <p class="mb-0">
                    <i class="fas fa-info-circle me-1"></i> 
                    Por favor, selecciona un nuevo cliente antes de guardar los cambios.
                </p>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
@endif

{{-- ==========================================
    ALERTA - EMPLEADO ELIMINADO
    ========================================== --}}
@if(!$pedido->empleado_id || !$pedido->empleado)
    <div class="alert alert-info alert-dismissible fade show shadow-sm animate__animated animate__fadeInDown" role="alert">
        <div class="d-flex align-items-start">
            <i class="fas fa-info-circle fa-2x me-3"></i>
            <div class="flex-grow-1">
                <h5 class="alert-heading mb-2">
                    <i class="fas fa-user-tie me-2"></i> Empleado No Disponible
                </h5>
                <p class="mb-2">
                    @if(!$pedido->empleado_id)
                        <strong>No hay empleado asignado</strong> (empleado_id es NULL)
                    @else
                        El empleado <strong>ID #{{ $pedido->empleado_id }}</strong> fue eliminado del sistema
                    @endif
                </p>
                <p class="mb-0">
                    <i class="fas fa-info-circle me-1"></i> 
                    Puedes asignar un nuevo empleado o dejarlo sin asignar.
                </p>
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
                    <i class="fas fa-edit text-primary"></i> Editar Pedido
                </h1>
                <span class="badge bg-primary-soft text-primary fs-5">
                    #{{ $pedido->numero_pedido }}
                </span>
            </div>
            <p class="page-subtitle text-muted mb-0">
                <i class="fas fa-info-circle me-1"></i> Modifica los datos del pedido existente
            </p>
        </div>
        <div class="col-auto">
            <div class="btn-group" role="group">
                <a href="{{ route('admin.pedidos.show', $pedido->id) }}" class="btn btn-outline-info">
                    <i class="fas fa-eye me-2"></i> Ver Pedido
                </a>
                <a href="{{ route('admin.pedidos.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i> Volver
                </a>
            </div>
        </div>
    </div>
</div>

{{-- ==========================================
    FORMULARIO
    ========================================== --}}
<form action="{{ route('admin.pedidos.update', $pedido->id) }}" method="POST" id="pedidoForm">
    @csrf
    @method('PUT')
    
    <div class="row g-4">
        {{-- ==========================================
            COLUMNA IZQUIERDA - DATOS DEL PEDIDO
            ========================================== --}}
        <div class="col-lg-8">
            
            {{-- Información del Pedido --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="fas fa-clipboard-list text-primary me-2"></i> Información del Pedido
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        {{-- Cliente --}}
                        <div class="col-md-6">
                            <label for="cliente_id" class="form-label fw-semibold required">
                                <i class="fas fa-user text-muted"></i> Cliente
                            </label>
                            <select class="form-select form-select-lg @error('cliente_id') is-invalid @enderror" 
                                    id="cliente_id" 
                                    name="cliente_id" 
                                    required>
                                <option value="">Selecciona un cliente</option>
                                @foreach($clientes as $cliente)
                                    <option value="{{ $cliente->id }}" 
                                            {{ old('cliente_id', $pedido->cliente_id) == $cliente->id ? 'selected' : '' }}>
                                        {{ $cliente->nombre }} {{ $cliente->apellido }} - {{ $cliente->email }}
                                    </option>
                                @endforeach
                            </select>
                            @error('cliente_id')
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-circle me-1"></i> {{ $message }}
                                </div>
                            @enderror
                            @if(!$pedido->cliente_id || !$pedido->cliente)
                                <div class="form-text text-warning">
                                    <i class="fas fa-exclamation-triangle me-1"></i> 
                                    <strong>Acción requerida:</strong> Selecciona un cliente válido
                                </div>
                            @else
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i> Cliente actual asignado al pedido
                                </div>
                            @endif
                        </div>

                        {{-- Empleado --}}
                        <div class="col-md-6">
                            <label for="empleado_id" class="form-label fw-semibold">
                                <i class="fas fa-user-tie text-muted"></i> Empleado
                            </label>
                            <select class="form-select form-select-lg @error('empleado_id') is-invalid @enderror" 
                                    id="empleado_id" 
                                    name="empleado_id">
                                <option value="">Sin asignar</option>
                                @foreach($empleados as $empleado)
                                    <option value="{{ $empleado->id }}" 
                                            {{ old('empleado_id', $pedido->empleado_id) == $empleado->id ? 'selected' : '' }}>
                                        {{ $empleado->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('empleado_id')
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-circle me-1"></i> {{ $message }}
                                </div>
                            @enderror
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i> Opcional
                            </div>
                        </div>

                        {{-- Fecha --}}
                        <div class="col-md-6">
                            <label for="fecha" class="form-label fw-semibold required">
                                <i class="fas fa-calendar-alt text-muted"></i> Fecha
                            </label>
                            <input type="date" 
                                   class="form-control @error('fecha') is-invalid @enderror" 
                                   id="fecha" 
                                   name="fecha" 
                                   value="{{ old('fecha', $pedido->fecha) }}"
                                   required>
                            @error('fecha')
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-circle me-1"></i> {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- Hora --}}
                        <div class="col-md-6">
                            <label for="hora" class="form-label fw-semibold required">
                                <i class="fas fa-clock text-muted"></i> Hora
                            </label>
                            <input type="time" 
                                   class="form-control @error('hora') is-invalid @enderror" 
                                   id="hora" 
                                   name="hora" 
                                   value="{{ old('hora', $pedido->hora) }}"
                                   required>
                            @error('hora')
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-circle me-1"></i> {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- Estado --}}
                        <div class="col-md-12">
                            <label for="estado" class="form-label fw-semibold required">
                                <i class="fas fa-flag text-muted"></i> Estado del Pedido
                            </label>
                            <select class="form-select form-select-lg @error('estado') is-invalid @enderror" 
                                    id="estado" 
                                    name="estado"
                                    required>
                                <option value="pendiente" {{ old('estado', $pedido->estado) == 'pendiente' ? 'selected' : '' }}>
                                    ⏳ Pendiente
                                </option>
                                <option value="en_proceso" {{ old('estado', $pedido->estado) == 'en_proceso' ? 'selected' : '' }}>
                                    🔄 En Proceso
                                </option>
                                <option value="completado" {{ old('estado', $pedido->estado) == 'completado' ? 'selected' : '' }}>
                                    ✅ Completado
                                </option>
                                <option value="cancelado" {{ old('estado', $pedido->estado) == 'cancelado' ? 'selected' : '' }}>
                                    ❌ Cancelado
                                </option>
                            </select>
                            @error('estado')
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-circle me-1"></i> {{ $message }}
                                </div>
                            @enderror
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i> 
                                Estado actual: 
                                @switch($pedido->estado)
                                    @case('pendiente')
                                        <span class="badge bg-warning">Pendiente</span>
                                        @break
                                    @case('en_proceso')
                                        <span class="badge bg-info">En Proceso</span>
                                        @break
                                    @case('completado')
                                        <span class="badge bg-success">Completado</span>
                                        @break
                                    @case('cancelado')
                                        <span class="badge bg-danger">Cancelado</span>
                                        @break
                                @endswitch
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Productos del Pedido --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-shopping-cart text-success me-2"></i> Productos del Pedido
                            <span class="badge bg-primary-soft text-primary ms-2" id="productCount">{{ $pedido->productos->count() }}</span>
                        </h5>
                        <button type="button" class="btn btn-sm btn-primary" id="btnAgregarProducto">
                            <i class="fas fa-plus-circle me-1"></i> Agregar Producto
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div id="productosContainer">
                        {{-- Tabla de Productos --}}
                        <div id="productosTable">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 35%">Producto</th>
                                            <th style="width: 15%" class="text-center">Cantidad</th>
                                            <th style="width: 15%" class="text-end">Precio</th>
                                            <th style="width: 20%" class="text-end">Subtotal</th>
                                            <th style="width: 15%" class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="productosTableBody">
                                        {{-- Productos existentes --}}
                                        @foreach($pedido->productos as $index => $producto)
                                        <tr data-index="{{ $index }}">
                                            <td>
                                                <strong>{{ $producto->nombre }}</strong>
                                                <input type="hidden" name="productos[{{ $index }}][producto_id]" value="{{ $producto->id }}">
                                                <input type="hidden" name="productos[{{ $index }}][pivot_id]" value="{{ $producto->pivot->id }}">
                                            </td>
                                            <td class="text-center">
                                                <div class="input-group input-group-sm" style="max-width: 120px; margin: 0 auto;">
                                                    <button class="btn btn-outline-secondary btn-cantidad" type="button" data-action="minus" data-index="{{ $index }}">
                                                        <i class="fas fa-minus"></i>
                                                    </button>
                                                    <input type="number" 
                                                           class="form-control text-center cantidad-input" 
                                                           name="productos[{{ $index }}][cantidad]" 
                                                           value="{{ $producto->pivot->cantidad }}"
                                                           min="1"
                                                           data-index="{{ $index }}"
                                                           data-precio="{{ $producto->precio }}"
                                                           data-stock="{{ $producto->stock + $producto->pivot->cantidad }}"
                                                           data-cantidad-original="{{ $producto->pivot->cantidad }}">
                                                    <button class="btn btn-outline-secondary btn-cantidad" type="button" data-action="plus" data-index="{{ $index }}">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                </div>
                                                <small class="text-muted d-block mt-1">
                                                    Stock: <span class="stock-disponible" data-index="{{ $index }}">{{ $producto->stock + $producto->pivot->cantidad }}</span>
                                                </small>
                                            </td>
                                            <td class="text-end">
                                                <strong class="text-success">S/ {{ number_format($producto->precio, 2) }}</strong>
                                                <small class="text-muted d-block">c/u</small>
                                            </td>
                                            <td class="text-end">
                                                <strong class="fs-5 subtotal-producto" data-index="{{ $index }}">
                                                    S/ {{ number_format($producto->pivot->cantidad * $producto->precio, 2) }}
                                                </strong>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-danger btn-eliminar-producto"
                                                        data-index="{{ $index }}"
                                                        data-bs-toggle="tooltip"
                                                        title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Observaciones --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-sticky-note text-warning me-2"></i> Observaciones
                        </h5>
                        <span class="badge bg-light text-muted">Opcional</span>
                    </div>
                </div>
                <div class="card-body">
                    <textarea class="form-control @error('observaciones') is-invalid @enderror" 
                              id="observaciones" 
                              name="observaciones" 
                              rows="4"
                              maxlength="500"
                              placeholder="Notas adicionales sobre el pedido...">{{ old('observaciones', $pedido->observaciones) }}</textarea>
                    @error('observaciones')
                        <div class="invalid-feedback">
                            <i class="fas fa-exclamation-circle me-1"></i> {{ $message }}
                        </div>
                    @enderror
                    <div class="form-text d-flex justify-content-between">
                        <span>
                            <i class="fas fa-info-circle me-1"></i> Máximo 500 caracteres
                        </span>
                        <span id="charCount" class="text-muted">{{ strlen(old('observaciones', $pedido->observaciones ?? '')) }}/500</span>
                    </div>
                </div>
            </div>

        </div>

        {{-- ==========================================
            COLUMNA DERECHA - RESUMEN
            ========================================== --}}
        <div class="col-lg-4">
            
            {{-- Resumen del Pedido --}}
            <div class="card shadow-sm border-0 mb-4 sticky-top" style="top: 20px;">
                <div class="card-header bg-gradient-primary text-white border-0">
                    <h5 class="mb-0">
                        <i class="fas fa-calculator me-2"></i> Resumen del Pedido
                    </h5>
                </div>
                <div class="card-body">
                    {{-- Resumen Items --}}
                    <div class="resumen-item mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">
                                <i class="fas fa-boxes me-2"></i> Productos
                            </span>
                            <strong id="resumenProductos">{{ $pedido->productos->count() }}</strong>
                        </div>
                    </div>
                    
                    <div class="resumen-item mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">
                                <i class="fas fa-layer-group me-2"></i> Cantidad Total
                            </span>
                            <strong id="resumenCantidad">{{ $pedido->productos->sum('pivot.cantidad') }}</strong>
                        </div>
                    </div>

                    <hr>

                    {{-- Total --}}
                    <div class="total-section">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="h5 mb-0">Total a Pagar:</span>
                            <span class="h3 mb-0 text-success" id="totalPagar">S/ {{ number_format($pedido->total, 2) }}</span>
                        </div>
                    </div>

                    {{-- Input Hidden del Total --}}
                    <input type="hidden" name="total" id="totalInput" value="{{ $pedido->total }}">

                    <div class="alert alert-info mb-0">
                        <small>
                            <i class="fas fa-info-circle me-1"></i>
                            Los cambios en cantidades ajustarán el stock automáticamente.
                        </small>
                    </div>
                </div>
            </div>

            {{-- Botones de Acción --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg" id="btnGuardar">
                            <i class="fas fa-save me-2"></i> Guardar Cambios
                        </button>
                        <a href="{{ route('admin.pedidos.show', $pedido->id) }}" class="btn btn-outline-info">
                            <i class="fas fa-eye me-2"></i> Ver Pedido
                        </a>
                        <a href="{{ route('admin.pedidos.index') }}" class="btn btn-outline-danger">
                            <i class="fas fa-times me-2"></i> Cancelar
                        </a>
                    </div>
                </div>
            </div>

            {{-- Información del Pedido --}}
            <div class="card shadow-sm border-0 mb-4 bg-light">
                <div class="card-body">
                    <h6 class="mb-3">
                        <i class="fas fa-info-circle text-primary me-2"></i> Información del Pedido
                    </h6>
                    <div class="small">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Número:</span>
                            <strong>#{{ $pedido->numero_pedido }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Creado:</span>
                            <strong>{{ $pedido->created_at->format('d/m/Y H:i') }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-0">
                            <span class="text-muted">Modificado:</span>
                            <strong>{{ $pedido->updated_at->format('d/m/Y H:i') }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Guía Rápida --}}
            <div class="card shadow-sm border-0 mb-4 bg-light">
                <div class="card-body">
                    <h6 class="mb-3">
                        <i class="fas fa-lightbulb text-warning me-2"></i> Guía de Edición
                    </h6>
                    <ul class="small mb-0 ps-3">
                        <li class="mb-2">
                            Puedes <strong>agregar/eliminar</strong> productos
                        </li>
                        <li class="mb-2">
                            El stock se <strong>ajusta automáticamente</strong>
                        </li>
                        <li class="mb-2">
                            Puedes cambiar el <strong>estado</strong> del pedido
                        </li>
                        <li class="mb-2">
                            El <strong>total se actualiza</strong> en tiempo real
                        </li>
                        <li class="mb-0">
                            Cliente es <strong>obligatorio</strong>
                        </li>
                    </ul>
                </div>
            </div>

        </div>
    </div>

</form>

{{-- ==========================================
    MODAL AGREGAR PRODUCTO
    ========================================== --}}
<div class="modal fade" id="modalAgregarProducto" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle me-2"></i> Agregar Producto
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    {{-- Producto --}}
                    <div class="col-12">
                        <label for="producto_id" class="form-label fw-semibold">
                            <i class="fas fa-box text-muted"></i> Selecciona un Producto
                        </label>
                        <select class="form-select form-select-lg" id="producto_id">
                            <option value="">-- Selecciona un producto --</option>
                            @foreach($productos as $producto)
                                <option value="{{ $producto->id }}" 
                                        data-precio="{{ $producto->precio }}"
                                        data-stock="{{ $producto->stock }}"
                                        data-nombre="{{ $producto->nombre }}">
                                    {{ $producto->nombre }} - S/ {{ number_format($producto->precio, 2) }} (Stock: {{ $producto->stock }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Info del Producto Seleccionado --}}
                    <div class="col-12" id="productoInfo" style="display: none;">
                        <div class="alert alert-light border">
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>Precio:</strong><br>
                                    <span class="text-success fs-4" id="productoPrecio">S/ 0.00</span>
                                </div>
                                <div class="col-md-4">
                                    <strong>Stock Disponible:</strong><br>
                                    <span class="text-info fs-4" id="productoStock">0</span>
                                </div>
                                <div class="col-md-4">
                                    <strong>Subtotal:</strong><br>
                                    <span class="text-primary fs-4" id="productoSubtotal">S/ 0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Cantidad --}}
                    <div class="col-12">
                        <label for="cantidad" class="form-label fw-semibold">
                            <i class="fas fa-sort-numeric-up text-muted"></i> Cantidad
                        </label>
                        <input type="number" 
                               class="form-control form-control-lg" 
                               id="cantidad" 
                               min="1" 
                               value="1"
                               placeholder="Cantidad">
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i> 
                            Cantidad máxima según stock disponible
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i> Cancelar
                </button>
                <button type="button" class="btn btn-primary" id="btnConfirmarProducto">
                    <i class="fas fa-check me-2"></i> Agregar al Pedido
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ==========================================
    ESTILOS PERSONALIZADOS
    ========================================== --}}
@push('styles')
<style>
    /* Required asterisk */
    .required::after {
        content: " *";
        color: #e74a3b;
    }

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

    /* Resumen Items */
    .resumen-item {
        padding: 10px;
        background: #f8f9fa;
        border-radius: 8px;
    }

    .total-section {
        padding: 15px;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 8px;
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
        transform: scale(1.01);
    }

    /* Form Controls */
    .form-control:focus,
    .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.25rem rgba(255, 107, 53, 0.25);
    }

    .form-select-lg,
    .form-control-lg {
        font-size: 1.05rem;
        padding: 0.75rem 1rem;
    }

    /* Cards */
    .card {
        transition: all 0.3s ease;
    }

    .card:hover {
        box-shadow: 0 8px 16px rgba(0,0,0,0.1) !important;
    }

    /* Sticky Sidebar */
    .sticky-top {
        position: sticky;
        z-index: 1020;
    }

    /* Badges */
    .bg-primary-soft {
        background-color: rgba(255, 107, 53, 0.1);
    }

    /* Buttons */
    .btn-lg {
        padding: 12px 24px;
        font-size: 16px;
        font-weight: 600;
    }

    /* Modal */
    .modal-header {
        border-bottom: 2px solid rgba(255,255,255,0.2);
    }

    .btn-close-white {
        filter: brightness(0) invert(1);
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

        .sticky-top {
            position: relative !important;
        }

        .table {
            font-size: 14px;
        }

        .table thead th,
        .table tbody td {
            padding: 12px 8px;
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
        let productosAgregados = [];
        let productoIndex = {{ $pedido->productos->count() }};

        // Inicializar productos existentes
        @foreach($pedido->productos as $index => $producto)
            productosAgregados.push({
                index: {{ $index }},
                producto_id: {{ $producto->id }},
                nombre: '{{ $producto->nombre }}',
                precio: {{ $producto->precio }},
                cantidad: {{ $producto->pivot->cantidad }},
                stock: {{ $producto->stock + $producto->pivot->cantidad }},
                subtotal: {{ $producto->pivot->cantidad * $producto->precio }},
                pivot_id: {{ $producto->pivot->id }}
            });
        @endforeach

        // ==========================================
        // ABRIR MODAL AGREGAR PRODUCTO
        // ==========================================
        $('#btnAgregarProducto').on('click', function() {
            $('#modalAgregarProducto').modal('show');
            $('#producto_id').val('');
            $('#cantidad').val(1);
            $('#productoInfo').hide();
        });

        // ==========================================
        // CAMBIO DE PRODUCTO EN MODAL
        // ==========================================
        $('#producto_id').on('change', function() {
            const productoId = $(this).val();
            
            if (productoId) {
                const option = $(this).find('option:selected');
                const precio = parseFloat(option.data('precio'));
                const stock = parseInt(option.data('stock'));
                const cantidad = parseInt($('#cantidad').val());

                $('#productoPrecio').text('S/ ' + precio.toFixed(2));
                $('#productoStock').text(stock);
                $('#cantidad').attr('max', stock);
                
                const subtotal = precio * cantidad;
                $('#productoSubtotal').text('S/ ' + subtotal.toFixed(2));
                
                $('#productoInfo').slideDown();
            } else {
                $('#productoInfo').slideUp();
            }
        });

        // ==========================================
        // CAMBIO DE CANTIDAD EN MODAL
        // ==========================================
        $('#cantidad').on('input', function() {
            const productoId = $('#producto_id').val();
            
            if (productoId) {
                const option = $('#producto_id').find('option:selected');
                const precio = parseFloat(option.data('precio'));
                const stock = parseInt(option.data('stock'));
                let cantidad = parseInt($(this).val());

                if (cantidad > stock) {
                    cantidad = stock;
                    $(this).val(stock);
                    Swal.fire({
                        icon: 'warning',
                        title: 'Stock insuficiente',
                        text: `Solo hay ${stock} unidades disponibles`,
                        timer: 2000
                    });
                }

                const subtotal = precio * cantidad;
                $('#productoSubtotal').text('S/ ' + subtotal.toFixed(2));
            }
        });

        // ==========================================
        // CONFIRMAR PRODUCTO
        // ==========================================
        $('#btnConfirmarProducto').on('click', function() {
            const productoId = $('#producto_id').val();
            const cantidad = parseInt($('#cantidad').val());

            if (!productoId) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Por favor selecciona un producto',
                    confirmButtonColor: '#e74a3b'
                });
                return;
            }

            if (!cantidad || cantidad < 1) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Por favor ingresa una cantidad válida',
                    confirmButtonColor: '#e74a3b'
                });
                return;
            }

            // Verificar si el producto ya está agregado
            const productoExistente = productosAgregados.find(p => p.producto_id == productoId);
            
            if (productoExistente) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Producto ya agregado',
                    text: 'Este producto ya está en el pedido. Puedes modificar la cantidad en la tabla.',
                    confirmButtonColor: '#f6c23e'
                });
                return;
            }

            const option = $('#producto_id').find('option:selected');
            const nombre = option.data('nombre');
            const precio = parseFloat(option.data('precio'));
            const stock = parseInt(option.data('stock'));
            const subtotal = precio * cantidad;

            const producto = {
                index: productoIndex++,
                producto_id: productoId,
                nombre: nombre,
                precio: precio,
                cantidad: cantidad,
                stock: stock,
                subtotal: subtotal,
                esNuevo: true
            };

            productosAgregados.push(producto);
            agregarProductoTabla(producto);
            actualizarResumen();

            $('#modalAgregarProducto').modal('hide');

            Swal.fire({
                icon: 'success',
                title: '¡Producto agregado!',
                text: `${nombre} agregado correctamente`,
                timer: 1500,
                showConfirmButton: false
            });
        });

        // ==========================================
        // AGREGAR PRODUCTO A LA TABLA
        // ==========================================
        function agregarProductoTabla(producto) {
            const row = `
                <tr data-index="${producto.index}">
                    <td>
                        <strong>${producto.nombre}</strong>
                        <input type="hidden" name="productos[${producto.index}][producto_id]" value="${producto.producto_id}">
                        ${producto.pivot_id ? `<input type="hidden" name="productos[${producto.index}][pivot_id]" value="${producto.pivot_id}">` : ''}
                    </td>
                    <td class="text-center">
                        <div class="input-group input-group-sm" style="max-width: 120px; margin: 0 auto;">
                            <button class="btn btn-outline-secondary btn-cantidad" type="button" data-action="minus" data-index="${producto.index}">
                                <i class="fas fa-minus"></i>
                            </button>
                            <input type="number" 
                                   class="form-control text-center cantidad-input" 
                                   name="productos[${producto.index}][cantidad]" 
                                   value="${producto.cantidad}"
                                   min="1"
                                   max="${producto.stock}"
                                   data-index="${producto.index}"
                                   data-precio="${producto.precio}"
                                   data-stock="${producto.stock}"
                                   data-cantidad-original="${producto.esNuevo ? 0 : producto.cantidad}">
                            <button class="btn btn-outline-secondary btn-cantidad" type="button" data-action="plus" data-index="${producto.index}">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        <small class="text-muted d-block mt-1">Stock: <span class="stock-disponible" data-index="${producto.index}">${producto.stock}</span></small>
                    </td>
                    <td class="text-end">
                        <strong class="text-success">S/ ${producto.precio.toFixed(2)}</strong>
                        <small class="text-muted d-block">c/u</small>
                    </td>
                    <td class="text-end">
                        <strong class="fs-5 subtotal-producto" data-index="${producto.index}">
                            S/ ${producto.subtotal.toFixed(2)}
                        </strong>
                    </td>
                    <td class="text-center">
                        <button type="button" 
                                class="btn btn-sm btn-outline-danger btn-eliminar-producto"
                                data-index="${producto.index}"
                                data-bs-toggle="tooltip"
                                title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;

            $('#productosTableBody').append(row);
            $('[data-bs-toggle="tooltip"]').tooltip();
        }

        // ==========================================
        // CAMBIAR CANTIDAD
        // ==========================================
        $(document).on('click', '.btn-cantidad', function() {
            const action = $(this).data('action');
            const index = $(this).data('index');
            const input = $(`.cantidad-input[data-index="${index}"]`);
            let cantidad = parseInt(input.val());
            const max = parseInt(input.attr('max'));

            if (action === 'plus' && cantidad < max) {
                cantidad++;
            } else if (action === 'minus' && cantidad > 1) {
                cantidad--;
            } else if (action === 'plus' && cantidad >= max) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Stock máximo',
                    text: `Solo hay ${max} unidades disponibles`,
                    timer: 2000
                });
                return;
            }

            input.val(cantidad);
            actualizarSubtotal(index, cantidad);
        });

        // ==========================================
        // CAMBIO MANUAL DE CANTIDAD
        // ==========================================
        $(document).on('change', '.cantidad-input', function() {
            const index = $(this).data('index');
            let cantidad = parseInt($(this).val());
            const max = parseInt($(this).attr('max'));

            if (cantidad < 1) {
                cantidad = 1;
                $(this).val(1);
            } else if (cantidad > max) {
                cantidad = max;
                $(this).val(max);
                Swal.fire({
                    icon: 'warning',
                    title: 'Stock insuficiente',
                    text: `Solo hay ${max} unidades disponibles`,
                    timer: 2000
                });
            }

            actualizarSubtotal(index, cantidad);
        });

        // ==========================================
        // ACTUALIZAR SUBTOTAL
        // ==========================================
        function actualizarSubtotal(index, cantidad) {
            const producto = productosAgregados.find(p => p.index == index);
            
            if (producto) {
                producto.cantidad = cantidad;
                producto.subtotal = producto.precio * cantidad;
                
                $(`.subtotal-producto[data-index="${index}"]`).text('S/ ' + producto.subtotal.toFixed(2));
                actualizarResumen();
            }
        }

        // ==========================================
        // ELIMINAR PRODUCTO
        // ==========================================
        $(document).on('click', '.btn-eliminar-producto', function() {
            const index = $(this).data('index');
            
            Swal.fire({
                title: '¿Eliminar producto?',
                text: 'Este producto será removido del pedido',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74a3b',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $(`tr[data-index="${index}"]`).fadeOut(300, function() {
                        $(this).remove();
                        productosAgregados = productosAgregados.filter(p => p.index != index);
                        actualizarResumen();
                    });
                }
            });
        });

        // ==========================================
        // ACTUALIZAR RESUMEN
        // ==========================================
        function actualizarResumen() {
            const totalProductos = productosAgregados.length;
            const totalCantidad = productosAgregados.reduce((sum, p) => sum + p.cantidad, 0);
            const totalPagar = productosAgregados.reduce((sum, p) => sum + p.subtotal, 0);

            $('#productCount').text(totalProductos);
            $('#resumenProductos').text(totalProductos);
            $('#resumenCantidad').text(totalCantidad);
            $('#totalPagar').text('S/ ' + totalPagar.toFixed(2));
            $('#totalInput').val(totalPagar.toFixed(2));
        }

        // ==========================================
        // CHARACTER COUNTER
        // ==========================================
        $('#observaciones').on('input', function() {
            const length = $(this).val().length;
            $('#charCount').text(`${length}/500`);
            
            if (length > 450) {
                $('#charCount').addClass('text-danger').removeClass('text-muted');
            } else {
                $('#charCount').addClass('text-muted').removeClass('text-danger');
            }
        });

        // ==========================================
        // VALIDACIÓN DEL FORMULARIO
        // ==========================================
        $('#pedidoForm').on('submit', function(e) {
            if (productosAgregados.length === 0) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Pedido vacío',
                    text: 'Debes tener al menos un producto en el pedido',
                    confirmButtonColor: '#e74a3b'
                });
                return false;
            }

            if (!$('#cliente_id').val()) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Cliente requerido',
                    text: 'Por favor selecciona un cliente',
                    confirmButtonColor: '#e74a3b'
                });
                return false;
            }

            Swal.fire({
                title: 'Guardando cambios...',
                text: 'Por favor espera',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        });

        // ==========================================
        // AUTO-CLOSE ALERTS
        // ==========================================
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 8000);

        // ==========================================
        // TOOLTIPS
        // ==========================================
        $('[data-bs-toggle="tooltip"]').tooltip();
    });
</script>
@endpush

@endsection
