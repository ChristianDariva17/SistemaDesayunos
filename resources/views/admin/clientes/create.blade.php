@extends('layouts.app')

@section('title', 'Nuevo Cliente - Sistema Dariva')

@section('breadcrumb')
        <li class="breadcrumb-item"><a href="{{ route('admin.clientes.index') }}">Clientes</a></li>
        <li class="breadcrumb-item active" aria-current="page">Nuevo Cliente</li>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <!-- ============================================ -->
            <!-- ENCABEZADO -->
            <!-- ============================================ -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-1">
                                <i class="fas fa-user-plus text-primary me-2"></i>
                                Nuevo Cliente
                            </h2>
                            <p class="text-muted mb-0">
                                Complete la información del cliente. Los campos marcados con 
                                <span class="text-danger">*</span> son obligatorios.
                            </p>
                        </div>
                        <a href="{{ route('admin.clientes.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>
                            Volver al Listado
                        </a>
                    </div>
                </div>
            </div>

            <!-- ============================================ -->
            <!-- ALERTAS DE ERROR GLOBAL -->
            <!-- ============================================ -->
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <h5 class="alert-heading">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Por favor corrige los siguientes errores:
                    </h5>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- ============================================ -->
            <!-- ALERTAS DE ÉXITO/ERROR -->
            <!-- ============================================ -->
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-times-circle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- ============================================ -->
            <!-- FORMULARIO PRINCIPAL -->
            <!-- ============================================ -->
            <form action="{{ route('admin.clientes.store') }}" method="POST" id="formCliente">
                @csrf

                <!-- ============================================ -->
                <!-- 1. INFORMACIÓN BÁSICA -->
                <!-- ============================================ -->
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-user me-2"></i>
                            Información Básica
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Nombre (OBLIGATORIO) -->
                            <div class="col-md-6 mb-3">
                                <label for="nombre" class="form-label fw-bold">
                                    Nombre <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       name="nombre" 
                                       id="nombre" 
                                       class="form-control @error('nombre') is-invalid @enderror"
                                       value="{{ old('nombre') }}"
                                       maxlength="255"
                                       required
                                       placeholder="Ej: Juan"
                                       autofocus>
                                @error('nombre')
                                    <div class="invalid-feedback">
                                        <i class="fas fa-exclamation-circle me-1"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Apellido (OPCIONAL) -->
                            <div class="col-md-6 mb-3">
                                <label for="apellido" class="form-label fw-bold">
                                    Apellido
                                </label>
                                <input type="text" 
                                       name="apellido" 
                                       id="apellido" 
                                       class="form-control @error('apellido') is-invalid @enderror"
                                       value="{{ old('apellido') }}"
                                       maxlength="255"
                                       placeholder="Ej: Pérez García">
                                @error('apellido')
                                    <div class="invalid-feedback">
                                        <i class="fas fa-exclamation-circle me-1"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============================================ -->
                <!-- 2. INFORMACIÓN DE CONTACTO -->
                <!-- ============================================ -->
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-address-book me-2"></i>
                            Información de Contacto
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Email (OBLIGATORIO) -->
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label fw-bold">
                                    Correo Electrónico <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-envelope"></i>
                                    </span>
                                    <input type="email" 
                                           name="email" 
                                           id="email" 
                                           class="form-control @error('email') is-invalid @enderror"
                                           value="{{ old('email') }}"
                                           maxlength="255"
                                           required
                                           placeholder="email@ejemplo.com">
                                    @error('email')
                                        <div class="invalid-feedback">
                                            <i class="fas fa-exclamation-circle me-1"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    El email debe ser único en el sistema
                                </small>
                            </div>

                            <!-- Teléfono (OPCIONAL) -->
                            <div class="col-md-6 mb-3">
                                <label for="telefono" class="form-label fw-bold">
                                    Teléfono
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-phone"></i>
                                    </span>
                                    <input type="text" 
                                           name="telefono" 
                                           id="telefono" 
                                           class="form-control @error('telefono') is-invalid @enderror"
                                           value="{{ old('telefono') }}"
                                           maxlength="20"
                                           placeholder="Ej: +51 999 888 777">
                                    @error('telefono')
                                        <div class="invalid-feedback">
                                            <i class="fas fa-exclamation-circle me-1"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Formato válido: números, +, -, ( ), espacios
                                </small>
                            </div>

                            <!-- Dirección (OPCIONAL) -->
                            <div class="col-12 mb-3">
                                <label for="direccion" class="form-label fw-bold">
                                    Dirección Completa
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </span>
                                    <textarea name="direccion" 
                                              id="direccion" 
                                              class="form-control @error('direccion') is-invalid @enderror"
                                              rows="2"
                                              maxlength="500"
                                              placeholder="Ej: Av. Principal 123, Distrito, Ciudad">{{ old('direccion') }}</textarea>
                                    @error('direccion')
                                        <div class="invalid-feedback">
                                            <i class="fas fa-exclamation-circle me-1"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <small class="text-muted">
                                    Máximo 500 caracteres
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============================================ -->
                <!-- 3. INFORMACIÓN ADICIONAL -->
                <!-- ============================================ -->
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            Información Adicional
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Fecha de Nacimiento (OPCIONAL) -->
                            <div class="col-md-6 mb-3">
                                <label for="fecha_nacimiento" class="form-label fw-bold">
                                    Fecha de Nacimiento
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-calendar-alt"></i>
                                    </span>
                                    <input type="date" 
                                           name="fecha_nacimiento" 
                                           id="fecha_nacimiento" 
                                           class="form-control @error('fecha_nacimiento') is-invalid @enderror"
                                           value="{{ old('fecha_nacimiento') }}"
                                           max="{{ now()->format('Y-m-d') }}">
                                    @error('fecha_nacimiento')
                                        <div class="invalid-feedback">
                                            <i class="fas fa-exclamation-circle me-1"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    La fecha no puede ser futura ni mayor a 120 años
                                </small>
                            </div>

                            <!-- Estado (OBLIGATORIO) -->
                            <div class="col-md-6 mb-3">
                                <label for="estado" class="form-label fw-bold">
                                    Estado del Cliente <span class="text-danger">*</span>
                                </label>
                                <select name="estado" 
                                        id="estado" 
                                        class="form-select @error('estado') is-invalid @enderror"
                                        required>
                                    <option value="" disabled {{ old('estado') ? '' : 'selected' }}>
                                        -- Seleccionar estado --
                                    </option>
                                    <option value="activo" 
                                            {{ old('estado') == 'activo' ? 'selected' : '' }}
                                            selected>
                                        ✅ Activo (puede realizar compras)
                                    </option>
                                    <option value="inactivo" 
                                            {{ old('estado') == 'inactivo' ? 'selected' : '' }}>
                                        ⛔ Inactivo (sin acceso)
                                    </option>
                                </select>
                                @error('estado')
                                    <div class="invalid-feedback">
                                        <i class="fas fa-exclamation-circle me-1"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Por defecto se crea como "Activo"
                                </small>
                            </div>

                            <!-- Notas (OPCIONAL) -->
                            <div class="col-12 mb-3">
                                <label for="notas" class="form-label fw-bold">
                                    Notas u Observaciones
                                </label>
                                <textarea name="notas" 
                                          id="notas" 
                                          class="form-control @error('notas') is-invalid @enderror"
                                          rows="3"
                                          maxlength="1000"
                                          placeholder="Observaciones, comentarios o información adicional sobre el cliente...">{{ old('notas') }}</textarea>
                                @error('notas')
                                    <div class="invalid-feedback">
                                        <i class="fas fa-exclamation-circle me-1"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                                <small class="text-muted">
                                    Máximo 1000 caracteres | Caracteres restantes: <span id="notasRestantes">1000</span>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============================================ -->
                <!-- 4. BOTONES DE ACCIÓN -->
                <!-- ============================================ -->
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('admin.clientes.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>
                                Cancelar
                            </a>

                            <div>
                                <button type="reset" class="btn btn-outline-warning me-2">
                                    <i class="fas fa-redo me-2"></i>
                                    Limpiar Formulario
                                </button>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save me-2"></i>
                                    Guardar Cliente
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // ============================================
    // CONTADOR DE CARACTERES PARA NOTAS
    // ============================================
    document.addEventListener('DOMContentLoaded', function() {
        const notasTextarea = document.getElementById('notas');
        const notasRestantes = document.getElementById('notasRestantes');

        if (notasTextarea && notasRestantes) {
            notasTextarea.addEventListener('input', function() {
                const restantes = 1000 - this.value.length;
                notasRestantes.textContent = restantes;

                // Cambiar color según caracteres restantes
                if (restantes < 100) {
                    notasRestantes.classList.add('text-danger');
                    notasRestantes.classList.remove('text-warning', 'text-muted');
                } else if (restantes < 300) {
                    notasRestantes.classList.add('text-warning');
                    notasRestantes.classList.remove('text-danger', 'text-muted');
                } else {
                    notasRestantes.classList.add('text-muted');
                    notasRestantes.classList.remove('text-danger', 'text-warning');
                }
            });
        }
    });

    // ============================================
    // VALIDACIÓN PERSONALIZADA DE TELÉFONO
    // ============================================
    document.getElementById('telefono').addEventListener('input', function(e) {
        // Permitir solo números, +, -, (, ), espacios
        this.value = this.value.replace(/[^0-9+\-\(\)\s]/g, '');
    });

    // ============================================
    // CONFIRMACIÓN AL SALIR SIN GUARDAR
    // ============================================
    let formModified = false;
    const form = document.getElementById('formCliente');

    form.addEventListener('change', function() {
        formModified = true;
    });

    window.addEventListener('beforeunload', function(e) {
        if (formModified) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    // No mostrar advertencia al enviar el formulario
    form.addEventListener('submit', function() {
        formModified = false;
    });
</script>
@endpush