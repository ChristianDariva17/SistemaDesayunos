@extends('layouts.app')

@section('title', 'Perfil de Cliente - ' . trim($cliente->nombre . ' ' . ($cliente->apellido ?? '')))

@section('breadcrumb')
        <li class="breadcrumb-item"><a href="{{ route('admin.clientes.index') }}">Clientes</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ trim($cliente->nombre . ' ' . ($cliente->apellido ?? '')) }}</li>
@endsection

@section('content')
<div class="container-fluid py-4">

    <!-- ============================================ -->
    <!-- 1. ALERTAS DE ÉXITO/ERROR -->
    <!-- ============================================ -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <strong>¡Éxito!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Error:</strong> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- ============================================ -->
    <!-- 2. ENCABEZADO CON AVATAR Y ACCIONES -->
    <!-- ============================================ -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <!-- AVATAR Y NOMBRE -->
                <div class="col-12 col-md-6">
                    <div class="d-flex align-items-center">
                        <!-- Avatar Grande con Iniciales -->
                        <div class="avatar-large me-4">
                            <span class="avatar-initials-large">
                                {{ strtoupper(substr($cliente->nombre, 0, 1)) }}{{ strtoupper(substr($cliente->apellido ?? '', 0, 1)) }}
                            </span>
                        </div>

                        <!-- Información Principal -->
                        <div>
                            <h2 class="mb-1 fw-bold">
                                {{ trim($cliente->nombre . ' ' . ($cliente->apellido ?? '')) }}
                                @if($cliente->estado == 'activo')
                                    <span class="badge bg-success ms-2">
                                        <i class="fas fa-check-circle me-1"></i>
                                        Activo
                                    </span>
                                @else
                                    <span class="badge bg-secondary ms-2">
                                        <i class="fas fa-ban me-1"></i>
                                        Inactivo
                                    </span>
                                @endif
                            </h2>

                            <p class="text-muted mb-1">
                            @if($cliente->email)
                                <i class="fas fa-envelope me-2"></i>
                                <a href="mailto:{{ $cliente->email }}" class="text-decoration-none">
                                    {{ $cliente->email }}
                                </a>
                            @else
                                <span class="text-muted">
                                    <i class="fas fa-envelope me-2"></i>
                                    Sin correo electrónico
                                </span>
                            @endif
                            </p>

                            @if($cliente->telefono)
                                <p class="text-muted mb-1">
                                    <i class="fas fa-phone me-2"></i>
                                    <a href="tel:{{ $cliente->telefono }}" class="text-decoration-none">
                                        {{ $cliente->telefono }}
                                    </a>
                                </p>
                            @endif

                            <p class="text-muted mb-0">
                                <i class="fas fa-calendar me-2"></i>
                                Cliente desde: {{ $cliente->created_at->format('d/m/Y') }}
                                <small>({{ $cliente->created_at->diffForHumans() }})</small>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- BOTONES DE ACCIÓN -->
                <div class="col-12 col-md-6 mt-3 mt-md-0">
                    <div class="d-flex gap-2 justify-content-md-end flex-wrap">
                        <!-- Volver -->
                        <a href="{{ route('admin.clientes.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>
                            Volver al Listado
                        </a>

                        <!-- Editar -->
                        <a href="{{ route('admin.clientes.edit', $cliente) }}" class="btn btn-warning">
                            <i class="fas fa-edit me-2"></i>
                            Editar Cliente
                        </a>

                        <!-- Eliminar -->
                        <form action="{{ route('admin.clientes.destroy', $cliente) }}" 
                              method="POST" 
                              class="d-inline"
                              onsubmit="return confirm('⚠️ ¿Estás seguro de eliminar a {{ trim($cliente->nombre . ' ' . ($cliente->apellido ?? '')) }}?\n\nEsta acción NO se puede deshacer.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash-alt me-2"></i>
                                Eliminar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- 3. TARJETAS DE ESTADÍSTICAS -->
    <!-- ============================================ -->
    <div class="row g-3 mb-4">
        <!-- Total de Pedidos -->
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 text-uppercase small fw-bold">
                                <i class="fas fa-shopping-cart me-1"></i>
                                Total Pedidos
                            </p>
                            <h3 class="mb-0 fw-bold text-primary">{{ $cliente->pedidos_count ?? 0 }}</h3>
                            <small class="text-muted">
                                @if($ultimoPedido)
                                    Último: {{ $ultimoPedido->created_at->format('d/m/Y') }}
                                @else
                                    Sin pedidos
                                @endif
                            </small>
                        </div>
                        <div class="stat-icon bg-primary">
                            <i class="fas fa-shopping-cart fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Gastado -->
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 text-uppercase small fw-bold">
                                <i class="fas fa-dollar-sign me-1"></i>
                                Total Gastado
                            </p>
                            <h3 class="mb-0 fw-bold text-success">S/ {{ number_format($totalGastado, 2) }}</h3>
                            <small class="text-muted">
                                Solo pedidos completados
                            </small>
                        </div>
                        <div class="stat-icon bg-success">
                            <i class="fas fa-dollar-sign fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Promedio por Pedido -->
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 text-uppercase small fw-bold">
                                <i class="fas fa-chart-line me-1"></i>
                                Promedio por Pedido
                            </p>
                            <h3 class="mb-0 fw-bold text-info">
                                S/ {{ $cliente->pedidos_count > 0 ? number_format($totalGastado / $cliente->pedidos_count, 2) : '0.00' }}
                            </h3>
                            <small class="text-muted">Ticket promedio</small>
                        </div>
                        <div class="stat-icon bg-info">
                            <i class="fas fa-chart-line fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pedidos Completados -->
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 text-uppercase small fw-bold">
                                <i class="fas fa-check-circle me-1"></i>
                                Completados
                            </p>
                            <h3 class="mb-0 fw-bold text-success">
                                {{ $cliente->pedidos->where('estado', 'completado')->count() }}
                            </h3>
                            <small class="text-muted">
                                @if($cliente->pedidos_count > 0)
                                    {{ round(($cliente->pedidos->where('estado', 'completado')->count() / $cliente->pedidos_count) * 100, 1) }}% del total
                                @else
                                    0% del total
                                @endif
                            </small>
                        </div>
                        <div class="stat-icon bg-success">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- ============================================ -->
        <!-- 4. INFORMACIÓN PERSONAL -->
        <!-- ============================================ -->
        <div class="col-12 col-lg-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user-circle me-2"></i>
                        Información Personal
                    </h5>
                </div>
                <div class="card-body">
                    <div class="info-item">
                        <label class="info-label">
                            <i class="fas fa-id-card text-primary me-2"></i>
                            ID Cliente:
                        </label>
                        <span class="info-value">#{{ $cliente->id }}</span>
                    </div>

                    <div class="info-item">
                        <label class="info-label">
                            <i class="fas fa-user text-primary me-2"></i>
                            Nombre Completo:
                        </label>
                        <span class="info-value fw-bold">{{ trim($cliente->nombre . ' ' . ($cliente->apellido ?? '')) }}</span>
                    </div>

                    <div class="info-item">
                        <label class="info-label">
                            <i class="fas fa-envelope text-primary me-2"></i>
                            Email:
                        </label>
                        <span class="info-value">
                            <a href="mailto:{{ $cliente->email }}" class="text-decoration-none">
                                {{ $cliente->email }}
                            </a>
                        </span>
                    </div>

                    <div class="info-item">
                        <label class="info-label">
                            <i class="fas fa-phone text-primary me-2"></i>
                            Teléfono:
                        </label>
                        <span class="info-value">
                            @if($cliente->telefono)
                                <a href="tel:{{ $cliente->telefono }}" class="text-decoration-none">
                                    {{ $cliente->telefono }}
                                </a>
                            @else
                                <span class="text-muted">No registrado</span>
                            @endif
                        </span>
                    </div>

                    <div class="info-item">
                        <label class="info-label">
                            <i class="fas fa-map-marker-alt text-primary me-2"></i>
                            Dirección:
                        </label>
                        <span class="info-value">
                            {{ $cliente->direccion ?? 'No registrada' }}
                        </span>
                    </div>

                    <div class="info-item">
                        <label class="info-label">
                            <i class="fas fa-birthday-cake text-primary me-2"></i>
                            Fecha de Nacimiento:
                        </label>
                        <span class="info-value">
                            @if($cliente->fecha_nacimiento)
                                {{ $cliente->fecha_nacimiento->format('d/m/Y') }}
                                @if($edad)
                                    <span class="badge bg-info ms-2">{{ $edad }} años</span>
                                @endif
                            @else
                                <span class="text-muted">No registrada</span>
                            @endif
                        </span>
                    </div>

                    <div class="info-item">
                        <label class="info-label">
                            <i class="fas fa-toggle-on text-primary me-2"></i>
                            Estado:
                        </label>
                        <span class="info-value">
                            @if($cliente->estado == 'activo')
                                <span class="badge bg-success">
                                    <i class="fas fa-check-circle me-1"></i>
                                    Activo
                                </span>
                            @else
                                <span class="badge bg-secondary">
                                    <i class="fas fa-ban me-1"></i>
                                    Inactivo
                                </span>
                            @endif
                        </span>
                    </div>

                    <div class="info-item border-0 pb-0">
                        <label class="info-label">
                            <i class="fas fa-calendar-plus text-primary me-2"></i>
                            Registro:
                        </label>
                        <span class="info-value">
                            {{ $cliente->created_at->format('d/m/Y H:i') }}
                            <br>
                            <small class="text-muted">{{ $cliente->created_at->diffForHumans() }}</small>
                        </span>
                    </div>

                    @if($cliente->notas)
                        <hr class="my-3">
                        <div class="info-item border-0 pb-0">
                            <label class="info-label">
                                <i class="fas fa-sticky-note text-primary me-2"></i>
                                Notas:
                            </label>
                            <div class="alert alert-light mt-2 mb-0">
                                <small>{{ $cliente->notas }}</small>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- 5. HISTORIAL DE PEDIDOS -->
        <!-- ============================================ -->
        <div class="col-12 col-lg-8 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2 text-primary"></i>
                        Historial de Pedidos
                        <span class="badge bg-primary ms-2">{{ $cliente->pedidos_count ?? 0 }}</span>
                    </h5>
                    @if($cliente->pedidos_count > 0)
                        <small class="text-muted">
                            Mostrando últimos 5 pedidos
                        </small>
                    @endif
                </div>
                <div class="card-body">
                    @if($cliente->pedidos->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">
                                            <i class="fas fa-hashtag me-1"></i>
                                            Pedido
                                        </th>
                                        <th>
                                            <i class="fas fa-calendar me-1"></i>
                                            Fecha
                                        </th>
                                        <th>
                                            <i class="fas fa-box me-1"></i>
                                            Productos
                                        </th>
                                        <th class="text-end">
                                            <i class="fas fa-dollar-sign me-1"></i>
                                            Total
                                        </th>
                                        <th class="text-center">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Estado
                                        </th>
                                        <th class="text-center pe-3">
                                            <i class="fas fa-cogs me-1"></i>
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cliente->pedidos as $pedido)
                                        <tr>
                                            <!-- NÚMERO DE PEDIDO -->
                                            <td class="ps-3">
                                                <span class="badge bg-light text-dark fs-6">
                                                    #{{ $pedido->id }}
                                                </span>
                                            </td>

                                            <!-- FECHA -->
                                            <td>
                                                <div>
                                                    <strong>{{ $pedido->created_at->format('d/m/Y') }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $pedido->created_at->format('H:i') }}</small>
                                                </div>
                                            </td>

                                            <!-- PRODUCTOS -->
                                            <td>
                                                <span class="badge bg-info">
                                                    {{ $pedido->productos->count() }} producto(s)
                                                </span>
                                            </td>

                                            <!-- TOTAL -->
                                            <td class="text-end">
                                                <strong class="text-success fs-6">
                                                    S/ {{ number_format($pedido->total ?? 0, 2) }}
                                                </strong>
                                            </td>

                                            <!-- ESTADO -->
                                            <td class="text-center">
                                                @switch($pedido->estado)
                                                    @case('pendiente')
                                                        <span class="badge bg-warning text-dark">
                                                            <i class="fas fa-clock me-1"></i>
                                                            Pendiente
                                                        </span>
                                                        @break
                                                    @case('procesando')
                                                        <span class="badge bg-info">
                                                            <i class="fas fa-spinner me-1"></i>
                                                            Procesando
                                                        </span>
                                                        @break
                                                    @case('completado')
                                                        <span class="badge bg-success">
                                                            <i class="fas fa-check-circle me-1"></i>
                                                            Completado
                                                        </span>
                                                        @break
                                                    @case('cancelado')
                                                        <span class="badge bg-danger">
                                                            <i class="fas fa-times-circle me-1"></i>
                                                            Cancelado
                                                        </span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-secondary">
                                                            {{ ucfirst($pedido->estado) }}
                                                        </span>
                                                @endswitch
                                            </td>

                                            <!-- ACCIONES -->
                                            <td class="text-center pe-3">
                                                <a href="{{ route('admin.pedidos.show', $pedido) }}" 
                                                   class="btn btn-sm btn-outline-primary"
                                                   title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($cliente->pedidos_count > 5)
                            <div class="alert alert-info mt-3 mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                Este cliente tiene <strong>{{ $cliente->pedidos_count }}</strong> pedidos en total. 
                                Mostrando solo los últimos 5.
                            </div>
                        @endif

                    @else
                        <!-- ESTADO VACÍO -->
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="fas fa-shopping-cart fa-5x text-muted opacity-50"></i>
                            </div>
                            <h5 class="text-muted mb-2">Este cliente aún no tiene pedidos</h5>
                            <p class="text-muted mb-4">
                                Cuando se creen pedidos para este cliente, aparecerán aquí.
                            </p>
                            @if($cliente->estado === 'activo')
                                <a href="{{ route('admin.pedidos.create', ['cliente_id' => $cliente->id]) }}" class="btn btn-primary">
                                    <i class="fas fa-plus-circle me-2"></i>
                                    Crear Primer Pedido
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- 6. TIMELINE DE ACTIVIDAD -->
    <!-- ============================================ -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-clock me-2 text-primary"></i>
                        Timeline de Actividad
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @if($ultimoPedido)
                            <!-- Último Pedido -->
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success">
                                    <i class="fas fa-shopping-cart"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="mb-0 fw-bold">Último Pedido Realizado</h6>
                                        <small class="text-muted">{{ $ultimoPedido->created_at->diffForHumans() }}</small>
                                    </div>
                                    <p class="mb-2">
                                        Pedido <strong>#{{ $ultimoPedido->id }}</strong> por un total de 
                                        <strong class="text-success">S/ {{ number_format($ultimoPedido->total ?? 0, 2) }}</strong>
                                    </p>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i>
                                        {{ $ultimoPedido->created_at->format('d/m/Y H:i:s') }}
                                    </small>
                                </div>
                            </div>
                        @endif

                        @if($cliente->updated_at != $cliente->created_at)
                            <!-- Actualización de Datos -->
                            <div class="timeline-item">
                                <div class="timeline-marker bg-warning">
                                    <i class="fas fa-edit"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="mb-0 fw-bold">Datos Actualizados</h6>
                                        <small class="text-muted">{{ $cliente->updated_at->diffForHumans() }}</small>
                                    </div>
                                    <p class="mb-2">
                                        Se actualizó la información del cliente
                                    </p>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i>
                                        {{ $cliente->updated_at->format('d/m/Y H:i:s') }}
                                    </small>
                                </div>
                            </div>
                        @endif

                        <!-- Registro Inicial -->
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="mb-0 fw-bold">Cliente Registrado</h6>
                                    <small class="text-muted">{{ $cliente->created_at->diffForHumans() }}</small>
                                </div>
                                <p class="mb-2">
                                    {{ trim($cliente->nombre . ' ' . ($cliente->apellido ?? '')) }} se registró en el sistema
                                </p>
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i>
                                    {{ $cliente->created_at->format('d/m/Y H:i:s') }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('styles')
<style>
    /* ============================================ */
    /* AVATAR GRANDE CON INICIALES */
    /* ============================================ */
    .avatar-large {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .avatar-initials-large {
        color: white;
        font-weight: bold;
        font-size: 40px;
        text-transform: uppercase;
    }

    /* ============================================ */
    /* TARJETAS DE ESTADÍSTICAS */
    /* ============================================ */
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

    .stat-icon i {
        color: currentColor;
    }

    /* ============================================ */
    /* INFORMACIÓN PERSONAL */
    /* ============================================ */
    .info-item {
        padding: 12px 0;
        border-bottom: 1px solid #e9ecef;
    }

    .info-item:last-child {
        border-bottom: none;
    }

    .info-label {
        display: block;
        font-weight: 600;
        color: #6c757d;
        margin-bottom: 5px;
        font-size: 0.9rem;
    }

    .info-value {
        display: block;
        color: #212529;
        font-size: 1rem;
    }

    /* ============================================ */
    /* TIMELINE */
    /* ============================================ */
    .timeline {
        position: relative;
        padding-left: 40px;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 15px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
    }

    .timeline-item {
        position: relative;
        margin-bottom: 30px;
        padding-bottom: 30px;
    }

    .timeline-item:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .timeline-marker {
        position: absolute;
        left: -40px;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        z-index: 1;
    }

    .timeline-content {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        border-left: 3px solid #667eea;
    }

    /* ============================================ */
    /* EFECTOS HOVER EN TABLA */
    /* ============================================ */
    .table tbody tr {
        transition: all 0.3s ease;
    }

    .table tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
        transform: scale(1.01);
    }

    /* ============================================ */
    /* ANIMACIÓN DE ALERTAS */
    /* ============================================ */
    .alert {
        animation: slideDown 0.3s ease-out;
    }

    @keyframes slideDown {
        from {
            transform: translateY(-20px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    /* ============================================ */
    /* RESPONSIVE */
    /* ============================================ */
    @media (max-width: 768px) {
        .avatar-large {
            width: 70px;
            height: 70px;
        }

        .avatar-initials-large {
            font-size: 28px;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
        }

        .timeline {
            padding-left: 30px;
        }

        .timeline-marker {
            left: -30px;
            width: 28px;
            height: 28px;
        }
    }

    /* ============================================ */
    /* BADGES PERSONALIZADOS */
    /* ============================================ */
    .badge {
        font-weight: 500;
        padding: 0.4em 0.8em;
    }
</style>
@endpush

@push('scripts')
<script>
    // ============================================
    // CONFIRMACIÓN MEJORADA PARA ELIMINAR
    // ============================================
    document.querySelectorAll('form[onsubmit*="confirm"]').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm(this.getAttribute('onsubmit').match(/'([^']+)'/)[1])) {
                e.preventDefault();
                return false;
            }
        });
    });

    // ============================================
    // ANIMACIÓN DE ENTRADA PARA TARJETAS
    // ============================================
    document.addEventListener('DOMContentLoaded', function() {
        const statCards = document.querySelectorAll('.stat-card');
        statCards.forEach((card, index) => {
            setTimeout(() => {
                card.style.animation = 'fadeInUp 0.5s ease-out';
                card.style.opacity = '1';
            }, index * 100);
        });
    });

    // ============================================
    // TOOLTIPS BOOTSTRAP
    // ============================================
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
</script>
@endpush
