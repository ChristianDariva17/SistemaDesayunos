@extends('layouts.app')

@section('title', 'Gestión de Clientes - Sistema Dariva')

@section('content')
<div class="container-fluid py-4">

    {{-- ==========================================
         ENCABEZADO Y DESCRIPCIÓN
        ========================================== --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">
                <i class="fas fa-users text-primary me-2"></i>
                Gestión de Clientes
            </h1>
            <p class="text-muted mb-0">
                <i class="fas fa-info-circle me-1"></i>
                Administra la información de todos tus clientes
            </p>
        </div>
        <div class="d-flex gap-2">
            {{-- Botón Exportar --}}
            <a href="{{ route('admin.clientes.exportar', request()->query()) }}" 
               class="btn btn-outline-success"
               title="Exportar clientes a CSV">
                <i class="fas fa-file-export me-2"></i>
                Exportar CSV
            </a>

            {{-- Botón Crear Cliente --}}
            <a href="{{ route('admin.clientes.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i>
                Crear Cliente
            </a>
        </div>
    </div>

    {{-- ==========================================
         ALERTAS DE SESIÓN (SUCCESS/ERROR)
        ========================================== --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- ==========================================
         TARJETAS DE ESTADÍSTICAS
        ========================================== --}}
    <div class="row g-4 mb-4">
        {{-- Total Clientes --}}
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 text-uppercase small fw-bold">
                                <i class="fas fa-users me-1"></i>
                                Total Clientes
                            </p>
                            <h3 class="mb-0 fw-bold text-primary">{{ $totalClientes }}</h3>
                            <small class="text-muted">En el sistema</small>
                        </div>
                        <div class="stat-icon bg-primary">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Clientes Activos --}}
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 text-uppercase small fw-bold">
                                <i class="fas fa-user-check me-1"></i>
                                Activos
                            </p>
                            <h3 class="mb-0 fw-bold text-success">{{ $clientesActivos }}</h3>
                            <small class="text-muted">
                                @if($totalClientes > 0)
                                    {{ round(($clientesActivos / $totalClientes) * 100, 1) }}% del total
                                @else
                                    0% del total
                                @endif
                            </small>
                        </div>
                        <div class="stat-icon bg-success">
                            <i class="fas fa-user-check fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Clientes Inactivos --}}
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 text-uppercase small fw-bold">
                                <i class="fas fa-user-slash me-1"></i>
                                Inactivos
                            </p>
                            <h3 class="mb-0 fw-bold text-secondary">{{ $clientesInactivos }}</h3>
                            <small class="text-muted">
                                @if($totalClientes > 0)
                                    {{ round(($clientesInactivos / $totalClientes) * 100, 1) }}% del total
                                @else
                                    0% del total
                                @endif
                            </small>
                        </div>
                        <div class="stat-icon bg-secondary">
                            <i class="fas fa-user-slash fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Nuevos Este Mes --}}
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 text-uppercase small fw-bold">
                                <i class="fas fa-user-plus me-1"></i>
                                Nuevos {{ now()->format('M Y') }}
                            </p>
                            <h3 class="mb-0 fw-bold text-info">{{ $nuevosEsteMes }}</h3>
                            <small class="text-muted">Este mes</small>
                        </div>
                        <div class="stat-icon bg-info">
                            <i class="fas fa-user-plus fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ==========================================
         PANEL DE FILTROS Y BÚSQUEDA
        ========================================== --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.clientes.index') }}" id="filterForm">
                <div class="row g-3 align-items-end">
                    {{-- Buscador --}}
                    <div class="col-12 col-md-4">
                        <label for="search" class="form-label fw-bold">
                            <i class="fas fa-search me-1"></i>
                            Buscar Cliente
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" 
                                   class="form-control" 
                                   id="search" 
                                   name="search" 
                                   value="{{ request('search') }}"
                                   placeholder="Nombre, email, teléfono...">
                            @if(request('search'))
                                <a href="{{ route('admin.clientes.index') }}" class="btn btn-outline-secondary" title="Limpiar búsqueda">
                                    <i class="fas fa-times"></i>
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- Filtro por Estado --}}
                    <div class="col-12 col-md-3">
                        <label for="estado" class="form-label fw-bold">
                            <i class="fas fa-filter me-1"></i>
                            Estado
                        </label>
                        <select class="form-select" id="estado" name="estado" onchange="document.getElementById('filterForm').submit();">
                            <option value="">Todos los estados</option>
                            <option value="activo" {{ request('estado') == 'activo' ? 'selected' : '' }}>
                                Activos
                            </option>
                            <option value="inactivo" {{ request('estado') == 'inactivo' ? 'selected' : '' }}>
                                Inactivos
                            </option>
                        </select>
                    </div>

                    {{-- Ordenar por --}}
                    <div class="col-12 col-md-3">
                        <label for="sort" class="form-label fw-bold">
                            <i class="fas fa-sort me-1"></i>
                            Ordenar por
                        </label>
                        <select class="form-select" id="sort" name="sort" onchange="document.getElementById('filterForm').submit();">
                            <option value="nombre_asc" {{ request('sort', 'nombre_asc') == 'nombre_asc' ? 'selected' : '' }}>
                                Nombre (A-Z)
                            </option>
                            <option value="nombre_desc" {{ request('sort') == 'nombre_desc' ? 'selected' : '' }}>
                                Nombre (Z-A)
                            </option>
                            <option value="reciente" {{ request('sort') == 'reciente' ? 'selected' : '' }}>
                                Más recientes
                            </option>
                            <option value="antiguo" {{ request('sort') == 'antiguo' ? 'selected' : '' }}>
                                Más antiguos
                            </option>
                            <option value="pedidos_desc" {{ request('sort') == 'pedidos_desc' ? 'selected' : '' }}>
                                Más pedidos
                            </option>
                        </select>
                    </div>

                    {{-- Items por página --}}
                    <div class="col-12 col-md-2">
                        <label for="per_page" class="form-label fw-bold">
                            <i class="fas fa-list me-1"></i>
                            Mostrar
                        </label>
                        <select class="form-select" id="per_page" name="per_page" onchange="document.getElementById('filterForm').submit();">
                            <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>
                </div>

                {{-- Botones de acción --}}
                <div class="mt-3 d-flex justify-content-between align-items-center">
                    <div>
                        @if(request('search') || request('estado'))
                            <a href="{{ route('admin.clientes.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-redo me-1"></i>
                                Limpiar Filtros
                            </a>
                        @endif
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-search me-1"></i>
                        Buscar
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ==========================================
         TABLA DE CLIENTES
        ========================================== --}}
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>
                Listado de Clientes
            </h5>
            <div class="text-muted small">
                Mostrando
                <strong>{{ $clientes->firstItem() ?? 0 }}</strong> -
                <strong>{{ $clientes->lastItem() ?? 0 }}</strong>
                de
                <strong>{{ $clientes->total() }}</strong>
                cliente(s)

                @if(request('search'))
                    <span class="badge bg-primary ms-2">
                        <i class="fas fa-search me-1"></i>
                        Filtrado: "{{ request('search') }}"
                    </span>
                @endif

                @if(request('estado'))
                    <span class="badge bg-info ms-2">
                        <i class="fas fa-filter me-1"></i>
                        Estado: {{ ucfirst(request('estado')) }}
                    </span>
                @endif
            </div>
        </div>

        <div class="card-body p-0">
            @if($clientes->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">
                                    <i class="fas fa-hashtag me-1"></i>
                                    ID
                                </th>
                                <th>
                                    <i class="fas fa-user me-1"></i>
                                    Cliente
                                </th>
                                <th>
                                    <i class="fas fa-envelope me-1"></i>
                                    Contacto
                                </th>
                                <th class="text-center">
                                    <i class="fas fa-shopping-cart me-1"></i>
                                    Pedidos
                                </th>
                                <th class="text-center">
                                    <i class="fas fa-toggle-on me-1"></i>
                                    Estado
                                </th>
                                <th class="text-center pe-3">
                                    <i class="fas fa-cogs me-1"></i>
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($clientes as $cliente)
                                <tr>
                                    {{-- ID --}}
                                    <td class="ps-3">
                                        <span class="badge bg-light text-dark fs-6">
                                            #{{ $cliente->id }}
                                        </span>
                                    </td>

                                    {{-- Cliente (Avatar + Nombre) --}}
                                    <td>
                                        <div class="d-flex align-items-center">
                                            {{-- Avatar con iniciales --}}
                                            <div class="avatar me-3">
                                                <span class="avatar-initials">
                                                    {{ strtoupper(substr($cliente->nombre, 0, 1)) }}{{ strtoupper(substr($cliente->apellido ?? '', 0, 1)) }}
                                                </span>
                                            </div>
                                            {{-- Información --}}
                                            <div>
                                                <div class="fw-bold">
                                                    {{ $cliente->nombre }} {{ $cliente->apellido }}
                                                </div>
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar-plus me-1"></i>
                                                    Registro: {{ $cliente->created_at->format('d/m/Y') }}
                                                </small>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Contacto --}}
                                    <td>
                                        @if($cliente->email)
                                            <div class="mb-1">
                                                <i class="fas fa-envelope text-primary me-1"></i>
                                                <a href="mailto:{{ $cliente->email }}" class="text-decoration-none">
                                                    {{ $cliente->email }}
                                                </a>
                                            </div>
                                        @endif
                                        @if($cliente->telefono)
                                            <div>
                                                <i class="fas fa-phone text-success me-1"></i>
                                                <a href="tel:{{ $cliente->telefono }}" class="text-decoration-none">
                                                    {{ $cliente->telefono }}
                                                </a>
                                            </div>
                                        @else
                                            <small class="text-muted">Sin teléfono</small>
                                        @endif
                                    </td>

                                    {{-- Pedidos --}}
                                    <td class="text-center">
                                        <span class="badge {{ $cliente->pedidos_count > 0 ? 'bg-info' : 'bg-light text-dark' }} fs-6">
                                            <i class="fas fa-shopping-cart me-1"></i>
                                            {{ $cliente->pedidos_count ?? 0 }} Pedidos
                                        </span>
                                    </td>

                                    {{-- Estado con Toggle --}}
                                    <td class="text-center">
                                        <div class="form-check form-switch d-flex justify-content-center">
                                            <input class="form-check-input toggle-estado" 
                                                   type="checkbox" 
                                                   role="switch" 
                                                   id="estado_{{ $cliente->id }}"
                                                   data-cliente-id="{{ $cliente->id }}"
                                                   {{ $cliente->estado == 'activo' ? 'checked' : '' }}
                                                   style="cursor: pointer;">
                                            <label class="form-check-label ms-2 small" for="estado_{{ $cliente->id }}">
                                                <span class="estado-text-{{ $cliente->id }}">
                                                    {{ ucfirst($cliente->estado) }}
                                                </span>
                                            </label>
                                        </div>
                                    </td>

                                    {{-- Acciones --}}
                                    <td class="text-center pe-3">
                                        <div class="btn-group" role="group">
                                            {{-- Ver --}}
                                            <a href="{{ route('admin.clientes.show', $cliente) }}" 
                                               class="btn btn-sm btn-outline-primary" 
                                               title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>

                                            {{-- Editar --}}
                                            <a href="{{ route('admin.clientes.edit', $cliente) }}" 
                                               class="btn btn-sm btn-outline-warning" 
                                               title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            {{-- Eliminar --}}
                                            <form action="{{ route('admin.clientes.destroy', $cliente) }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('⚠️ ¿Estás seguro de eliminar a {{ $cliente->nombre }} {{ $cliente->apellido }}?\n\nEsta acción NO se puede deshacer.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="btn btn-sm btn-outline-danger" 
                                                        title="Eliminar">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Paginación --}}
                <div class="card-footer bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Mostrando {{ $clientes->firstItem() }} - {{ $clientes->lastItem() }} de {{ $clientes->total() }} clientes
                        </div>
                        <div>
                            {{ $clientes->links() }}
                        </div>
                    </div>
                </div>

            @else
                {{-- Estado Vacío --}}
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-users fa-5x text-muted opacity-50"></i>
                    </div>

                    @if(request('search') || request('estado'))
                        {{-- No hay resultados con filtros --}}
                        <h5 class="text-muted mb-2">No se encontraron clientes</h5>
                        <p class="text-muted mb-4">
                            Intenta ajustar los filtros de búsqueda o crear un nuevo cliente.
                        </p>
                        <a href="{{ route('admin.clientes.index') }}" class="btn btn-outline-secondary me-2">
                            <i class="fas fa-redo me-2"></i>
                            Limpiar Filtros
                        </a>
                        <a href="{{ route('admin.clientes.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus-circle me-2"></i>
                            Crear Cliente
                        </a>
                    @else
                        {{-- No hay clientes en el sistema --}}
                        <h5 class="text-muted mb-2">No hay clientes registrados</h5>
                        <p class="text-muted mb-4">
                            Comienza agregando tu primer cliente para gestionar mejor tus ventas.
                        </p>
                        <a href="{{ route('admin.clientes.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus-circle me-2"></i>
                            Crear Primer Cliente
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>

