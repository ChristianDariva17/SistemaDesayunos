@extends('layouts.app')

@section('title', 'Nuevo Empleado')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.empleados.index') }}">Empleados</a></li>
    <li class="breadcrumb-item active">Nuevo Empleado</li>
@endsection

@section('content')

{{-- ==========================================
    ALERTAS DE VALIDACIÓN
    ========================================== --}}
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show shadow-sm animate__animated animate__fadeInDown" role="alert">
        <div class="d-flex align-items-start">
            <i class="fas fa-exclamation-circle fa-2x me-3"></i>
            <div class="flex-grow-1">
                <h5 class="alert-heading mb-2">
                    <i class="fas fa-exclamation-triangle me-2"></i> Errores de Validación
                </h5>
                <p class="mb-2">Por favor corrige los siguientes errores:</p>
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
@endif

{{-- ==========================================
    PAGE HEADER
    ========================================== --}}
<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="page-title mb-2">
                <i class="fas fa-user-plus text-primary"></i> Registrar Nuevo Empleado
            </h1>
            <p class="page-subtitle text-muted mb-0">
                <i class="fas fa-info-circle me-1"></i> Completa los datos del nuevo miembro del equipo
            </p>
        </div>
        <div class="col-auto">
            <a href="{{ route('admin.empleados.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i> Volver
            </a>
        </div>
    </div>
</div>

{{-- ==========================================
    FORMULARIO
    ========================================== --}}
