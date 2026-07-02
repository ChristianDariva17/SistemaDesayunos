@extends('layouts.app')

@section('title', 'Productos')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.productos.index') }}">Productos</a></li>
    <li class="breadcrumb-item active">Listado</li>
@endsection

@section('content')
<div class="container-fluid py-4">
    {{-- ========================================== --}}
    {{-- ENCABEZADO Y DESCRIPCIÓN --}}
    {{-- ========================================== --}}
    <div class="row align-items-center mb-4">
        <div class="col-lg-8">
            <h2 class="mb-2">
                <i class="fas fa-box-open text-primary me-2"></i>
                Gestión de Productos
            </h2>
            <p class="text-muted mb-0">
                <i class="fas fa-info-circle me-1"></i>
                Administra tu catálogo de productos, controla inventario y gestiona información de ventas
            </p>
        </div>
        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
            {{-- ========================================== --}}
            {{-- ACCIONES PRINCIPALES --}}
            {{-- ========================================== --}}
            <div class="d-flex gap-2 justify-content-lg-end justify-content-start flex-wrap">
                <a href="{{ route('admin.stock-entries.create') }}" class="btn btn-success btn-lg shadow-sm">
                    <i class="fas fa-dolly me-2"></i>
                    Registrar Entrada
                </a>
                <a href="{{ route('admin.stock-adjustments.create') }}" class="btn btn-warning btn-lg shadow-sm">
                    <i class="fas fa-sliders-h me-2"></i>
                    Ajustar Stock
                </a>
                <a href="{{ route('admin.productos.create') }}" class="btn btn-primary btn-lg shadow-sm">
                    <i class="fas fa-plus-circle me-2"></i>
                    Nuevo Producto
                </a>
            </div>
        </div>
    </div>

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

    {{-- ========================================== --}}
    {{-- TARJETAS DE ESTADÍSTICAS --}}
    {{-- ========================================== --}}
    <div class="row g-4 mb-4">
        {{-- Total Productos --}}
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted text-uppercase mb-1" style="font-size: 0.75rem; font-weight: 600;">
                                Total Productos
                            </p>
                            <h3 class="mb-0 fw-bold">{{ $totalProductos }}</h3>
                            <small class="text-muted">
                                <i class="fas fa-boxes me-1"></i>En catálogo
                            </small>
                        </div>
                        <div class="bg-primary bg-opacity-10 rounded-3 p-3">
                            <i class="fas fa-box-open fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Productos Activos --}}
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted text-uppercase mb-1" style="font-size: 0.75rem; font-weight: 600;">
                                Activos
                            </p>
                            <h3 class="mb-0 fw-bold text-success">{{ $productosActivos }}</h3>
                            <small class="text-muted">
                                <i class="fas fa-check-circle me-1"></i>Disponibles
                            </small>
                        </div>
                        <div class="bg-success bg-opacity-10 rounded-3 p-3">
                            <i class="fas fa-toggle-on fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Productos Nuevos Este Mes --}}
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted text-uppercase mb-1" style="font-size: 0.75rem; font-weight: 600;">
                                Nuevos Este Mes
                            </p>
                            <h3 class="mb-0 fw-bold text-info">{{ $productosNuevos }}</h3>
                            <small class="text-muted">
                                <i class="fas fa-calendar-plus me-1"></i>{{ now()->format('M Y') }}
                            </small>
                        </div>
                        <div class="bg-info bg-opacity-10 rounded-3 p-3">
                            <i class="fas fa-sparkles fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Stock Bajo --}}
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted text-uppercase mb-1" style="font-size: 0.75rem; font-weight: 600;">
                                Stock Bajo
                            </p>
                            <h3 class="mb-0 fw-bold text-warning">{{ $stockBajo }}</h3>
                            <small class="text-muted">
                                <i class="fas fa-exclamation-triangle me-1"></i>≤ 10 unidades
                            </small>
                        </div>
                        <div class="bg-warning bg-opacity-10 rounded-3 p-3">
                            <i class="fas fa-box-tissue fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- TARJETA PRINCIPAL CON FILTROS Y TABLA --}}
    {{-- ========================================== --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <div class="row align-items-center g-3">
                {{-- BARRA DE BÚSQUEDA --}}
                <div class="col-lg-4">
                    <form action="{{ route('admin.productos.index') }}" method="GET" id="searchForm">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input 
                                type="text" 
                                name="search" 
                                class="form-control border-start-0 ps-0" 
                                placeholder="Buscar productos..." 
                                value="{{ request('search') }}"
                            >
                            @if(request()->hasAny(['search', 'categoria', 'estado']))
                                <a href="{{ route('admin.productos.index') }}" class="btn btn-outline-secondary" title="Limpiar filtros">
                                    <i class="fas fa-times"></i>
                                </a>
                            @endif
                        </div>
                        <input type="hidden" name="categoria" value="{{ request('categoria') }}">
                        <input type="hidden" name="estado" value="{{ request('estado') }}">
                    </form>
                </div>

                {{-- FILTROS --}}
                <div class="col-lg-5">
                    <div class="row g-2">
                        {{-- Filtro por Categoría --}}
                        <div class="col-md-6">
                            <select name="categoria" class="form-select" id="filterCategoria">
                                <option value="">Todas las categorías</option>
                                <option value="comidas" {{ request('categoria') == 'comidas' ? 'selected' : '' }}>Comidas</option>
                                <option value="bebidas" {{ request('categoria') == 'bebidas' ? 'selected' : '' }}>Bebidas</option>
                                <option value="postres" {{ request('categoria') == 'postres' ? 'selected' : '' }}>Postres</option>
                                <option value="snacks" {{ request('categoria') == 'snacks' ? 'selected' : '' }}>Snacks</option>
                            </select>
                        </div>

                        {{-- Filtro por Estado --}}
                        <div class="col-md-6">
                            <select name="estado" class="form-select" id="filterEstado">
                                <option value="">Todos los estados</option>
                                <option value="activo" {{ request('estado') == 'activo' ? 'selected' : '' }}>Activo</option>
                                <option value="inactivo" {{ request('estado') == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- ✅ BOTÓN EXPORTAR CSV --}}
                <div class="col-lg-3 text-lg-end">
                    <a href="{{ route('admin.productos.exportar', request()->all()) }}" 
                       class="btn btn-success btn-sm shadow-sm"
                       title="Exportar a CSV">
                        <i class="fas fa-file-excel me-2"></i>
                        Exportar CSV
                    </a>
                </div>
            </div>
        </div>

        {{-- ========================================== --}}
        {{-- TABLA DE PRODUCTOS --}}
        {{-- ========================================== --}}
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="text-center" width="80">Imagen</th>
                            <th>Producto</th>
                            <th class="text-center" width="120">Categoría</th>
                            <th class="text-center" width="100">Stock</th>
                            <th class="text-center" width="120">Precio</th>
                            <th class="text-center" width="100">Estado</th>
                            <th class="text-center" width="200">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($productos as $producto)
                            <tr>
                                {{-- IMAGEN --}}
                                <td class="text-center">
                                    @if($producto->imagen)
                                        <img 
                                            src="{{ asset('storage/' . $producto->imagen) }}" 
                                            alt="{{ $producto->nombre }}" 
                                            class="rounded shadow-sm"
                                            width="50"
                                            height="50"
                                            style="object-fit: cover; cursor: pointer;"
                                            data-bs-toggle="modal"
                                            data-bs-target="#imageModal{{ $producto->id }}"
                                        >
                                    @else
                                        <div class="bg-secondary bg-opacity-10 rounded d-inline-flex align-items-center justify-content-center" 
                                             style="width: 50px; height: 50px;">
                                            <i class="fas fa-image text-secondary"></i>
                                        </div>
                                    @endif
                                </td>

                                {{-- PRODUCTO --}}
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold text-dark">{{ $producto->nombre }}</span>
                                        @if($producto->descripcion)
                                            <small class="text-muted text-truncate" style="max-width: 300px;">
                                                {{ $producto->descripcion }}
                                            </small>
                                        @else
                                            <small class="text-muted fst-italic">Sin descripción</small>
                                        @endif
                                        @if($producto->sku)
                                            <small class="text-muted">
                                                <i class="fas fa-barcode me-1"></i>SKU: {{ $producto->sku }}
                                            </small>
                                        @endif
                                    </div>
                                </td>

                                {{-- CATEGORÍA --}}
                                <td class="text-center">
                                    @if($producto->categoria)
                                        <span class="badge bg-info bg-opacity-10 text-info border border-info">
                                            {{ ucfirst($producto->categoria) }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary">N/A</span>
                                    @endif
                                </td>

                                {{-- STOCK --}}
                                <td class="text-center">
                                    @if($producto->stock == 0)
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger">
                                            <i class="fas fa-times-circle me-1"></i>
                                            Sin stock
                                        </span>
                                    @elseif($producto->stock <= 10)
                                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning">
                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                            {{ $producto->stock }} und.
                                        </span>
                                    @else
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success">
                                            <i class="fas fa-check-circle me-1"></i>
                                            {{ $producto->stock }} und.
                                        </span>
                                    @endif
                                </td>

                                {{-- PRECIO --}}
                                <td class="text-center">
                                    <span class="fw-bold text-dark">S/ {{ number_format($producto->precio, 2) }}</span>
                                </td>

                                {{-- ✅ ESTADO CON TOGGLE --}}
                                <td class="text-center">
                                    <div class="form-check form-switch d-flex justify-content-center">
                                        <input 
                                            class="form-check-input toggle-estado" 
                                            type="checkbox" 
                                            role="switch" 
                                            data-producto-id="{{ $producto->id }}"
                                            {{ $producto->estado == 'activo' ? 'checked' : '' }}
                                            title="Cambiar estado"
                                            style="cursor: pointer; width: 3rem; height: 1.5rem;"
                                        >
                                    </div>
                                </td>

                                {{-- ACCIONES --}}
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        {{-- Ver --}}
                                        <a href="{{ route('admin.productos.show', $producto) }}" 
                                           class="btn btn-sm btn-outline-info" 
                                           title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        {{-- Editar --}}
                                        <a href="{{ route('admin.productos.edit', $producto) }}" 
                                           class="btn btn-sm btn-outline-primary" 
                                           title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        {{-- ✅ Actualizar Stock --}}
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-warning" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#stockModal{{ $producto->id }}"
                                                title="Actualizar stock">
                                            <i class="fas fa-boxes"></i>
                                        </button>

                                        {{-- ✅ Duplicar --}}
                                        <form action="{{ route('admin.productos.duplicar', $producto) }}" 
                                              method="POST" 
                                              class="d-inline"
                                              onsubmit="return confirm('¿Duplicar este producto?')">
                                            @csrf
                                            <button type="submit" 
                                                    class="btn btn-sm btn-outline-secondary" 
                                                    title="Duplicar producto">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </form>

                                        {{-- Eliminar --}}
                                        <form action="{{ route('admin.productos.destroy', $producto) }}" 
                                              method="POST" 
                                              class="d-inline"
                                              onsubmit="return confirm('¿Estás seguro de eliminar este producto?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-outline-danger" 
                                                    title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            {{-- ✅ MODAL ACTUALIZAR STOCK --}}
                            <div class="modal fade" id="stockModal{{ $producto->id }}" tabindex="-1" aria-labelledby="stockModalLabel{{ $producto->id }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('admin.productos.actualizar-stock', $producto) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <div class="modal-header bg-warning bg-opacity-10">
                                                <h5 class="modal-title" id="stockModalLabel{{ $producto->id }}">
                                                    <i class="fas fa-boxes me-2"></i>
                                                    Actualizar Stock - {{ $producto->nombre }}
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="alert alert-info">
                                                    <i class="fas fa-info-circle me-2"></i>
                                                    Stock actual: <strong>{{ $producto->stock }} unidades</strong>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Tipo de Operación</label>
                                                    <select name="tipo" class="form-select" required>
                                                        <option value="incrementar">➕ Incrementar (Agregar stock)</option>
                                                        <option value="decrementar">➖ Decrementar (Restar stock)</option>
                                                        <option value="establecer">🔢 Establecer (Stock exacto)</option>
                                                    </select>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Cantidad</label>
                                                    <input type="number" 
                                                           name="cantidad" 
                                                           class="form-control" 
                                                           min="0" 
                                                           required 
                                                           placeholder="Ingrese cantidad">
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Motivo (Opcional)</label>
                                                    <textarea name="motivo" 
                                                              class="form-control" 
                                                              rows="2" 
                                                              placeholder="Ej: Compra, Ajuste de inventario, etc."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                <button type="submit" class="btn btn-warning">
                                                    <i class="fas fa-save me-2"></i>Actualizar Stock
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            {{-- MODAL IMAGEN --}}
                            @if($producto->imagen)
                                <div class="modal fade" id="imageModal{{ $producto->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">{{ $producto->nombre }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body text-center">
                                                <img src="{{ asset('storage/' . $producto->imagen) }}" 
                                                     alt="{{ $producto->nombre }}" 
                                                     class="img-fluid rounded">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3 d-block text-secondary opacity-50"></i>
                                        @if(request()->hasAny(['search', 'categoria', 'estado']))
                                            <h5 class="fw-bold">No hay productos que coincidan</h5>
                                            <p class="mb-3">Intenta con otros criterios de búsqueda</p>
                                            <a href="{{ route('admin.productos.index') }}" class="btn btn-outline-primary">
                                                <i class="fas fa-undo me-2"></i>Limpiar filtros
                                            </a>
                                        @else
                                            <h5 class="fw-bold">No hay productos registrados</h5>
                                            <p class="mb-3">Comienza agregando tu primer producto</p>
                                            <a href="{{ route('admin.productos.create') }}" class="btn btn-primary">
                                                <i class="fas fa-plus-circle me-2"></i>Agregar Producto
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ========================================== --}}
        {{-- PAGINACIÓN --}}
        {{-- ========================================== --}}
        @if($productos->hasPages())
            <div class="card-footer bg-white border-top">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Mostrando {{ $productos->firstItem() }} a {{ $productos->lastItem() }} de {{ $productos->total() }} productos
                    </div>
                    <div>
                        {{ $productos->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

{{-- ========================================== --}}
{{-- ESTILOS PERSONALIZADOS --}}
{{-- ========================================== --}}
<style>
    /* Efecto hover en tarjetas */
    .hover-lift {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .hover-lift:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.15) !important;
    }

    /* Animación de fade-in */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .card, .alert {
        animation: fadeIn 0.4s ease;
    }

    /* Estilo para badges */
    .badge {
        font-weight: 500;
        padding: 0.5rem 0.75rem;
        font-size: 0.75rem;
    }

    /* Estilo para botones de acción */
    .btn-group .btn {
        padding: 0.375rem 0.75rem;
    }

    /* Efecto hover en filas de tabla */
    .table-hover tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
        transition: background-color 0.2s ease;
    }

    /* Switch toggle personalizado */
    .form-check-input.toggle-estado:checked {
        background-color: #28a745;
        border-color: #28a745;
    }
    .form-check-input.toggle-estado {
        background-color: #dc3545;
        border-color: #dc3545;
    }
</style>

{{-- ========================================== --}}
{{-- JAVASCRIPT PARA TOGGLE ESTADO Y FILTROS --}}
{{-- ========================================== --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // ✅ TOGGLE ESTADO AJAX
    document.querySelectorAll('.toggle-estado').forEach(toggle => {
        toggle.addEventListener('change', function() {
            const productoId = this.dataset.productoId;
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            fetch(`/productos/${productoId}/toggle-estado`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Mostrar notificación de éxito
                    const alert = document.createElement('div');
                    alert.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-3';
                    alert.style.zIndex = '9999';
                    alert.innerHTML = `
                        <i class="fas fa-check-circle me-2"></i>
                        ${data.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                    document.body.appendChild(alert);
                    
                    // Auto-eliminar después de 3 segundos
                    setTimeout(() => alert.remove(), 3000);
                } else {
                    // Revertir el toggle si hay error
                    this.checked = !this.checked;
                    alert('Error al cambiar el estado');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.checked = !this.checked;
                alert('Error de conexión');
            });
        });
    });

    // ✅ FILTROS AUTOMÁTICOS
    document.getElementById('filterCategoria').addEventListener('change', function() {
        aplicarFiltros();
    });

    document.getElementById('filterEstado').addEventListener('change', function() {
        aplicarFiltros();
    });

    function aplicarFiltros() {
        const form = document.getElementById('searchForm');
        const searchValue = form.querySelector('input[name="search"]').value;
        const categoriaValue = document.getElementById('filterCategoria').value;
        const estadoValue = document.getElementById('filterEstado').value;

        // Construir URL con parámetros
        const params = new URLSearchParams();
        if (searchValue) params.append('search', searchValue);
        if (categoriaValue) params.append('categoria', categoriaValue);
        if (estadoValue) params.append('estado', estadoValue);

        // Redirigir con filtros
        window.location.href = `{{ route('admin.productos.index') }}?${params.toString()}`;
    }

    // ✅ AUTO-SUBMIT EN BÚSQUEDA
    document.querySelector('input[name="search"]').addEventListener('keyup', debounce(function() {
        document.getElementById('searchForm').submit();
    }, 500));

    // Función debounce para evitar múltiples requests
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
});
</script>
@endpush
@endsection
