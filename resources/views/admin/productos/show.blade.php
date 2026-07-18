@extends('layouts.app')

@section('title', 'Detalles del Producto')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.productos.index') }}">Productos</a></li>
    <li class="breadcrumb-item active">{{ $producto->nombre }}</li>
@endsection

@section('content')
<div class="container-fluid py-4">
    {{-- ========================================== --}}
    {{-- ALERTAS DE SESIÓN --}}
    {{-- ========================================== --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <strong>¡Éxito!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>¡Error!</strong> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        {{-- ========================================== --}}
        {{-- COLUMNA IZQUIERDA: IMAGEN Y DETALLES --}}
        {{-- ========================================== --}}
        <div class="col-lg-8">
            {{-- SECCIÓN: ENCABEZADO CON ACCIONES --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center mb-2">
                                <h2 class="mb-0 me-3">{{ $producto->nombre }}</h2>
                                {{-- Badge de Estado --}}
                                @if($producto->estado == 'activo')
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success px-3 py-2">
                                        <i class="fas fa-check-circle me-1"></i>
                                        Activo
                                    </span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-3 py-2">
                                        <i class="fas fa-times-circle me-1"></i>
                                        Inactivo
                                    </span>
                                @endif
                            </div>
                            <p class="text-muted mb-0">
                                <i class="fas fa-hashtag me-1"></i>
                                ID: {{ $producto->id }}
                                @if($producto->sku)
                                    <span class="mx-2">•</span>
                                    <i class="fas fa-tag me-1"></i>
                                    SKU: {{ $producto->sku }}
                                @endif
                                @if($producto->categoria)
                                    <span class="mx-2">•</span>
                                    <i class="fas fa-folder me-1"></i>
                                    {{ ucfirst($producto->categoria) }}
                                @endif
                            </p>
                        </div>

                        {{-- Botones de Acción Principales --}}
                        <div class="btn-group" role="group">
                            <a href="{{ route('admin.productos.edit', $producto) }}" class="btn btn-primary">
                                <i class="fas fa-edit me-2"></i>
                                Editar
                            </a>
                            <button type="button" class="btn btn-outline-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="visually-hidden">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                <li>
                                    <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#modalActualizarStock">
                                        <i class="fas fa-boxes text-warning me-2"></i>
                                        Actualizar Stock
                                    </button>
                                </li>
                                <li>
                                    <button class="dropdown-item" id="btnToggleEstado">
                                        <i class="fas fa-toggle-on text-info me-2"></i>
                                        Cambiar Estado
                                    </button>
                                </li>
                                <li>
                                    <form action="{{ route('admin.productos.duplicar', $producto) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="fas fa-copy text-secondary me-2"></i>
                                            Duplicar Producto
                                        </button>
                                    </form>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <button class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#modalEliminar">
                                        <i class="fas fa-trash me-2"></i>
                                        Eliminar Producto
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SECCIÓN: IMAGEN DEL PRODUCTO --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body text-center">
                    @php
                        $imageUrl = $producto->getImagenUrl();
                    @endphp
                    @if($imageUrl)
                        <img 
                            src="{{ $imageUrl }}"
                            alt="{{ $producto->nombre }}" 
                            class="img-fluid rounded shadow"
                            style="max-height: 500px; object-fit: contain; cursor: pointer;"
                            data-bs-toggle="modal"
                            data-bs-target="#modalImagen"
                        >
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Haz clic en la imagen para ampliarla
                            </small>
                        </div>
                    @else
                        <div class="bg-secondary bg-opacity-10 rounded p-5">
                            <i class="fas fa-image fa-5x text-secondary mb-3"></i>
                            <p class="text-muted mb-0">Este producto no tiene imagen</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- SECCIÓN: DESCRIPCIÓN --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="fas fa-align-left text-primary me-2"></i>
                        Descripción
                    </h5>
                </div>
                <div class="card-body">
                    @if($producto->descripcion)
                        <p class="mb-0" style="white-space: pre-wrap;">{{ $producto->descripcion }}</p>
                    @else
                        <p class="text-muted fst-italic mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            Este producto no tiene descripción
                        </p>
                    @endif
                </div>
            </div>

            {{-- SECCIÓN: ÚLTIMOS PEDIDOS --}}
            @if($producto->pedidos_count > 0)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-shopping-cart text-primary me-2"></i>
                                Últimos Pedidos
                            </h5>
                            <span class="badge bg-primary">{{ $producto->pedidos_count }} total</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>ID Pedido</th>
                                        <th>Cliente</th>
                                        <th>Fecha</th>
                                        <th class="text-end">Total</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($producto->pedidos as $pedido)
                                        <tr>
                                            <td>
                                                <strong>#{{ $pedido->id }}</strong>
                                            </td>
                                            <td>
                                                @if($pedido->cliente)
                                                    <i class="fas fa-user text-muted me-1"></i>
                                                    {{ $pedido->cliente->nombre }}
                                                @else
                                                    <span class="text-muted">Sin cliente</span>
                                                @endif
                                            </td>
                                            <td>
                                                <small>{{ $pedido->created_at->format('d/m/Y H:i') }}</small>
                                            </td>
                                            <td class="text-end">
                                                <strong>S/ {{ number_format($pedido->total, 2) }}</strong>
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('admin.pedidos.show', $pedido) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">
                                                No hay pedidos registrados
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if($producto->pedidos_count > 5)
                            <div class="card-footer bg-light text-center">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Mostrando los últimos 5 pedidos de {{ $producto->pedidos_count }} totales
                                </small>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- ========================================== --}}
        {{-- COLUMNA DERECHA: INFORMACIÓN Y ESTADÍSTICAS --}}
        {{-- ========================================== --}}
        <div class="col-lg-4">
            {{-- SECCIÓN: PRECIO Y STOCK --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-gradient text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h5 class="mb-0">
                        <i class="fas fa-dollar-sign me-2"></i>
                        Precio e Inventario
                    </h5>
                </div>
                <div class="card-body">
                    {{-- Precio --}}
                    <div class="mb-4">
                        <label class="text-muted small text-uppercase fw-bold mb-1">Precio de Venta</label>
                        <h2 class="mb-0 text-success">
                            S/ {{ number_format($producto->precio, 2) }}
                        </h2>
                    </div>

                    <hr>

                    {{-- Stock --}}
                    <div class="mb-3">
                        <label class="text-muted small text-uppercase fw-bold mb-1">Stock Disponible</label>
                        <div class="d-flex align-items-center">
                            <h3 class="mb-0 me-2">{{ $producto->stock }}</h3>
                            <span class="text-muted">unidades</span>
                        </div>
                        {{-- Indicador visual de stock --}}
                        <div class="progress mt-2" style="height: 8px;">
                            @php
                                $stockMax = 100;
                                $stockPorcentaje = min(($producto->stock / $stockMax) * 100, 100);
                                $colorClass = $producto->stock == 0 ? 'bg-danger' : ($producto->stock <= 10 ? 'bg-warning' : 'bg-success');
                            @endphp
                            <div class="progress-bar {{ $colorClass }}" role="progressbar" style="width: {{ $stockPorcentaje }}%"></div>
                        </div>
                        <small class="text-muted mt-1 d-block">
                            @if($producto->stock == 0)
                                <i class="fas fa-exclamation-circle text-danger me-1"></i>
                                Sin stock
                            @elseif($producto->stock <= 10)
                                <i class="fas fa-exclamation-triangle text-warning me-1"></i>
                                Stock bajo
                            @else
                                <i class="fas fa-check-circle text-success me-1"></i>
                                Stock disponible
                            @endif
                        </small>
                    </div>

                    <hr>

                    {{-- Valor en Inventario --}}
                    <div class="row text-center">
                        <div class="col-12">
                            <label class="text-muted small text-uppercase fw-bold mb-1">Valor en Inventario</label>
                            <h4 class="mb-0 text-primary">
                                S/ {{ number_format($producto->precio * $producto->stock, 2) }}
                            </h4>
                            <small class="text-muted">{{ $producto->stock }} × S/ {{ number_format($producto->precio, 2) }}</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SECCIÓN: ESTADÍSTICAS DE VENTAS --}}
            @if($producto->pedidos_count > 0)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-line text-success me-2"></i>
                            Estadísticas de Ventas
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center g-3">
                            {{-- Total Vendido --}}
                            <div class="col-12">
                                <div class="p-3 bg-success bg-opacity-10 rounded">
                                    <label class="text-muted small text-uppercase fw-bold mb-1">Total Vendido</label>
                                    <h3 class="mb-0 text-success">
                                        S/ {{ number_format($producto->total_vendido ?? 0, 2) }}
                                    </h3>
                                </div>
                            </div>

                            {{-- Pedidos --}}
                            <div class="col-6">
                                <div class="p-3 bg-primary bg-opacity-10 rounded">
                                    <label class="text-muted small text-uppercase fw-bold mb-1">Pedidos</label>
                                    <h4 class="mb-0 text-primary">{{ $producto->pedidos_count }}</h4>
                                </div>
                            </div>

                            {{-- Promedio --}}
                            <div class="col-6">
                                <div class="p-3 bg-info bg-opacity-10 rounded">
                                    <label class="text-muted small text-uppercase fw-bold mb-1">Promedio</label>
                                    <h4 class="mb-0 text-info">
                                        S/ {{ $producto->pedidos_count > 0 ? number_format(($producto->total_vendido ?? 0) / $producto->pedidos_count, 2) : '0.00' }}
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="card border-0 shadow-sm mb-4 bg-light">
                    <div class="card-body text-center py-4">
                        <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                        <h6 class="text-muted mb-2">Sin Ventas Registradas</h6>
                        <p class="text-muted small mb-0">
                            Este producto aún no ha sido vendido
                        </p>
                    </div>
                </div>
            @endif

            {{-- SECCIÓN: CÓDIGOS DE IDENTIFICACIÓN --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="fas fa-barcode text-info me-2"></i>
                        Códigos de Identificación
                    </h5>
                </div>
                <div class="card-body">
                    {{-- SKU --}}
                    <div class="mb-3">
                        <label class="text-muted small text-uppercase fw-bold mb-1">SKU</label>
                        @if($producto->sku)
                            <div class="input-group">
                                <input type="text" class="form-control" value="{{ $producto->sku }}" readonly>
                                <button class="btn btn-outline-secondary" onclick="copiarTexto('{{ $producto->sku }}')" title="Copiar">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        @else
                            <p class="text-muted fst-italic mb-0">Sin SKU asignado</p>
                        @endif
                    </div>

                    {{-- Código de Barras --}}
                    <div class="mb-0">
                        <label class="text-muted small text-uppercase fw-bold mb-1">Código de Barras</label>
                        @if($producto->codigo_barras)
                            <div class="input-group">
                                <input type="text" class="form-control" value="{{ $producto->codigo_barras }}" readonly>
                                <button class="btn btn-outline-secondary" onclick="copiarTexto('{{ $producto->codigo_barras }}')" title="Copiar">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        @else
                            <p class="text-muted fst-italic mb-0">Sin código de barras</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- SECCIÓN: INFORMACIÓN DE FECHAS --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="fas fa-clock text-secondary me-2"></i>
                        Información de Fechas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-start mb-3">
                        <div class="bg-primary bg-opacity-10 rounded p-2 me-3">
                            <i class="fas fa-calendar-plus text-primary"></i>
                        </div>
                        <div class="flex-grow-1">
                            <small class="text-muted d-block">Creado</small>
                            <strong>{{ $producto->created_at->format('d/m/Y H:i') }}</strong>
                            <br>
                            <small class="text-muted">{{ $producto->created_at->diffForHumans() }}</small>
                        </div>
                    </div>

                    <div class="d-flex align-items-start mb-3">
                        <div class="bg-warning bg-opacity-10 rounded p-2 me-3">
                            <i class="fas fa-edit text-warning"></i>
                        </div>
                        <div class="flex-grow-1">
                            <small class="text-muted d-block">Última Actualización</small>
                            <strong>{{ $producto->updated_at->format('d/m/Y H:i') }}</strong>
                            <br>
                            <small class="text-muted">{{ $producto->updated_at->diffForHumans() }}</small>
                        </div>
                    </div>

                    <div class="d-flex align-items-start">
                        <div class="bg-success bg-opacity-10 rounded p-2 me-3">
                            <i class="fas fa-eye text-success"></i>
                        </div>
                        <div class="flex-grow-1">
                            <small class="text-muted d-block">Visualizando Ahora</small>
                            <strong>{{ now()->format('d/m/Y H:i') }}</strong>
                            <br>
                            <small class="text-muted">En este momento</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SECCIÓN: ACCIONES RÁPIDAS --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt text-warning me-2"></i>
                        Acciones Rápidas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.productos.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>
                            Volver al Listado
                        </a>
                        <a href="{{ route('admin.productos.create') }}" class="btn btn-outline-success">
                            <i class="fas fa-plus-circle me-2"></i>
                            Crear Nuevo Producto
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ========================================== --}}
{{-- MODAL: IMAGEN AMPLIADA --}}
{{-- ========================================== --}}
@if($imageUrl)
    <div class="modal fade" id="modalImagen" tabindex="-1" aria-labelledby="modalImagenLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalImagenLabel">{{ $producto->nombre }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-0">
                    <img 
                        src="{{ $imageUrl }}"
                        alt="{{ $producto->nombre }}" 
                        class="img-fluid"
                        style="max-height: 80vh; object-fit: contain;"
                    >
                </div>
            </div>
        </div>
    </div>
@endif

{{-- ========================================== --}}
{{-- MODAL: ACTUALIZAR STOCK --}}
{{-- ========================================== --}}
<div class="modal fade" id="modalActualizarStock" tabindex="-1" aria-labelledby="modalActualizarStockLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('admin.productos.actualizar-stock', $producto) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-header bg-warning bg-opacity-10">
                    <h5 class="modal-title" id="modalActualizarStockLabel">
                        <i class="fas fa-boxes text-warning me-2"></i>
                        Actualizar Stock
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Stock actual: <strong>{{ $producto->stock }} unidades</strong>
                    </div>

                    {{-- Tipo de Operación --}}
                    <div class="mb-3">
                        <label for="tipo" class="form-label fw-bold">Tipo de Operación <span class="text-danger">*</span></label>
                        <select name="tipo" id="tipo" class="form-select" required>
                            <option value="incrementar">➕ Incrementar (sumar)</option>
                            <option value="decrementar">➖ Decrementar (restar)</option>
                            <option value="establecer">🔢 Establecer (valor exacto)</option>
                        </select>
                    </div>

                    {{-- Cantidad --}}
                    <div class="mb-3">
                        <label for="cantidad" class="form-label fw-bold">Cantidad <span class="text-danger">*</span></label>
                        <input 
                            type="number" 
                            name="cantidad" 
                            id="cantidad" 
                            class="form-control form-control-lg" 
                            min="0" 
                            required
                            placeholder="Ingrese la cantidad"
                        >
                    </div>

                    {{-- Motivo --}}
                    <div class="mb-3">
                        <label for="motivo" class="form-label fw-bold">Motivo (opcional)</label>
                        <textarea 
                            name="motivo" 
                            id="motivo" 
                            class="form-control" 
                            rows="2"
                            placeholder="Ej: Inventario físico, ajuste, devolución, etc."
                        ></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save me-2"></i>
                        Actualizar Stock
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ========================================== --}}
{{-- MODAL: CONFIRMAR ELIMINACIÓN --}}
{{-- ========================================== --}}
<div class="modal fade" id="modalEliminar" tabindex="-1" aria-labelledby="modalEliminarLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalEliminarLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Confirmar Eliminación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="fas fa-trash-alt fa-4x text-danger mb-3"></i>
                    <h5>¿Estás seguro de eliminar este producto?</h5>
                    <p class="text-muted mb-2">
                        <strong>{{ $producto->nombre }}</strong>
                    </p>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Esta acción no se puede deshacer
                    </div>
                    @if($producto->pedidos_count > 0)
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <strong>Atención:</strong> Este producto tiene {{ $producto->pedidos_count }} pedido(s) asociado(s).
                            <br>
                            <small>Es posible que no se pueda eliminar.</small>
                        </div>
                    @endif
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>
                    Cancelar
                </button>
                <form action="{{ route('admin.productos.destroy', $producto) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i>
                        Sí, Eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ========================================== --}}
{{-- ESTILOS PERSONALIZADOS --}}
{{-- ========================================== --}}
<style>
    /* Hover effect para imagen */
    [data-bs-target="#modalImagen"] {
        transition: transform 0.3s ease;
    }
    [data-bs-target="#modalImagen"]:hover {
        transform: scale(1.02);
    }

    /* Animaciones de fade-in */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .card {
        animation: fadeIn 0.4s ease;
    }

    /* Gradiente para header */
    .bg-gradient {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
</style>

{{-- ========================================== --}}
{{-- JAVASCRIPT PARA FUNCIONALIDADES --}}
{{-- ========================================== --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // ==========================================
    // TOGGLE ESTADO CON AJAX
    // ==========================================
    document.getElementById('btnToggleEstado')?.addEventListener('click', function() {
        if (confirm('¿Estás seguro de cambiar el estado del producto?')) {
            fetch('{{ route("admin.productos.toggle-estado", $producto) }}', {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Mostrar notificación
                    mostrarNotificacion('success', data.message);
                    
                    // Recargar página después de 1 segundo
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    mostrarNotificacion('error', data.message || 'Error al cambiar el estado');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarNotificacion('error', 'Error al cambiar el estado del producto');
            });
        }
    });

    // ==========================================
    // FUNCIÓN: COPIAR TEXTO AL PORTAPAPELES
    // ==========================================
    window.copiarTexto = function(texto) {
        navigator.clipboard.writeText(texto).then(function() {
            mostrarNotificacion('success', `Copiado: ${texto}`);
        }).catch(function(err) {
            console.error('Error al copiar:', err);
            mostrarNotificacion('error', 'No se pudo copiar al portapapeles');
        });
    };

    // ==========================================
    // FUNCIÓN: MOSTRAR NOTIFICACIONES
    // ==========================================
    function mostrarNotificacion(tipo, mensaje) {
        const iconos = {
            'success': 'fa-check-circle',
            'error': 'fa-exclamation-circle',
            'warning': 'fa-exclamation-triangle',
            'info': 'fa-info-circle'
        };

        const colores = {
            'success': 'alert-success',
            'error': 'alert-danger',
            'warning': 'alert-warning',
            'info': 'alert-info'
        };

        const toast = document.createElement('div');
        toast.className = `alert ${colores[tipo]} alert-dismissible fade show position-fixed top-0 end-0 m-3 shadow-lg`;
        toast.style.zIndex = '9999';
        toast.style.minWidth = '300px';
        toast.innerHTML = `
            <i class="fas ${iconos[tipo]} me-2"></i>
            ${mensaje}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.remove();
        }, 5000);
    }

    // ==========================================
    // TOOLTIPS DE BOOTSTRAP
    // ==========================================
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endpush
@endsection
