@extends('layouts.app')

@section('title', 'Editar Producto')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.productos.index') }}">Productos</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.productos.show', $producto) }}">{{ $producto->nombre }}</a></li>
    <li class="breadcrumb-item active">Editar</li>
@endsection

@section('content')
<div class="container-fluid py-4">
    {{-- ========================================== --}}
    {{-- ENCABEZADO CON INFORMACIÓN DEL PRODUCTO --}}
    {{-- ========================================== --}}
    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm bg-primary bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-20 rounded-3 p-3 me-3">
                            <i class="fas fa-edit fa-2x text-primary"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h2 class="mb-1">Editar Producto</h2>
                            <p class="text-muted mb-0">
                                <i class="fas fa-info-circle me-1"></i>
                                Modifica la información de <strong>{{ $producto->nombre }}</strong>
                            </p>
                        </div>
                        <div class="text-end">
                            <div class="badge bg-white text-dark border mb-2">
                                <i class="fas fa-hashtag me-1"></i>
                                ID: {{ $producto->id }}
                            </div>
                            <div>
                                <small class="text-muted d-block">
                                    <i class="fas fa-calendar-plus me-1"></i>
                                    Creado: {{ $producto->created_at->format('d/m/Y H:i') }}
                                </small>
                                <small class="text-muted d-block">
                                    <i class="fas fa-clock me-1"></i>
                                    Última actualización: {{ $producto->updated_at->format('d/m/Y H:i') }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- ALERTAS DE ERROR --}}
    {{-- ========================================== --}}
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <div class="d-flex align-items-start">
                <i class="fas fa-exclamation-triangle fa-2x me-3 mt-1"></i>
                <div class="flex-grow-1">
                    <h5 class="alert-heading mb-2">
                        <strong>¡Errores de Validación!</strong>
                    </h5>
                    <p class="mb-2">Por favor corrige los siguientes errores antes de continuar:</p>
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- ========================================== --}}
    {{-- FORMULARIO DE EDICIÓN --}}
    {{-- ========================================== --}}
    <form action="{{ route('admin.productos.update', $producto) }}" method="POST" enctype="multipart/form-data" id="formProducto">
        @csrf
        @method('PUT')

        <div class="row g-4">
            {{-- ========================================== --}}
            {{-- COLUMNA IZQUIERDA: INFORMACIÓN BÁSICA --}}
            {{-- ========================================== --}}
            <div class="col-lg-8">
                {{-- SECCIÓN: INFORMACIÓN GENERAL --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle text-primary me-2"></i>
                            Información General
                        </h5>
                    </div>
                    <div class="card-body">
                        {{-- Nombre del Producto --}}
                        <div class="mb-4">
                            <label for="nombre" class="form-label fw-bold">
                                Nombre del Producto <span class="text-danger">*</span>
                                <i class="fas fa-question-circle text-muted" 
                                   data-bs-toggle="tooltip" 
                                   data-bs-placement="right" 
                                   title="Nombre descriptivo del producto (máx. 255 caracteres)"></i>
                            </label>
                            <input 
                                type="text" 
                                name="nombre" 
                                id="nombre" 
                                class="form-control form-control-lg @error('nombre') is-invalid @enderror" 
                                value="{{ old('nombre', $producto->nombre) }}" 
                                placeholder="Ej: Ceviche de Pescado"
                                maxlength="255"
                                required
                                autofocus
                            >
                            <div class="d-flex justify-content-between mt-1">
                                @error('nombre')
                                    <small class="text-danger">{{ $message }}</small>
                                @else
                                    <small class="text-muted">
                                        <i class="fas fa-lightbulb me-1"></i>
                                        Usa un nombre claro y descriptivo
                                    </small>
                                @enderror
                                <small class="text-muted">
                                    <span id="nombreCount">0</span>/255
                                </small>
                            </div>
                        </div>

                        {{-- Descripción --}}
                        <div class="mb-4">
                            <label for="descripcion" class="form-label fw-bold">
                                Descripción
                                <i class="fas fa-question-circle text-muted" 
                                   data-bs-toggle="tooltip" 
                                   data-bs-placement="right" 
                                   title="Descripción detallada del producto (máx. 1000 caracteres)"></i>
                            </label>
                            <textarea 
                                name="descripcion" 
                                id="descripcion" 
                                class="form-control @error('descripcion') is-invalid @enderror" 
                                rows="4" 
                                placeholder="Describe el producto, ingredientes, características especiales..."
                                maxlength="1000"
                            >{{ old('descripcion', $producto->descripcion) }}</textarea>
                            <div class="d-flex justify-content-between mt-1">
                                @error('descripcion')
                                    <small class="text-danger">{{ $message }}</small>
                                @else
                                    <small class="text-muted">
                                        <i class="fas fa-lightbulb me-1"></i>
                                        Opcional pero recomendado
                                    </small>
                                @enderror
                                <small class="text-muted">
                                    <span id="descripcionCount">0</span>/1000
                                </small>
                            </div>
                        </div>

                        {{-- Categoría --}}
                        <div class="mb-4">
                            <label for="categoria" class="form-label fw-bold">
                                Categoría
                                <i class="fas fa-question-circle text-muted" 
                                   data-bs-toggle="tooltip" 
                                   data-bs-placement="right" 
                                   title="Selecciona la categoría del producto"></i>
                            </label>
                            <select 
                                name="categoria" 
                                id="categoria" 
                                class="form-select @error('categoria') is-invalid @enderror"
                            >
                                <option value="">-- Seleccionar categoría --</option>
                                <option value="comidas" {{ old('categoria', $producto->categoria) == 'comidas' ? 'selected' : '' }}>
                                    🍽️ Comidas
                                </option>
                                <option value="bebidas" {{ old('categoria', $producto->categoria) == 'bebidas' ? 'selected' : '' }}>
                                    🥤 Bebidas
                                </option>
                                <option value="postres" {{ old('categoria', $producto->categoria) == 'postres' ? 'selected' : '' }}>
                                    🍰 Postres
                                </option>
                                <option value="snacks" {{ old('categoria', $producto->categoria) == 'snacks' ? 'selected' : '' }}>
                                    🍿 Snacks
                                </option>
                                <option value="otros" {{ old('categoria', $producto->categoria) == 'otros' ? 'selected' : '' }}>
                                    📦 Otros
                                </option>
                            </select>
                            @error('categoria')
                                <small class="text-danger">{{ $message }}</small>
                            @else
                                <small class="text-muted">
                                    <i class="fas fa-lightbulb me-1"></i>
                                    Campo opcional - ayuda a organizar tus productos
                                </small>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- SECCIÓN: PRECIO E INVENTARIO --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">
                            <i class="fas fa-dollar-sign text-success me-2"></i>
                            Precio e Inventario
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            {{-- Precio --}}
                            <div class="col-md-6 mb-4">
                                <label for="precio" class="form-label fw-bold">
                                    Precio de Venta <span class="text-danger">*</span>
                                    <i class="fas fa-question-circle text-muted" 
                                       data-bs-toggle="tooltip" 
                                       data-bs-placement="right" 
                                       title="Precio en soles (S/) - Máx: S/ 999,999.99"></i>
                                </label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light">
                                        <i class="fas fa-dollar-sign text-success"></i>
                                    </span>
                                    <input 
                                        type="number" 
                                        name="precio" 
                                        id="precio" 
                                        class="form-control @error('precio') is-invalid @enderror" 
                                        value="{{ old('precio', $producto->precio) }}" 
                                        placeholder="0.00"
                                        step="0.01"
                                        min="0"
                                        max="999999.99"
                                        required
                                    >
                                    <span class="input-group-text bg-light">S/</span>
                                </div>
                                @error('precio')
                                    <small class="text-danger">{{ $message }}</small>
                                @else
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Precio actual: <strong>S/ {{ number_format($producto->precio, 2) }}</strong>
                                    </small>
                                @enderror
                            </div>

                            {{-- Stock --}}
                            <div class="col-md-6 mb-4">
                                <label for="stock" class="form-label fw-bold">
                                    Stock Disponible <span class="text-danger">*</span>
                                    <i class="fas fa-question-circle text-muted" 
                                       data-bs-toggle="tooltip" 
                                       data-bs-placement="right" 
                                       title="Cantidad disponible en inventario (0 - 999,999)"></i>
                                </label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light">
                                        <i class="fas fa-boxes text-warning"></i>
                                    </span>
                                    <input 
                                        type="number" 
                                        name="stock" 
                                        id="stock" 
                                        class="form-control @error('stock') is-invalid @enderror" 
                                        value="{{ old('stock', $producto->stock) }}" 
                                        placeholder="0"
                                        min="0"
                                        max="999999"
                                        required
                                    >
                                    <span class="input-group-text bg-light">und.</span>
                                </div>
                                @error('stock')
                                    <small class="text-danger">{{ $message }}</small>
                                @else
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Stock actual: <strong>{{ $producto->stock }} unidades</strong>
                                    </small>
                                @enderror
                            </div>

                            {{-- Stock Mínimo --}}
                            <div class="col-md-6 mb-4">
                                <label for="stock_minimo" class="form-label fw-bold">
                                    Stock Mínimo
                                    <i class="fas fa-question-circle text-muted"
                                       data-bs-toggle="tooltip"
                                       data-bs-placement="right"
                                       title="Cantidad mínima para alertas de stock bajo (0 desactiva la alerta)"></i>
                                </label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light">
                                        <i class="fas fa-bell text-danger"></i>
                                    </span>
                                    <input
                                        type="number"
                                        name="stock_minimo"
                                        id="stock_minimo"
                                        class="form-control @error('stock_minimo') is-invalid @enderror"
                                        value="{{ old('stock_minimo', $producto->stock_minimo) }}"
                                        placeholder="0"
                                        min="0"
                                        max="999999"
                                    >
                                    <span class="input-group-text bg-light">und.</span>
                                </div>
                                @error('stock_minimo')
                                    <small class="text-danger">{{ $message }}</small>
                                @else
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Mínimo actual: <strong>{{ $producto->stock_minimo }} unidades</strong>
                                    </small>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SECCIÓN: CÓDIGOS DE IDENTIFICACIÓN --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">
                            <i class="fas fa-barcode text-info me-2"></i>
                            Códigos de Identificación
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            {{-- Código de Barras --}}
                            <div class="col-md-6 mb-4">
                                <label for="codigo_barras" class="form-label fw-bold">
                                    Código de Barras
                                    <i class="fas fa-question-circle text-muted" 
                                       data-bs-toggle="tooltip" 
                                       data-bs-placement="right" 
                                       title="Código de barras del producto (máx. 50 caracteres)"></i>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">
                                        <i class="fas fa-barcode text-info"></i>
                                    </span>
                                    <input 
                                        type="text" 
                                        name="codigo_barras" 
                                        id="codigo_barras" 
                                        class="form-control @error('codigo_barras') is-invalid @enderror" 
                                        value="{{ old('codigo_barras', $producto->codigo_barras) }}" 
                                        placeholder="Ej: 7501234567890"
                                        maxlength="50"
                                    >
                                </div>
                                @error('codigo_barras')
                                    <small class="text-danger">{{ $message }}</small>
                                @else
                                    <small class="text-muted">
                                        <i class="fas fa-lightbulb me-1"></i>
                                        {{ $producto->codigo_barras ? 'Actual: ' . $producto->codigo_barras : 'Sin código de barras' }}
                                    </small>
                                @enderror
                            </div>

                            {{-- SKU --}}
                            <div class="col-md-6 mb-4">
                                <label for="sku" class="form-label fw-bold">
                                    SKU (Stock Keeping Unit)
                                    <i class="fas fa-question-circle text-muted" 
                                       data-bs-toggle="tooltip" 
                                       data-bs-placement="right" 
                                       title="Código único de identificación (máx. 50 caracteres)"></i>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">
                                        <i class="fas fa-hashtag text-info"></i>
                                    </span>
                                    <input 
                                        type="text" 
                                        name="sku" 
                                        id="sku" 
                                        class="form-control @error('sku') is-invalid @enderror" 
                                        value="{{ old('sku', $producto->sku) }}" 
                                        placeholder="Ej: CEV-PESC-001"
                                        maxlength="50"
                                    >
                                    <button 
                                        type="button" 
                                        class="btn btn-outline-secondary" 
                                        id="btnGenerarSKU"
                                        title="Generar SKU automático"
                                    >
                                        <i class="fas fa-magic"></i>
                                    </button>
                                </div>
                                @error('sku')
                                    <small class="text-danger">{{ $message }}</small>
                                @else
                                    <small class="text-muted">
                                        <i class="fas fa-lightbulb me-1"></i>
                                        {{ $producto->sku ? 'Actual: ' . $producto->sku : 'Sin SKU asignado' }}
                                    </small>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ========================================== --}}
            {{-- COLUMNA DERECHA: IMAGEN Y ESTADO --}}
            {{-- ========================================== --}}
            <div class="col-lg-4">
                {{-- SECCIÓN: IMAGEN DEL PRODUCTO --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">
                            <i class="fas fa-image text-primary me-2"></i>
                            Imagen del Producto
                        </h5>
                    </div>
                    <div class="card-body">
                        {{-- Imagen Actual --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Imagen Actual</label>
                            <div class="text-center">
                                @php
                                    $imageUrl = $producto->getImagenUrl();
                                @endphp
                                @if($imageUrl)
                                    <div class="position-relative d-inline-block">
                                        <img 
                                            src="{{ $imageUrl }}"
                                            alt="{{ $producto->nombre }}" 
                                            class="img-fluid rounded shadow-sm"
                                            style="max-height: 200px; object-fit: cover;"
                                            id="imagenActual"
                                        >
                                        <button 
                                            type="button" 
                                            class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2" 
                                            id="btnEliminarImagenActual"
                                            title="Eliminar imagen actual"
                                        >
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <input type="hidden" name="eliminar_imagen" id="eliminarImagenInput" value="0">
                                    </div>
                                @else
                                    <div class="bg-secondary bg-opacity-10 rounded p-4">
                                        <i class="fas fa-image fa-3x text-secondary"></i>
                                        <p class="text-muted mb-0 mt-2">Sin imagen</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <hr>

                        {{-- Nueva Imagen --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Cambiar Imagen</label>
                            <div 
                                id="imagenPreview" 
                                class="border border-2 border-dashed rounded-3 p-4 bg-light position-relative"
                                style="min-height: 200px; cursor: pointer;"
                                onclick="document.getElementById('imagen').click()"
                            >
                                <div id="placeholderImagen">
                                    <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3 d-block"></i>
                                    <h6 class="text-muted mb-2">Arrastra aquí o</h6>
                                    <button type="button" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-folder-open me-2"></i>
                                        Seleccionar Nueva Imagen
                                    </button>
                                </div>
                                <img 
                                    id="imagenMostrada" 
                                    class="img-fluid rounded d-none" 
                                    alt="Vista previa" 
                                    style="max-height: 200px; object-fit: cover;"
                                >
                            </div>
                        </div>

                        {{-- Input File (Oculto) --}}
                        <input 
                            type="file" 
                            name="imagen" 
                            id="imagen" 
                            class="d-none @error('imagen') is-invalid @enderror" 
                            accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                        >

                        {{-- Información del Archivo --}}
                        <div id="infoArchivo" class="d-none">
                            <div class="alert alert-info mb-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-file-image me-2"></i>
                                        <span id="nombreArchivo" class="small fw-bold"></span>
                                    </div>
                                    <button 
                                        type="button" 
                                        class="btn btn-sm btn-danger" 
                                        id="btnEliminarImagen"
                                        title="Eliminar nueva imagen"
                                    >
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <small class="d-block mt-1 text-muted">
                                    Tamaño: <span id="tamanoArchivo"></span>
                                </small>
                            </div>
                        </div>

                        @error('imagen')
                            <small class="text-danger d-block mt-2">{{ $message }}</small>
                        @else
                            <small class="text-muted d-block mt-2">
                                <i class="fas fa-info-circle me-1"></i>
                                <strong>Formatos:</strong> JPG, PNG, GIF, WEBP<br>
                                <strong>Tamaño máximo:</strong> 2MB<br>
                                <strong>Nota:</strong> Deja en blanco para mantener la imagen actual
                            </small>
                        @enderror
                    </div>
                </div>

                {{-- SECCIÓN: ESTADO DEL PRODUCTO --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">
                            <i class="fas fa-toggle-on text-success me-2"></i>
                            Estado del Producto
                        </h5>
                    </div>
                    <div class="card-body">
                        <label class="form-label fw-bold">
                            Estado <span class="text-danger">*</span>
                        </label>

                        {{-- Radio Buttons con Tarjetas --}}
                        <div class="d-grid gap-2">
                            <div class="form-check-card">
                                <input 
                                    class="form-check-input d-none" 
                                    type="radio" 
                                    name="estado" 
                                    id="estado_activo" 
                                    value="{{ \App\Enums\ProductoEstado::Active->value }}" 
                                    {{ old('estado', $producto->estado) == 'activo' ? 'checked' : '' }}
                                    required
                                >
                                <label class="form-check-label w-100" for="estado_activo">
                                    <div class="card border estado-card {{ old('estado', $producto->estado) == 'activo' ? 'border-success bg-success bg-opacity-10' : '' }}">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <i class="fas fa-check-circle fa-2x text-success"></i>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-0 fw-bold text-success">Activo</h6>
                                                    <small class="text-muted">Visible y disponible para venta</small>
                                                </div>
                                                <div>
                                                    <i class="fas fa-circle-check text-success {{ old('estado', $producto->estado) == 'activo' ? '' : 'd-none' }}"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>

                            <div class="form-check-card">
                                <input 
                                    class="form-check-input d-none" 
                                    type="radio" 
                                    name="estado" 
                                    id="estado_inactivo" 
                                    value="{{ \App\Enums\ProductoEstado::Inactive->value }}" 
                                    {{ old('estado', $producto->estado) == 'inactivo' ? 'checked' : '' }}
                                >
                                <label class="form-check-label w-100" for="estado_inactivo">
                                    <div class="card border estado-card {{ old('estado', $producto->estado) == 'inactivo' ? 'border-danger bg-danger bg-opacity-10' : '' }}">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <i class="fas fa-times-circle fa-2x text-danger"></i>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-0 fw-bold text-danger">Inactivo</h6>
                                                    <small class="text-muted">Oculto, no disponible para venta</small>
                                                </div>
                                                <div>
                                                    <i class="fas fa-circle-check text-danger {{ old('estado', $producto->estado) == 'inactivo' ? '' : 'd-none' }}"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        @error('estado')
                            <small class="text-danger d-block mt-2">{{ $message }}</small>
                        @else
                            <small class="text-muted d-block mt-2">
                                <i class="fas fa-info-circle me-1"></i>
                                Estado actual: <strong class="text-{{ $producto->estado == 'activo' ? 'success' : 'danger' }}">{{ ucfirst($producto->estado) }}</strong>
                            </small>
                        @enderror
                    </div>
                </div>

                {{-- ACCIONES RÁPIDAS --}}
                <div class="card border-0 bg-light shadow-sm">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">
                            <i class="fas fa-bolt text-warning me-2"></i>
                            Acciones Rápidas
                        </h6>
                        <div class="d-grid gap-2">
                            <a href="{{ route('admin.productos.show', $producto) }}" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-eye me-2"></i>
                                Ver Producto
                            </a>
                            <button 
                                type="button" 
                                class="btn btn-outline-danger btn-sm" 
                                data-bs-toggle="modal" 
                                data-bs-target="#modalEliminar"
                            >
                                <i class="fas fa-trash me-2"></i>
                                Eliminar Producto
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================== --}}
        {{-- BOTONES DE ACCIÓN --}}
        {{-- ========================================== --}}
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Los cambios se aplicarán inmediatamente
                                </small>
                            </div>
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.productos.show', $producto) }}" class="btn btn-lg btn-outline-secondary">
                                    <i class="fas fa-times me-2"></i>
                                    Cancelar
                                </a>
                                <button type="reset" class="btn btn-lg btn-outline-warning" onclick="location.reload();">
                                    <i class="fas fa-undo me-2"></i>
                                    Deshacer Cambios
                                </button>
                                <button type="submit" class="btn btn-lg btn-primary" id="btnActualizar">
                                    <i class="fas fa-save me-2"></i>
                                    Actualizar Producto
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- ========================================== --}}
{{-- MODAL DE CONFIRMACIÓN DE ELIMINACIÓN --}}
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
    /* Tarjetas de estado */
    .estado-card {
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .estado-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
    }

    /* Área de imagen con drag & drop */
    #imagenPreview {
        transition: all 0.3s ease;
    }
    #imagenPreview:hover {
        background-color: #f0f0f0;
        border-color: #0d6efd !important;
    }
    #imagenPreview.dragover {
        background-color: #e7f3ff;
        border-color: #0d6efd !important;
        border-width: 3px !important;
    }

    /* Imagen actual con efecto hover */
    #imagenActual {
        transition: all 0.3s ease;
    }
    #imagenActual:hover {
        transform: scale(1.05);
    }

    /* Animación de fade-in */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .card {
        animation: fadeIn 0.4s ease;
    }
