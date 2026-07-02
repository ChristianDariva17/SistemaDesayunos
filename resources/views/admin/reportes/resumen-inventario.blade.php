@extends('layouts.app')

@section('title', 'Resumen de Inventario')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.reportes.index') }}">Reportes</a></li>
    <li class="breadcrumb-item active">Resumen de Inventario</li>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="row align-items-center mb-4">
        <div class="col-lg-8">
            <h2 class="mb-2">
                <i class="fas fa-clipboard-list text-primary me-2"></i>
                Resumen de Inventario
            </h2>
            <p class="text-muted mb-0">
                <i class="fas fa-info-circle me-1"></i>
                Stock actual por producto con totales de entradas, salidas y ajustes.
            </p>
        </div>
        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
            <a href="{{ route('admin.reportes.index') }}" class="btn btn-outline-secondary shadow-sm">
                <i class="fas fa-arrow-left me-2"></i>Volver a Reportes
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0">
                <i class="fas fa-filter text-primary me-2"></i>Filtros
            </h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.reportes.resumen-inventario') }}" method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-4 col-md-6">
                        <label for="buscar" class="form-label fw-bold">Producto</label>
                        <input id="buscar" type="search" name="buscar" class="form-control" value="{{ request('buscar') }}" placeholder="Buscar por nombre">
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <label for="categoria" class="form-label fw-bold">Categoría</label>
                        <select id="categoria" name="categoria" class="form-select">
                            <option value="">Todas las categorías</option>
                            @foreach($categorias as $categoria)
                                <option value="{{ $categoria }}" @selected(request('categoria') === $categoria)>
                                    {{ ucfirst($categoria) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <label for="estado" class="form-label fw-bold">Estado</label>
                        <select id="estado" name="estado" class="form-select">
                            <option value="">Todos los estados</option>
                            <option value="activo" @selected(request('estado') === 'activo')>Activo</option>
                            <option value="inactivo" @selected(request('estado') === 'inactivo')>Inactivo</option>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-6 d-flex gap-2 justify-content-end">
                        @if(request()->hasAny(['buscar', 'categoria', 'estado']))
                            <a href="{{ route('admin.reportes.resumen-inventario') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>Filtrar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-table text-primary me-2"></i>Productos
            </h5>
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary">
                {{ number_format($productos->total()) }} productos
            </span>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Producto</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Stock actual</th>
                            <th class="text-center">Stock mínimo</th>
                            <th class="text-center">Entradas</th>
                            <th class="text-center">Salidas</th>
                            <th class="text-center">Ajustes</th>
                            <th class="text-center">Último movimiento</th>
                            <th class="text-center">Situación</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($productos as $producto)
                            @php
                                $minimumStock = (int) $producto->stock_minimo;
                                $currentStock = (int) $producto->stock;
                                $stockStatus = $minimumStock <= 0
                                    ? ['label' => 'Sin mínimo', 'class' => 'secondary']
                                    : ($currentStock <= $minimumStock
                                        ? ['label' => 'Stock bajo', 'class' => 'danger']
                                        : ['label' => 'OK', 'class' => 'success']);
                            @endphp
                            <tr>
                                <td>
                                    <span class="fw-bold">{{ $producto->nombre }}</span>
                                    <small class="text-muted d-block">{{ ucfirst($producto->categoria) }}</small>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $producto->estado === 'activo' ? 'success' : 'danger' }} bg-opacity-10 text-{{ $producto->estado === 'activo' ? 'success' : 'danger' }} border">
                                        {{ ucfirst($producto->estado) }}
                                    </span>
                                </td>
                                <td class="text-center fw-bold">{{ number_format($currentStock) }}</td>
                                <td class="text-center">{{ number_format($minimumStock) }}</td>
                                <td class="text-center text-success fw-semibold">{{ number_format((int) ($producto->total_entradas ?? 0)) }}</td>
                                <td class="text-center text-danger fw-semibold">{{ number_format((int) ($producto->total_salidas ?? 0)) }}</td>
                                <td class="text-center text-secondary fw-semibold">{{ number_format((int) ($producto->total_ajustes ?? 0)) }}</td>
                                <td class="text-center">
                                    @if($producto->ultimo_movimiento_fecha)
                                        <span class="fw-semibold">{{ \Carbon\Carbon::parse($producto->ultimo_movimiento_fecha)->format('d/m/Y') }}</span>
                                        <small class="text-muted d-block">{{ ucfirst($producto->ultimo_movimiento_tipo) }}</small>
                                    @else
                                        <span class="text-muted">Sin movimientos</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $stockStatus['class'] }} bg-opacity-10 text-{{ $stockStatus['class'] }} border">
                                        {{ $stockStatus['label'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3 d-block text-secondary opacity-50"></i>
                                        <h5 class="fw-bold">No hay productos para mostrar</h5>
                                        <p class="mb-0">Ajusta los filtros o registra productos en el inventario.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

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
@endsection