<form action="{{ route('admin.empleados.store') }}" method="POST" id="empleadoForm">
    @csrf
    
    <div class="row g-4">
        {{-- ==========================================
            COLUMNA IZQUIERDA - FORMULARIO
            ========================================== --}}
        <div class="col-lg-8">
            
            {{-- Información Básica --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="fas fa-user text-primary me-2"></i> Información Básica
                        <span class="badge bg-danger ms-2">Obligatorio</span>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        {{-- Nombre --}}
                        <div class="col-12">
                            <label for="nombre" class="form-label fw-semibold required">
                                <i class="fas fa-user text-muted"></i> Nombre Completo
                            </label>
                            <input type="text"
                                   class="form-control form-control-lg @error('nombre') is-invalid @enderror"
                                   id="nombre"
                                   name="nombre"
                                   value="{{ old('nombre') }}"
                                   placeholder="Ej: Juan Carlos Pérez García"
                                   maxlength="255"
                                   required>
                            @error('nombre')
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-circle me-1"></i> {{ $message }}
                                </div>
                            @enderror
                            <div class="form-text d-flex justify-content-between">
                                <span>
                                    <i class="fas fa-info-circle me-1"></i> Ingresa el nombre completo del empleado
                                </span>
                                <span id="charCount" class="text-muted">0/255</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Rol y Estado --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="fas fa-briefcase text-primary me-2"></i> Función y Estado
                        <span class="badge bg-danger ms-2">Obligatorio</span>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        {{-- Rol --}}
                        <div class="col-12">
                            <label for="rol_operativo" class="form-label fw-semibold required">
                                <i class="fas fa-user-tag text-muted"></i> Rol del Empleado
                            </label>
                            <select class="form-select form-select-lg @error('rol_operativo') is-invalid @enderror"
                                    id="rol_operativo"
                                    name="rol_operativo"
                                    required>
                                <option value="">Selecciona un rol...</option>
                                <option value="mesero" {{ old('rol_operativo') == 'mesero' ? 'selected' : '' }}>
                                    👨‍🍳 Mesero - Atención a mesas
                                </option>
                                <option value="cajero" {{ old('rol_operativo') == 'cajero' ? 'selected' : '' }}>
                                    💰 Cajero - Gestión de pagos
                                </option>
                                <option value="cocinero" {{ old('rol_operativo') == 'cocinero' ? 'selected' : '' }}>
                                    🍳 Cocinero - Preparación de alimentos
                                </option>
                                <option value="chef" {{ old('rol_operativo') == 'chef' ? 'selected' : '' }}>
                                    👨‍🍳 Chef - Jefe de cocina
                                </option>
                                <option value="ayudante" {{ old('rol_operativo') == 'ayudante' ? 'selected' : '' }}>
                                    🤝 Ayudante - Apoyo general
                                </option>
                                <option value="otros" {{ old('rol_operativo') == 'otros' ? 'selected' : '' }}>
                                    🧩 Otros - Función operativa adicional
                                </option>
                            </select>
                            @error('rol_operativo')
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-circle me-1"></i> {{ $message }}
                                </div>
                            @enderror
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i> Define la función del empleado en el restaurante
                            </div>
                        </div>

                        {{-- Estado --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold required d-block mb-3">
                                <i class="fas fa-toggle-on text-muted"></i> Estado del Empleado
                            </label>
                            <div class="row g-3">
                                {{-- Activo --}}
                                <div class="col-md-6">
                                    <div class="form-check-card {{ old('estado', 'activo') == 'activo' ? 'active' : '' }}">
                                        <input class="form-check-input" 
                                               type="radio" 
                                               name="estado" 
                                               id="estado_activo" 
                                               value="activo"
                                               {{ old('estado', 'activo') == 'activo' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100" for="estado_activo">
                                            <div class="text-center">
                                                <div class="icon-circle bg-success mb-2">
                                                    <i class="fas fa-check-circle fa-2x"></i>
                                                </div>
                                                <h6 class="mb-1">✅ Activo</h6>
                                                <small class="text-muted">Puede trabajar ahora</small>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                {{-- Inactivo --}}
                                <div class="col-md-6">
                                    <div class="form-check-card {{ old('estado') == 'inactivo' ? 'active' : '' }}">
                                        <input class="form-check-input" 
                                               type="radio" 
                                               name="estado" 
                                               id="estado_inactivo" 
                                               value="inactivo"
                                               {{ old('estado') == 'inactivo' ? 'checked' : '' }}>
                                        <label class="form-check-label w-100" for="estado_inactivo">
                                            <div class="text-center">
                                                <div class="icon-circle bg-danger mb-2">
                                                    <i class="fas fa-times-circle fa-2x"></i>
                                                </div>
                                                <h6 class="mb-1">❌ Inactivo</h6>
                                                <small class="text-muted">No disponible</small>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            @error('estado')
                                <div class="invalid-feedback d-block mt-2">
                                    <i class="fas fa-exclamation-circle me-1"></i> {{ $message }}
                                </div>
                            @enderror
                            <div class="form-text mt-3">
                                <i class="fas fa-info-circle me-1"></i> Define si el empleado está disponible para trabajar
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Información Adicional --}}
            <div class="card shadow-sm border-0 mb-4 bg-light">
                <div class="card-body">
                    <div class="d-flex align-items-start">
                        <div class="flex-shrink-0">
                            <i class="fas fa-lightbulb fa-2x text-warning"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-2">
                                <i class="fas fa-info-circle me-1"></i> Información Adicional
                            </h6>
                            <p class="text-muted mb-2 small">
                                💡 <strong>Nota:</strong> Los campos de esta sección son opcionales. 
                                Puedes completarlos ahora o editarlos más tarde.
                            </p>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="telefono" class="form-label fw-semibold">
                                        <i class="fas fa-phone text-muted"></i> Teléfono
                                    </label>
                                    <input type="text"
                                           class="form-control @error('telefono') is-invalid @enderror"
                                           id="telefono"
                                           name="telefono"
                                           value="{{ old('telefono') }}"
                                           maxlength="255"
                                           placeholder="Ej: 999 888 777">
                                    @error('telefono')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label for="observaciones" class="form-label fw-semibold">
                                        <i class="fas fa-note-sticky text-muted"></i> Observaciones
                                    </label>
                                    <textarea class="form-control @error('observaciones') is-invalid @enderror"
                                              id="observaciones"
                                              name="observaciones"
                                              rows="4"
                                              placeholder="Observaciones internas opcionales">{{ old('observaciones') }}</textarea>
                                    @error('observaciones')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- ==========================================
            COLUMNA DERECHA - PREVIEW Y ACCIONES
            ========================================== --}}
        <div class="col-lg-4">
            
            {{-- Preview del Empleado --}}
            <div class="card shadow-sm border-0 mb-4 sticky-top" style="top: 20px;">
                <div class="card-header bg-gradient-primary text-white border-0">
                    <h5 class="mb-0">
                        <i class="fas fa-eye me-2"></i> Vista Previa
                    </h5>
                </div>
                <div class="card-body text-center">
                    {{-- Avatar --}}
                    <div class="preview-avatar mx-auto mb-3">
                        <div class="avatar-circle-large" id="previewAvatar">
                            <span class="avatar-initials-large" id="previewInitials">?</span>
                        </div>
                    </div>

                    {{-- Nombre --}}
                    <h5 class="mb-1" id="previewName">Sin nombre</h5>
                    
                    {{-- Rol --}}
                    <div class="mb-3">
                        <span class="badge bg-secondary px-3 py-2" id="previewRole">
                            <i class="fas fa-user me-1"></i> Sin rol asignado
                        </span>
                    </div>

                    {{-- Estado --}}
                    <div class="mb-3">
                        <span class="badge bg-success-soft text-success px-3 py-2" id="previewEstado">
                            <i class="fas fa-check-circle me-1"></i> Activo
                        </span>
                    </div>

                    <hr>

                    {{-- Info adicional --}}
                    <div class="text-start">
                        <small class="text-muted d-block mb-2">
                            <i class="fas fa-calendar-plus me-1"></i> 
                            Se registrará hoy
                        </small>
                        <small class="text-muted d-block">
                            <i class="fas fa-user-shield me-1"></i> 
                            Usuario: <span id="previewUser">-</span>
                        </small>
                    </div>
                </div>
            </div>

            {{-- Botones de Acción --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-save me-2"></i> Registrar Empleado
                        </button>
                        <a href="{{ route('admin.empleados.index') }}" class="btn btn-outline-danger">
                            <i class="fas fa-times me-2"></i> Cancelar
                        </a>
                    </div>
                </div>
            </div>

            {{-- Guía Rápida --}}
            <div class="card shadow-sm border-0 mb-4 bg-light">
                <div class="card-body">
                    <h6 class="mb-3">
                        <i class="fas fa-question-circle text-primary me-2"></i> Guía Rápida
                    </h6>
                    <ul class="small mb-0 ps-3">
                        <li class="mb-2">
                            <strong>Nombre:</strong> Completo del empleado
                        </li>
                        <li class="mb-2">
                            <strong>Rol:</strong> 5 opciones disponibles
                        </li>
                        <li class="mb-2">
                            <strong>Estado:</strong> Activo o Inactivo
                        </li>
                        <li class="mb-0">
                            Todos los campos son <strong class="text-danger">obligatorios</strong>
                        </li>
                    </ul>
                </div>
            </div>

        </div>
    </div>

</form>

{{-- ==========================================
    ESTILOS PERSONALIZADOS
    ========================================== --}}
@push('styles')
<style>
    /* Required asterisk */
    .required::after {
        content: " *";
        color: #e74a3b;
    }

    /* Page Title */
    .page-title {
        font-size: 28px;
        font-weight: 700;
        color: #2d3748;
    }

    .page-subtitle {
        font-size: 14px;
    }

    /* Gradient Header */
    .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    /* Form Check Cards */
    .form-check-card {
        position: relative;
        border: 2px solid #dee2e6;
        border-radius: 12px;
        padding: 20px;
        cursor: pointer;
        transition: all 0.3s ease;
        background: white;
    }

    .form-check-card:hover {
        border-color: var(--primary-color);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }

    .form-check-card.active {
        border-color: var(--primary-color);
        background: rgba(255, 107, 53, 0.05);
        box-shadow: 0 4px 12px rgba(255, 107, 53, 0.2);
    }

    .form-check-card input[type="radio"] {
        position: absolute;
        opacity: 0;
    }

    .form-check-card .form-check-label {
        cursor: pointer;
        margin: 0;
    }

    .form-check-card .icon-circle {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        margin: 0 auto;
    }

    /* Preview Avatar */
    .avatar-circle-large {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        margin: 0 auto;
    }

    .avatar-initials-large {
        font-size: 48px;
        font-weight: 700;
        color: white;
    }

    /* Cards */
    .card {
        transition: all 0.3s ease;
    }

    .card:hover {
        box-shadow: 0 8px 16px rgba(0,0,0,0.1) !important;
    }

    /* Sticky Sidebar */
    .sticky-top {
        position: sticky;
        z-index: 1020;
    }

    /* Badges */
    .bg-success-soft {
        background-color: rgba(40, 167, 69, 0.1);
    }

    /* Form Controls */
    .form-control:focus,
    .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.25rem rgba(255, 107, 53, 0.25);
    }

    .form-control-lg,
    .form-select-lg {
        font-size: 1.05rem;
        padding: 0.75rem 1rem;
    }

    /* Animations */
    .animate__animated {
        animation-duration: 0.5s;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .page-title {
            font-size: 22px;
        }

        .sticky-top {
            position: relative !important;
        }

        .avatar-circle-large {
            width: 100px;
            height: 100px;
        }

        .avatar-initials-large {
            font-size: 40px;
        }
    }
</style>
@endpush

{{-- ==========================================
    SCRIPTS PERSONALIZADOS
    ========================================== --}}
@push('scripts')
<script>
    $(document).ready(function() {
        // ==========================================
        // PREVIEW EN TIEMPO REAL
        // ==========================================

        // Configuración de roles
        const roleConfig = {
            'mesero': { emoji: '👨‍🍳', text: 'Mesero', bg: 'bg-info' },
            'cajero': { emoji: '💰', text: 'Cajero', bg: 'bg-success' },
            'cocinero': { emoji: '🍳', text: 'Cocinero', bg: 'bg-warning' },
            'chef': { emoji: '👨‍🍳', text: 'Chef', bg: 'bg-danger' },
            'ayudante': { emoji: '🤝', text: 'Ayudante', bg: 'bg-secondary' },
            'otros': { emoji: '🧩', text: 'Otros', bg: 'bg-secondary' }
        };

        // Actualizar nombre
        $('#nombre').on('input', function() {
            const nombre = $(this).val().trim();
            const charCount = nombre.length;
            
            // Actualizar contador
            $('#charCount').text(`${charCount}/255`);
            
            if (charCount > 230) {
                $('#charCount').addClass('text-danger').removeClass('text-muted');
            } else {
                $('#charCount').removeClass('text-danger').addClass('text-muted');
            }
            
            // Actualizar preview
            if (nombre) {
                $('#previewName').text(nombre);
                
                // Actualizar iniciales del avatar
                const words = nombre.split(' ');
                let initials = '';
                if (words.length >= 2) {
                    initials = words[0].charAt(0).toUpperCase() + words[1].charAt(0).toUpperCase();
                } else if (words.length === 1) {
                    initials = words[0].charAt(0).toUpperCase();
                }
                $('#previewInitials').text(initials || '?');
                
                // Actualizar usuario sugerido
                const username = nombre.toLowerCase()
                    .split(' ')
                    .slice(0, 2)
                    .join('.')
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '');
                $('#previewUser').text(username);
            } else {
                $('#previewName').text('Sin nombre');
                $('#previewInitials').text('?');
                $('#previewUser').text('-');
            }
        });

        // Actualizar rol
        $('#rol_operativo').on('change', function() {
            const rolOperativo = $(this).val();
            
            if (rolOperativo && roleConfig[rolOperativo]) {
                const config = roleConfig[rolOperativo];
                $('#previewRole')
                    .removeClass('bg-secondary bg-info bg-success bg-warning bg-danger')
                    .addClass(config.bg)
                    .html(`${config.emoji} ${config.text}`);
            } else {
                $('#previewRole')
                    .removeClass('bg-info bg-success bg-warning bg-danger')
                    .addClass('bg-secondary')
                    .html('<i class="fas fa-user me-1"></i> Sin rol asignado');
            }
        });

        // Actualizar estado
        $('input[name="estado"]').on('change', function() {
            const estado = $(this).val();
            
            if (estado === 'activo') {
                $('#previewEstado')
                    .removeClass('bg-danger-soft text-danger')
                    .addClass('bg-success-soft text-success')
                    .html('<i class="fas fa-check-circle me-1"></i> Activo');
            } else {
                $('#previewEstado')
                    .removeClass('bg-success-soft text-success')
                    .addClass('bg-danger-soft text-danger')
                    .html('<i class="fas fa-times-circle me-1"></i> Inactivo');
            }
        });

        // ==========================================
        // EFECTOS EN FORM-CHECK-CARDS
        // ==========================================
        $('input[name="estado"]').on('change', function() {
            $('.form-check-card').removeClass('active');
            $(this).closest('.form-check-card').addClass('active');
        });

        // ==========================================
        // VALIDACIÓN DEL FORMULARIO
        // ==========================================
        $('#empleadoForm').on('submit', function(e) {
            const nombre = $('#nombre').val().trim();
            const rolOperativo = $('#rol_operativo').val();
            const estado = $('input[name="estado"]:checked').val();
            const telefono = $('#telefono').val().trim();
            const observaciones = $('#observaciones').val().trim();

            let errors = [];

            if (!nombre) {
                errors.push('El nombre del empleado es obligatorio');
            }

            if (nombre.length > 255) {
                errors.push('El nombre no puede exceder 255 caracteres');
            }

            if (!rolOperativo) {
                errors.push('Debes seleccionar un rol para el empleado');
            }

            if (telefono.length > 255) {
                errors.push('El teléfono no puede exceder 255 caracteres');
            }

            if (!estado) {
                errors.push('Debes seleccionar el estado del empleado');
            }

            if (errors.length > 0) {
                e.preventDefault();
                
                Swal.fire({
                    icon: 'error',
                    title: 'Errores de validación',
                    html: '<ul class="text-start mb-0">' + 
                          errors.map(err => `<li>${err}</li>`).join('') + 
                          '</ul>',
                    confirmButtonColor: '#e74a3b'
                });
                
                return false;
            }

            // Confirmación antes de enviar
            e.preventDefault();
            
            Swal.fire({
                title: '¿Registrar empleado?',
                html: `
                    <div class="text-start">
                        <p class="mb-2">Estás a punto de registrar al empleado:</p>
                        <div class="alert alert-info mb-3">
                            <strong><i class="fas fa-user me-1"></i> ${nombre}</strong><br>
                            <small>${roleConfig[rolOperativo]?.emoji || '👤'} ${roleConfig[rolOperativo]?.text || rolOperativo}</small><br>
                            <small>${estado === 'activo' ? '✅ Activo' : '❌ Inactivo'}</small>
                            ${telefono ? `<br><small><i class="fas fa-phone me-1"></i>${telefono}</small>` : ''}
                            ${observaciones ? `<br><small><i class="fas fa-note-sticky me-1"></i>${observaciones}</small>` : ''}
                        </div>
                        <p class="text-muted small mb-0">
                            <i class="fas fa-info-circle me-1"></i>
                            Podrás editar esta información más tarde.
                        </p>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-save me-2"></i> Sí, registrar',
                cancelButtonText: '<i class="fas fa-times me-2"></i> Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Registrando empleado...',
                        text: 'Por favor espera',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $('#empleadoForm').off('submit').submit();
                }
            });
        });

        // ==========================================
        // AUTO-CLOSE ALERTS
        // ==========================================
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);

        // ==========================================
        // FOCUS EN PRIMER CAMPO
        // ==========================================
        $('#nombre').focus();
    });
</script>
@endpush

@endsection
