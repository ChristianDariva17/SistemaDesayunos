@extends('layouts.app')

@section('title', 'Registrar Ajuste de Stock')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.productos.index') }}">Productos</a></li>
    <li class="breadcrumb-item active">Ajuste de Stock</li>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="row align-items-center mb-4">
        <div class="col-lg-8">
            <h2 class="mb-2">
                <i class="fas fa-sliders-h text-warning me-2"></i>
                Registrar Ajuste de Stock
            </h2>
            <p class="text-muted mb-0">
                Corrige el stock de un producto y deja un movimiento de ajuste registrado en el historial auditable.
            </p>
        </div>
        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
            <a href="{{ route('admin.reportes.stock-movimientos') }}" class="btn btn-outline-info shadow-sm">
                <i class="fas fa-exchange-alt me-2"></i>Ver Movimientos
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-xl-7 col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="fas fa-clipboard-check text-warning me-2"></i>Datos del ajuste
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.stock-adjustments.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="producto_id" class="form-label fw-bold">Producto</label>
                            <select id="producto_id" name="producto_id" class="form-select @error('producto_id') is-invalid @enderror" required>
                                <option value="">Selecciona un producto</option>
                                @foreach($productos as $producto)
                                    <option value="{{ $producto->id }}" @selected((string) old('producto_id') === (string) $producto->id)>
                                        {{ $producto->nombre }} — stock actual: {{ number_format($producto->stock) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('producto_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="stock_nuevo" class="form-label fw-bold">Nuevo stock</label>
                            <input
                                id="stock_nuevo"
                                type="number"
                                name="stock_nuevo"
                                class="form-control @error('stock_nuevo') is-invalid @enderror"
                                value="{{ old('stock_nuevo') }}"
                                min="0"
                                step="1"
                                required
                                placeholder="Ej: 24"
                            >
                            @error('stock_nuevo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="motivo" class="form-label fw-bold">Motivo del ajuste</label>
                            <textarea
                                id="motivo"
                                name="motivo"
                                class="form-control @error('motivo') is-invalid @enderror"
                                rows="3"
                                maxlength="255"
                                required
                                placeholder="Ej: Corrección por conteo físico de inventario"
                            >{{ old('motivo') }}</textarea>
                            @error('motivo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.productos.index') }}" class="btn btn-outline-secondary">
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-save me-2"></i>Registrar Ajuste
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
