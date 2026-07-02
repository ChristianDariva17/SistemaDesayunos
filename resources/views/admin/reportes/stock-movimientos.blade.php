@extends('layouts.app')

@section('title', 'Movimientos de Stock')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.reportes.index') }}">Reportes</a></li>
    <li class="breadcrumb-item active">Movimientos de Stock</li>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="row align-items-center mb-4">
        <div class="col-lg-8">
            <h2 class="mb-2">
                <i class="fas fa-exchange-alt text-info me-2"></i>
                Movimientos de Stock
            </h2>
            <p class="text-muted mb-0">
                <i class="fas fa-info-circle me-1"></i>
                Historial auditable de entradas, salidas y ajustes del inventario.
            </p>
        </div>
        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
            <div class="d-flex gap-2 justify-content-lg-end justify-content-start flex-wrap">
                <a href="{{ route('admin.stock-entries.create') }}" class="btn btn-success shadow-sm">
                    <i class="fas fa-dolly me-2"></i>Registrar Entrada
                </a>
                <a href="{{ route('admin.reportes.index') }}" class="btn btn-outline-secondary shadow-sm">
                    <i class="fas fa-arrow-left me-2"></i>Volver a Reportes
                </a>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0">
                <i class="fas fa-filter text-info me-2"></i>Filtros
            </h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.reportes.stock-movimientos') }}" method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-3 col-md-6">
                        <label for="producto_id" class="form-label fw-bold">Producto</label>
                        <select id="producto_id" name="producto_id" class="form-select">
                            <option value="">Todos los productos</option>
                            @foreach($productos as $producto)
                                <option value="{{ $producto->id }}" @selected((string) request('producto_id') === (string) $producto->id)>
                                    {{ $producto->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-6">
                        <label for="tipo" class="form-label fw-bold">Tipo</label>
                        <select id="tipo" name="tipo" class="form-select">
                            <option value="">Todos los tipos</option>
                            @foreach($tipos as $tipo)
                                <option value="{{ $tipo }}" @selected(request('tipo') === $tipo)>
                                    {{ ucfirst($tipo) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <label for="user_id" class="form-label fw-bold">Usuario / Actor</label>
                        <select id="user_id" name="user_id" class="form-select">
                            <option value="">Todos los actores</option>
                            @foreach($usuarios as $usuario)
                                <option value="{{ $usuario->id }}" @selected((string) request('user_id') === (string) $usuario->id)>
                                    {{ $usuario->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-6">
                        <label for="fecha_inicio" class="form-label fw-bold">Fecha inicio</label>
                        <input id="fecha_inicio" type="date" name="fecha_inicio" class="form-control" value="{{ request('fecha_inicio') }}">
                    </div>

                    <div class="col-lg-2 col-md-6">
                        <label for="fecha_fin" class="form-label fw-bold">Fecha fin</label>
                        <input id="fecha_fin" type="date" name="fecha_fin" class="form-control" value="{{ request('fecha_fin') }}">
                    </div>

                    <div class="col-12 d-flex gap-2 justify-content-end">
                        @if(request()->hasAny(['producto_id', 'tipo', 'user_id', 'fecha_inicio', 'fecha_fin']))
                            <a href="{{ route('admin.reportes.stock-movimientos') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Limpiar
                            </a>
                        @endif
                        <button type="submit" class="btn btn-info text-white">
                            <i class="fas fa-search me-2"></i>Aplicar filtros
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-table text-info me-2"></i>Listado de movimientos
            </h5>
            <span class="badge bg-info bg-opacity-10 text-info border border-info">
                {{ number_format($movimientos->total()) }} registros
            </span>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Producto</th>
                            <th>Pedido</th>
                            <th>Usuario / Actor</th>
                            <th class="text-center">Tipo</th>
                            <th class="text-center">Cantidad</th>
                            <th class="text-center">Stock anterior</th>
                            <th class="text-center">Stock nuevo</th>
                            <th>Motivo</th>
                            <th class="text-center">Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($movimientos as $movimiento)
                            <tr>
                                <td>
                                    <span class="fw-bold">{{ $movimiento->producto?->nombre ?? 'Producto no disponible' }}</span>
                                </td>
                                <td>
                                    {{ $movimiento->pedido_numero ?: ($movimiento->pedido?->numero_pedido ?? 'Sin pedido') }}
                                </td>
                                <td>
                                    @if($movimiento->user)
                                        <span class="fw-semibold">{{ $movimiento->user->name }}</span>
                                        <small class="text-muted d-block">{{ $movimiento->user->email }}</small>
                                    @else
                                        <span class="text-muted">Sistema</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $movimiento->tipo === 'salida' ? 'danger' : ($movimiento->tipo === 'entrada' ? 'success' : 'secondary') }} bg-opacity-10 text-{{ $movimiento->tipo === 'salida' ? 'danger' : ($movimiento->tipo === 'entrada' ? 'success' : 'secondary') }} border">
                                        {{ ucfirst($movimiento->tipo) }}
                                    </span>
                                </td>
                                <td class="text-center fw-bold">{{ number_format($movimiento->cantidad) }}</td>
                                <td class="text-center">{{ number_format($movimiento->stock_anterior) }}</td>
                                <td class="text-center">{{ number_format($movimiento->stock_nuevo) }}</td>
                                <td>{{ $movimiento->motivo ?? 'Sin motivo' }}</td>
                                <td class="text-center">
                                    <span class="fw-semibold">{{ $movimiento->created_at?->format('d/m/Y') }}</span>
                                    <small class="text-muted d-block">{{ $movimiento->created_at?->format('H:i') }}</small>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3 d-block text-secondary opacity-50"></i>
                                        <h5 class="fw-bold">No hay movimientos para mostrar</h5>
                                        <p class="mb-0">Ajusta los filtros o registra movimientos de inventario.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($movimientos->hasPages())
            <div class="card-footer bg-white border-top">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Mostrando {{ $movimientos->firstItem() }} a {{ $movimientos->lastItem() }} de {{ $movimientos->total() }} movimientos
                    </div>
                    <div>
                        {{ $movimientos->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