</style>

{{-- ========================================== --}}
{{-- JAVASCRIPT PARA FUNCIONALIDADES --}}
{{-- ========================================== --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // ==========================================
    // CONTADOR DE CARACTERES
    // ==========================================
    const contadores = [
        { input: 'nombre', contador: 'nombreCount', max: 255 },
        { input: 'descripcion', contador: 'descripcionCount', max: 1000 }
    ];

    contadores.forEach(item => {
        const input = document.getElementById(item.input);
        const contador = document.getElementById(item.contador);
        
        if (input && contador) {
            // Actualizar al cargar
            contador.textContent = input.value.length;
            
            // Actualizar al escribir
            input.addEventListener('input', function() {
                contador.textContent = this.value.length;
                
                // Cambiar color si se acerca al límite
                if (this.value.length > item.max * 0.9) {
                    contador.classList.add('text-warning');
                } else {
                    contador.classList.remove('text-warning');
                }
            });
        }
    });

    // ==========================================
    // VISTA PREVIA DE NUEVA IMAGEN
    // ==========================================
    const imagenInput = document.getElementById('imagen');
    const imagenPreview = document.getElementById('imagenPreview');
    const imagenMostrada = document.getElementById('imagenMostrada');
    const placeholderImagen = document.getElementById('placeholderImagen');
    const infoArchivo = document.getElementById('infoArchivo');
    const nombreArchivo = document.getElementById('nombreArchivo');
    const tamanoArchivo = document.getElementById('tamanoArchivo');

    imagenInput.addEventListener('change', function(e) {
        mostrarVistaPrevia(this.files[0]);
    });

    function mostrarVistaPrevia(file) {
        if (file) {
            // Validar tipo de archivo
            const tiposPermitidos = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
            if (!tiposPermitidos.includes(file.type)) {
                alert('❌ Formato no válido. Solo se permiten: JPG, PNG, GIF, WEBP');
                imagenInput.value = '';
                return;
            }

            // Validar tamaño (2MB)
            if (file.size > 2048 * 1024) {
                alert('❌ El archivo es demasiado grande. Máximo 2MB');
                imagenInput.value = '';
                return;
            }

            // Mostrar vista previa
            const reader = new FileReader();
            reader.onload = function(e) {
                imagenMostrada.src = e.target.result;
                imagenMostrada.classList.remove('d-none');
                placeholderImagen.classList.add('d-none');
                
                // Mostrar info del archivo
                nombreArchivo.textContent = file.name;
                tamanoArchivo.textContent = formatBytes(file.size);
                infoArchivo.classList.remove('d-none');
            };
            reader.readAsDataURL(file);
        }
    }

    // Drag & Drop
    imagenPreview.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('dragover');
    });

    imagenPreview.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
    });

    imagenPreview.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
        
        const file = e.dataTransfer.files[0];
        if (file) {
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            imagenInput.files = dataTransfer.files;
            
            mostrarVistaPrevia(file);
        }
    });

    // Botón eliminar nueva imagen
    document.getElementById('btnEliminarImagen')?.addEventListener('click', function() {
        imagenInput.value = '';
        imagenMostrada.src = '';
        imagenMostrada.classList.add('d-none');
        placeholderImagen.classList.remove('d-none');
        infoArchivo.classList.add('d-none');
    });

    // ==========================================
    // ELIMINAR IMAGEN ACTUAL
    // ==========================================
    document.getElementById('btnEliminarImagenActual')?.addEventListener('click', function() {
        if (confirm('¿Estás seguro de eliminar la imagen actual? Esta acción no se puede deshacer.')) {
            document.getElementById('eliminarImagenInput').value = '1';
            document.getElementById('imagenActual').style.opacity = '0.3';
            this.remove();
            
            // Mostrar notificación
            const toast = document.createElement('div');
            toast.className = 'alert alert-warning alert-dismissible fade show position-fixed top-0 end-0 m-3';
            toast.style.zIndex = '9999';
            toast.innerHTML = `
                <i class="fas fa-exclamation-triangle me-2"></i>
                Imagen marcada para eliminación. <strong>Guarda los cambios</strong> para aplicar.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 5000);
        }
    });

    // ==========================================
    // GENERAR SKU AUTOMÁTICO
    // ==========================================
    document.getElementById('btnGenerarSKU')?.addEventListener('click', function() {
        const nombre = document.getElementById('nombre').value;
        const categoria = document.getElementById('categoria').value;
        
        if (!nombre) {
            alert('⚠️ Por favor ingresa el nombre del producto primero');
            document.getElementById('nombre').focus();
            return;
        }

        // Generar SKU
        const nombreCorto = nombre.substring(0, 3).toUpperCase().replace(/\s/g, '');
        const categoriaCorto = categoria ? categoria.substring(0, 3).toUpperCase() : 'GEN';
        const timestamp = Date.now().toString().slice(-4);
        
        const sku = `${nombreCorto}-${categoriaCorto}-${timestamp}`;
        document.getElementById('sku').value = sku;

        // Mostrar notificación
        const toast = document.createElement('div');
        toast.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-3';
        toast.style.zIndex = '9999';
        toast.innerHTML = `
            <i class="fas fa-check-circle me-2"></i>
            SKU generado: <strong>${sku}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    });

    // ==========================================
    // RADIO BUTTONS DE ESTADO
    // ==========================================
    document.querySelectorAll('input[name="estado"]').forEach(radio => {
        radio.addEventListener('change', function() {
            // Remover clases de todas las tarjetas
            document.querySelectorAll('.estado-card').forEach(card => {
                card.classList.remove('border-success', 'bg-success', 'bg-opacity-10', 'border-danger', 'bg-danger');
            });

            // Ocultar todos los checks
            document.querySelectorAll('.estado-card i.fa-circle-check').forEach(icon => {
                icon.classList.add('d-none');
            });

            // Agregar clases a la tarjeta seleccionada
            const label = this.parentElement.querySelector('.estado-card');
            const icon = label.querySelector('i.fa-circle-check');
            
            if (this.value === 'activo') {
                label.classList.add('border-success', 'bg-success', 'bg-opacity-10');
            } else {
                label.classList.add('border-danger', 'bg-danger', 'bg-opacity-10');
            }
            
            icon.classList.remove('d-none');
        });
    });

    // ==========================================
    // VALIDACIÓN ANTES DE ENVIAR
    // ==========================================
    document.getElementById('formProducto').addEventListener('submit', function(e) {
        const btnActualizar = document.getElementById('btnActualizar');
        
        // Deshabilitar botón para evitar doble envío
        btnActualizar.disabled = true;
        btnActualizar.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Actualizando...';
    });

    // ==========================================
    // TOOLTIPS DE BOOTSTRAP
    // ==========================================
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // ==========================================
    // FUNCIÓN AUXILIAR: FORMATEAR BYTES
    // ==========================================
    function formatBytes(bytes, decimals = 2) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }
});
</script>
@endpush
@endsection