</div>
@endsection

{{-- ==========================================
     ESTILOS PERSONALIZADOS
    ========================================== --}}
@push('styles')
<style>
    /* Tarjetas de Estadísticas */
    .stat-card {
        transition: all 0.3s ease;
        border-left: 4px solid transparent;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0,0,0,0.15) !important;
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0.1;
    }

    /* Avatar en tabla */
    .avatar {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .avatar-initials {
        color: white;
        font-weight: bold;
        font-size: 16px;
        text-transform: uppercase;
    }

    /* Tabla hover mejorada */
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
        cursor: pointer;
    }

    /* Switch de estado */
    .form-switch .form-check-input {
        width: 3rem;
        height: 1.5rem;
    }

    .form-switch .form-check-input:checked {
        background-color: #28a745;
        border-color: #28a745;
    }

    /* Botones de acción */
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
    }

    /* Badges personalizados */
    .badge {
        padding: 0.35rem 0.65rem;
        font-weight: 500;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .avatar {
            width: 35px;
            height: 35px;
        }

        .avatar-initials {
            font-size: 14px;
        }

        .stat-card h3 {
            font-size: 1.5rem;
        }
    }
</style>
@endpush

{{-- ==========================================
     JAVASCRIPT PARA TOGGLE DE ESTADO
    ========================================== --}}
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle de estado AJAX
        document.querySelectorAll('.toggle-estado').forEach(function(toggle) {
            toggle.addEventListener('change', function() {
                const clienteId = this.dataset.clienteId;
                const isChecked = this.checked;

                // Deshabilitar el toggle mientras se procesa
                this.disabled = true;

                fetch(`/clientes/${clienteId}/toggle-estado`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Actualizar texto del estado
                        const estadoText = document.querySelector(`.estado-text-${clienteId}`);
                        if (estadoText) {
                            estadoText.textContent = data.nuevo_estado.charAt(0).toUpperCase() + data.nuevo_estado.slice(1);
                        }

                        // Mostrar notificación
                        mostrarNotificacion(data.message, 'success');
                    } else {
                        // Revertir el switch si hay error
                        this.checked = !isChecked;
                        mostrarNotificacion('Error al cambiar el estado', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    // Revertir el switch si hay error
                    this.checked = !isChecked;
                    mostrarNotificacion('Error de conexión', 'error');
                })
                .finally(() => {
                    // Rehabilitar el toggle
                    this.disabled = false;
                });
            });
        });

        // Función para mostrar notificaciones
        function mostrarNotificacion(mensaje, tipo) {
            const alertClass = tipo === 'success' ? 'alert-success' : 'alert-danger';
            const iconClass = tipo === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';

            const alertHTML = `
                <div class="alert ${alertClass} alert-dismissible fade show position-fixed top-0 end-0 m-3" 
                     role="alert" 
                     style="z-index: 9999; min-width: 300px;">
                    <i class="fas ${iconClass} me-2"></i>
                    ${mensaje}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;

            document.body.insertAdjacentHTML('beforeend', alertHTML);

            // Auto-cerrar después de 3 segundos
            setTimeout(() => {
                const alert = document.querySelector('.alert:last-of-type');
                if (alert) {
                    alert.remove();
                }
            }, 3000);
        }

        // Auto-hide alerts después de 5 segundos
        setTimeout(() => {
            document.querySelectorAll('.alert-dismissible').forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    });
</script>
@endpush