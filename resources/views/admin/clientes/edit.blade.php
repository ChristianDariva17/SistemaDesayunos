@extends('layouts.app')

@section('title', 'Editar Cliente - Sistema Dariva')

@section('breadcrumb')
        <li class="breadcrumb-item"><a href="{{ route('admin.clientes.index') }}">Clientes</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.clientes.show', $cliente) }}">{{ $cliente->nombre }} {{ $cliente->apellido }}</a></li>
        <li class="breadcrumb-item active" aria-current="page">Editar</li>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <!-- ============================================ -->
            <!-- ENCABEZADO CON INFO DEL CLIENTE -->
            <!-- ============================================ -->
            <div class="card shadow-sm mb-4 border-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-1">
                                <i class="fas fa-user-edit text-warning me-2"></i>
                                Editar Cliente
                            </h2>
                            <p class="text-muted mb-0">
                                <i class="fas fa-user me-1"></i>
                                Actualizando datos de: 
                                <strong>{{ $cliente->nombre }} {{ $cliente->apellido }}</strong>
                                <span class="badge bg-{{ $cliente->estado === 'activo' ? 'success' : 'secondary' }} ms-2">
                                    {{ ucfirst($cliente->estado) }}
                                </span>
                            </p>
                            <small class="text-muted">
                                <i class="fas fa-calendar me-1"></i>
                                Cliente desde: {{ $cliente->created_at->format('d/m/Y') }}
                            </small>
                        </div>
                        <div class="text-end">
                            <a href="{{ route('admin.clientes.show', $cliente) }}" class="btn btn-outline-primary me-2">
                                <i class="fas fa-eye me-1"></i>
                                Ver Perfil
                            </a>
                            <a href="{{ route('admin.clientes.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>
                                Volver al Listado
                            </a>
                        </div>
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
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-times-circle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- ============================================ -->
            <!-- FORMULARIO DE EDICIÓN -->
            <!-- ============================================ -->
            <form action="{{ route('admin.clientes.update', $cliente) }}" 
                  method="POST" 
                  id="formEditarCliente">
                @csrf
                @method('PUT')

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
                                       value="{{ old('nombre', $cliente->nombre) }}"
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
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Último cambio: {{ $cliente->updated_at->diffForHumans() }}
                                </small>
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
                                       value="{{ old('apellido', $cliente->apellido) }}"
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
                            <!-- Email (OPCIONAL) -->
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label fw-bold">
                                    Correo Electrónico
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-envelope"></i>
                                    </span>
                                    <input type="email" 
                                           name="email" 
                                           id="email" 
                                           class="form-control @error('email') is-invalid @enderror"
                                           value="{{ old('email', $cliente->email) }}"
                                           maxlength="255"
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
                                    Opcional. Si lo registras, debe ser único en el sistema.
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
                                           value="{{ old('telefono', $cliente->telefono) }}"
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
                                              maxlength="255"
                                              placeholder="Ej: Av. Principal 123, Distrito, Ciudad">{{ old('direccion', $cliente->direccion) }}</textarea>
                                    @error('direccion')
                                        <div class="invalid-feedback">
                                            <i class="fas fa-exclamation-circle me-1"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <small class="text-muted">
                                    Máximo 255 caracteres
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
                                           value="{{ old('fecha_nacimiento', $cliente->fecha_nacimiento ? $cliente->fecha_nacimiento->format('Y-m-d') : '') }}"
                                           max="{{ now()->format('Y-m-d') }}">
                                    @error('fecha_nacimiento')
                                        <div class="invalid-feedback">
                                            <i class="fas fa-exclamation-circle me-1"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                @if($cliente->fecha_nacimiento)
                                    <small class="text-muted">
                                        <i class="fas fa-birthday-cake me-1"></i>
                                        Edad actual: {{ now()->diffInYears($cliente->fecha_nacimiento) }} años
                                    </small>
                                @else
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        La fecha no puede ser futura ni mayor a 120 años
                                    </small>
                                @endif
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
                                    <option value="" disabled>
                                        -- Seleccionar estado --
                                    </option>
                                    <option value="activo" 
                                            {{ old('estado', $cliente->estado) == 'activo' ? 'selected' : '' }}>
                                        ✅ Activo (puede realizar compras)
                                    </option>
                                    <option value="inactivo" 
                                            {{ old('estado', $cliente->estado) == 'inactivo' ? 'selected' : '' }}>
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
                                    Estado actual: 
                                    <span class="badge bg-{{ $cliente->estado === 'activo' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($cliente->estado) }}
                                    </span>
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
                                          placeholder="Observaciones, comentarios o información adicional sobre el cliente...">{{ old('notas', $cliente->notas) }}</textarea>
                                @error('notas')
                                    <div class="invalid-feedback">
                                        <i class="fas fa-exclamation-circle me-1"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                                <small class="text-muted">
                                    Máximo 1000 caracteres | Caracteres restantes: 
                                    <span id="notasRestantes">{{ 1000 - strlen($cliente->notas ?? '') }}</span>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============================================ -->
                <!-- 4. INFORMACIÓN DE AUDITORÍA (Solo lectura) -->
                <!-- ============================================ -->
                <div class="card shadow-sm mb-3 bg-light">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-history me-2"></i>
                            Información de Registro
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <p class="mb-1">
                                    <i class="fas fa-calendar-plus text-primary me-2"></i>
                                    <strong>Fecha de Registro:</strong>
                                </p>
                                <p class="text-muted">
                                    {{ $cliente->created_at->format('d/m/Y H:i:s') }}
                                    <br>
                                    <small>({{ $cliente->created_at->diffForHumans() }})</small>
                                </p>
                            </div>
                            <div class="col-md-4">
                                <p class="mb-1">
                                    <i class="fas fa-calendar-check text-success me-2"></i>
                                    <strong>Última Actualización:</strong>
                                </p>
                                <p class="text-muted">
                                    {{ $cliente->updated_at->format('d/m/Y H:i:s') }}
                                    <br>
                                    <small>({{ $cliente->updated_at->diffForHumans() }})</small>
                                </p>
                            </div>
                            <div class="col-md-4">
                                <p class="mb-1">
                                    <i class="fas fa-hashtag text-info me-2"></i>
                                    <strong>ID del Cliente:</strong>
                                </p>
                                <p class="text-muted">
                                    #{{ $cliente->id }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============================================ -->
                <!-- 5. BOTONES DE ACCIÓN -->
                <!-- ============================================ -->
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <!-- Botones Izquierda -->
                            <div>
                                <a href="{{ route('admin.clientes.show', $cliente) }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>
                                    Cancelar
                                </a>
                                <a href="{{ route('admin.clientes.index') }}" class="btn btn-outline-secondary ms-2">
                                    <i class="fas fa-list me-2"></i>
                                    Ver Listado
                                </a>
                            </div>

                            <!-- Botones Derecha -->
                            <div>
                                <button type="button" 
                                        class="btn btn-outline-info me-2"
                                        onclick="document.getElementById('formEditarCliente').reset();">
                                    <i class="fas fa-undo me-2"></i>
                                    Deshacer Cambios
                                </button>
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-save me-2"></i>
                                    Actualizar Cliente
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <!-- ============================================ -->
            <!-- 6. ACCIONES ADICIONALES -->
            <!-- ============================================ -->
            <div class="card shadow-sm mt-3 border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Zona de Peligro
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Eliminar Cliente</h6>
                            <p class="text-muted mb-0">
                                Esta acción es irreversible. El cliente será eliminado permanentemente.
                                @if($cliente->pedidos()->exists())
                                    <br>
                                    <span class="badge bg-warning text-dark">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        Este cliente tiene {{ $cliente->pedidos()->count() }} pedido(s) asociado(s)
                                    </span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <form action="{{ route('admin.clientes.destroy', $cliente) }}" 
                                  method="POST" 
                                  id="formEliminar"
                                  onsubmit="return confirm('⚠️ ¿Estás seguro de eliminar a {{ $cliente->nombre }} {{ $cliente->apellido }}?\n\nEsta acción NO se puede deshacer.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-trash-alt me-2"></i>
                                    Eliminar Cliente
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
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
            // Calcular restantes iniciales
            const calcularRestantes = () => {
                const restantes = 1000 - notasTextarea.value.length;
                notasRestantes.textContent = restantes;

                // Cambiar color según caracteres restantes
                notasRestantes.classList.remove('text-danger', 'text-warning', 'text-muted');
                if (restantes < 100) {
                    notasRestantes.classList.add('text-danger');
                } else if (restantes < 300) {
                    notasRestantes.classList.add('text-warning');
                } else {
                    notasRestantes.classList.add('text-muted');
                }
            };

            // Actualizar al escribir
            notasTextarea.addEventListener('input', calcularRestantes);

            // Calcular al cargar
            calcularRestantes();
        }
    });

    // ============================================
    // VALIDACIÓN PERSONALIZADA DE TELÉFONO
    // ============================================
    const telefonoInput = document.getElementById('telefono');
    if (telefonoInput) {
        telefonoInput.addEventListener('input', function(e) {
            // Permitir solo números, +, -, (, ), espacios
            this.value = this.value.replace(/[^0-9+\-\(\)\s]/g, '');
        });
    }

    // ============================================
    // CONFIRMACIÓN AL SALIR SIN GUARDAR
    // ============================================
    let formModified = false;
    const form = document.getElementById('formEditarCliente');
    const initialFormData = new FormData(form);

    form.addEventListener('change', function() {
        formModified = true;
    });

    form.addEventListener('input', function() {
        formModified = true;
    });

    window.addEventListener('beforeunload', function(e) {
        if (formModified) {
            e.preventDefault();
            e.returnValue = '¿Seguro que quieres salir? Los cambios no guardados se perderán.';
        }
    });

    // No mostrar advertencia al enviar el formulario
    form.addEventListener('submit', function() {
        formModified = false;
    });

    // ============================================
    // RESALTAR CAMPOS MODIFICADOS
    // ============================================
    const inputs = form.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
        const initialValue = input.value;

        input.addEventListener('change', function() {
            if (this.value !== initialValue) {
                this.classList.add('border-warning');
                this.classList.add('border-2');
            } else {
                this.classList.remove('border-warning');
                this.classList.remove('border-2');
            }
        });
    });

    // ============================================
    // BOTÓN DESHACER CAMBIOS
    // ============================================
    function deshacerCambios() {
        if (confirm('¿Deseas deshacer todos los cambios?')) {
            location.reload();
        }
    }
</script>
@endpush

@push('styles')
<style>
    /* Estilos para campos modificados */
    .border-warning {
        border-width: 2px !important;
    }

    /* Animación suave para alertas */
    .alert {
        animation: slideIn 0.3s ease-out;
    }

    @keyframes slideIn {
        from {
            transform: translateY(-20px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    /* Mejorar apariencia de la zona de peligro */
    .border-danger {
        border-width: 2px;
    }

    /* Efecto hover en botones */
    .btn {
        transition: all 0.3s ease;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
</style>
@endpush