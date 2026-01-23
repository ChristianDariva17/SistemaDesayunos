@extends('layouts.app')

@section('title', 'Editar Empleado')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.empleados.index') }}">Empleados</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.empleados.show', $empleado) }}">{{ $empleado->name }}</a></li>
    <li class="breadcrumb-item active">Editar</li>
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
                <i class="fas fa-user-edit text-primary"></i> Editar Empleado
            </h1>
            <p class="page-subtitle text-muted mb-0">
                <i class="fas fa-info-circle me-1"></i> Actualiza los datos de <strong>{{ $empleado->name }}</strong>
            </p>
        </div>
        <div class="col-auto">
            <a href="{{ route('admin.empleados.show', $empleado) }}" class="btn btn-outline-secondary me-2">
                <i class="fas fa-eye me-2"></i> Ver Detalles
            </a>
            <a href="{{ route('admin.empleados.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i> Volver
            </a>
        </div>
    </div>
</div>

{{-- ==========================================
    FORMULARIO
    ========================================== --}}
<form action="{{ route('admin.empleados.update', $empleado) }}" method="POST" id="empleadoForm">
    @csrf
    @method('PUT')
    
    <div class="row g-4">
        {{-- ==========================================
            COLUMNA IZQUIERDA - FORMULARIO
            ========================================== --}}
        <div class="col-lg-8">
            
            {{-- Información Básica --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-user text-primary me-2"></i> Información Básica
                            <span class="badge bg-danger ms-2">Obligatorio</span>
                        </h5>
                        <small class="text-muted">
                            <i class="fas fa-clock me-1"></i> Última actualización: {{ $empleado->updated_at->diffForHumans() }}
                        </small>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        {{-- Nombre --}}
                        <div class="col-12">
                            <label for="name" class="form-label fw-semibold required">
                                <i class="fas fa-user text-muted"></i> Nombre Completo
                            </label>
                            <div class="mb-2">
                                <small class="text-muted">
                                    <strong>Nombre actual:</strong> 
                                    <span class="badge bg-light text-dark">{{ $empleado->name }}</span>
                                </small>
                            </div>
                            <input type="text" 
                                   class="form-control form-control-lg @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $empleado->name) }}"
                                   data-original="{{ $empleado->name }}"
                                   placeholder="Ej: Juan Carlos Pérez García"
                                   maxlength="255"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-circle me-1"></i> {{ $message }}
                                </div>
                            @enderror
                            <div class="form-text d-flex justify-content-between">
                                <span id="nameStatus" class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i> Modifica el nombre del empleado
                                </span>
                                <span id="charCount" class="text-muted">{{ strlen($empleado->name) }}/255</span>
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
                            <label for="role" class="form-label fw-semibold required">
                                <i class="fas fa-user-tag text-muted"></i> Rol del Empleado
                            </label>
                            <div class="mb-2">
                                <small class="text-muted">
                                    <strong>Rol actual:</strong> 
                                    <span class="badge bg-light text-dark">
                                        {{ ucfirst($empleado->role ?? 'Sin rol') }}
                                    </span>
                                </small>
                            </div>
                            <select class="form-select form-select-lg @error('role') is-invalid @enderror" 
                                    id="role" 
                                    name="role"
                                    data-original="{{ $empleado->role }}"
                                    required>
                                <option value="">Selecciona un rol...</option>
                                <option value="mesero" {{ old('role', $empleado->role) == 'mesero' ? 'selected' : '' }}>
                                    👨‍🍳 Mesero - Atención a mesas
                                </option>
                                <option value="cajero" {{ old('role', $empleado->role) == 'cajero' ? 'selected' : '' }}>
                                    💰 Cajero - Gestión de pagos
                                </option>
                                <option value="cocinero" {{ old('role', $empleado->role) == 'cocinero' ? 'selected' : '' }}>
                                    🍳 Cocinero - Preparación de alimentos
                                </option>
                                <option value="chef" {{ old('role', $empleado->role) == 'chef' ? 'selected' : '' }}>
                                    👨‍🍳 Chef - Jefe de cocina
                                </option>
                                <option value="ayudante" {{ old('role', $empleado->role) == 'ayudante' ? 'selected' : '' }}>
                                    🤝 Ayudante - Apoyo general
                                </option>
                            </select>
                            @error('role')
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-circle me-1"></i> {{ $message }}
                                </div>
                            @enderror
                            <div class="form-text">
                                <span id="roleStatus" class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i> Actualiza la función del empleado
                                </span>
                            </div>
                        </div>

                        {{-- Estado --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold required d-block mb-3">
                                <i class="fas fa-toggle-on text-muted"></i> Estado del Empleado
                            </label>
                            <div class="mb-2">
                                <small class="text-muted">
                                    <strong>Estado actual:</strong> 
                                    <span class="badge {{ $empleado->estado == 'activo' ? 'bg-success' : 'bg-danger' }}">
                                        {{ ucfirst($empleado->estado) }}
                                    </span>
                                </small>
                            </div>
                            <div class="row g-3">
                                {{-- Activo --}}
                                <div class="col-md-6">
                                    <div class="form-check-card {{ old('estado', $empleado->estado) == 'activo' ? 'active' : '' }}">
                                        <input class="form-check-input" 
                                               type="radio" 
                                               name="estado" 
                                               id="estado_activo" 
                                               value="activo"
                                               data-original="{{ $empleado->estado }}"
                                               {{ old('estado', $empleado->estado) == 'activo' ? 'checked' : '' }}
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
                                    <div class="form-check-card {{ old('estado', $empleado->estado) == 'inactivo' ? 'active' : '' }}">
                                        <input class="form-check-input" 
                                               type="radio" 
                                               name="estado" 
                                               id="estado_inactivo" 
                                               value="inactivo"
                                               {{ old('estado', $empleado->estado) == 'inactivo' ? 'checked' : '' }}>
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
                                <span id="estadoStatus" class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i> Cambia el estado del empleado
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Información del Registro --}}
            <div class="card shadow-sm border-0 mb-4 bg-light">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="info-box">
                                <div class="d-flex align-items-center">
                                    <div class="icon-circle-small bg-primary me-3">
                                        <i class="fas fa-calendar-plus"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Fecha de Registro</small>
                                        <strong>{{ $empleado->created_at->format('d/m/Y') }}</strong>
                                        <small class="text-muted d-block">{{ $empleado->created_at->format('h:i A') }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <div class="d-flex align-items-center">
                                    <div class="icon-circle-small bg-warning me-3">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Última Actualización</small>
                                        <strong>{{ $empleado->updated_at->format('d/m/Y') }}</strong>
                                        <small class="text-muted d-block">{{ $empleado->updated_at->diffForHumans() }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr class="my-3">
                    <div class="alert alert-info mb-0 small">
                        <i class="fas fa-info-circle me-1"></i>
                        <strong>💡 Nota:</strong> Los cambios que realices se guardarán inmediatamente y 
                        se actualizará la fecha de modificación.
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
                <div class="card-header bg-gradient-warning text-dark border-0">
                    <h5 class="mb-0">
                        <i class="fas fa-eye me-2"></i> Vista Previa
                    </h5>
                </div>
                <div class="card-body text-center">
                    {{-- Avatar --}}
                    <div class="preview-avatar mx-auto mb-3">
                        <div class="avatar-circle-large" id="previewAvatar">
                            <span class="avatar-initials-large" id="previewInitials">
                                {{ strtoupper(substr($empleado->name, 0, 1)) }}
                            </span>
                        </div>
                    </div>

                    {{-- Nombre --}}
                    <h5 class="mb-1" id="previewName">{{ $empleado->name }}</h5>
                    
                    {{-- Rol --}}
                    <div class="mb-3">
                        @php
                            $roleConfig = [
                                'mesero' => ['emoji' => '👨‍🍳', 'bg' => 'bg-info', 'text' => 'Mesero'],
                                'cajero' => ['emoji' => '💰', 'bg' => 'bg-success', 'text' => 'Cajero'],
                                'cocinero' => ['emoji' => '🍳', 'bg' => 'bg-warning', 'text' => 'Cocinero'],
                                'chef' => ['emoji' => '👨‍🍳', 'bg' => 'bg-danger', 'text' => 'Chef'],
                                'ayudante' => ['emoji' => '🤝', 'bg' => 'bg-secondary', 'text' => 'Ayudante'],
                            ];
                            $config = $roleConfig[$empleado->role] ?? ['emoji' => '👤', 'bg' => 'bg-secondary', 'text' => 'Sin rol'];
                        @endphp
                        <span class="badge {{ $config['bg'] }} px-3 py-2" id="previewRole">
                            {{ $config['emoji'] }} {{ $config['text'] }}
                        </span>
                    </div>

                    {{-- Estado --}}
                    <div class="mb-3">
                        <span class="badge {{ $empleado->estado == 'activo' ? 'bg-success-soft text-success' : 'bg-danger-soft text-danger' }} px-3 py-2" id="previewEstado">
                            <i class="fas fa-{{ $empleado->estado == 'activo' ? 'check' : 'times' }}-circle me-1"></i> 
                            {{ ucfirst($empleado->estado) }}
                        </span>
                    </div>

                    <hr>

                    {{-- Detector de cambios --}}
                    <div class="change-detector mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="text-muted">Cambios realizados</small>
                            <span class="badge bg-primary" id="changePercentage">0%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-primary" id="changeProgress" role="progressbar" style="width: 0%"></div>
                        </div>
                        <small class="text-muted d-block mt-2" id="changeText">
                            <i class="fas fa-equals me-1"></i> Sin cambios detectados
                        </small>
                    </div>

                    <hr>

                    {{-- Info adicional --}}
                    <div class="text-start">
                        <small class="text-muted d-block mb-2">
                            <i class="fas fa-calendar-alt me-1"></i> 
                            Registrado: {{ $empleado->created_at->format('d/m/Y') }}
                        </small>
                        <small class="text-muted d-block">
                            <i class="fas fa-clock me-1"></i> 
                            Última edición: {{ $empleado->updated_at->diffForHumans() }}
                        </small>
                    </div>
                </div>
            </div>

            {{-- Botones de Acción --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg" id="btnSave" disabled>
                            <i class="fas fa-save me-2"></i> Guardar Cambios
                        </button>
                        <a href="{{ route('admin.empleados.show', $empleado) }}" class="btn btn-outline-primary">
                            <i class="fas fa-eye me-2"></i> Ver Detalles
                        </a>
                        <a href="{{ route('admin.empleados.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i> Cancelar
                        </a>
                    </div>
                </div>
            </div>

            {{-- Advertencia de Eliminación --}}
            <div class="card shadow-sm border-0 mb-4 border-danger">
                <div class="card-body bg-danger-soft">
                    <h6 class="text-danger mb-3">
                        <i class="fas fa-exclamation-triangle me-2"></i> Zona de Peligro
                    </h6>
                    <p class="small text-muted mb-3">
                        Si necesitas eliminar permanentemente a este empleado, ten en cuenta que 
                        esta acción no se puede deshacer.
                    </p>
                    <button type="button" class="btn btn-danger btn-sm w-100" id="btnDelete">
                        <i class="fas fa-trash me-2"></i> Eliminar Empleado
                    </button>
                </div>
            </div>

        </div>
    </div>

</form>

{{-- Form oculto para eliminar --}}
<form id="delete-form" action="{{ route('admin.empleados.destroy', $empleado) }}" method="POST" class="d-none">
    @csrf
    @method('DELETE')
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
    .bg-gradient-warning {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white !important;
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
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        margin: 0 auto;
    }

    .avatar-initials-large {
        font-size: 48px;
        font-weight: 700;
        color: white;
    }

    /* Icon Circle Small */
    .icon-circle-small {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        flex-shrink: 0;
    }

    /* Info Box */
    .info-box {
        padding: 15px;
        background: white;
        border-radius: 8px;
        border: 1px solid #e9ecef;
    }

    /* Change Detector */
    .change-detector {
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
    }

    /* Danger Soft */
    .bg-danger-soft {
        background-color: rgba(220, 53, 69, 0.05);
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

    /* Changed Input */
    .input-changed {
        border-color: #ffc107 !important;
        background-color: rgba(255, 193, 7, 0.05);
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
        // VALORES ORIGINALES
        // ==========================================
        const originalData = {
            name: $('#name').data('original'),
            role: $('#role').data('original'),
            estado: $('input[name="estado"]').filter('[data-original]').first().data('original')
        };

        // ==========================================
        // CONFIGURACIÓN DE ROLES
        // ==========================================
        const roleConfig = {
            'mesero': { emoji: '👨‍🍳', text: 'Mesero', bg: 'bg-info' },
            'cajero': { emoji: '💰', text: 'Cajero', bg: 'bg-success' },
            'cocinero': { emoji: '🍳', text: 'Cocinero', bg: 'bg-warning' },
            'chef': { emoji: '👨‍🍳', text: 'Chef', bg: 'bg-danger' },
            'ayudante': { emoji: '🤝', text: 'Ayudante', bg: 'bg-secondary' }
        };

        // ==========================================
        // DETECCIÓN DE CAMBIOS
        // ==========================================
        function detectChanges() {
            const currentData = {
                name: $('#name').val().trim(),
                role: $('#role').val(),
                estado: $('input[name="estado"]:checked').val()
            };

            let changedFields = 0;
            const totalFields = 3;

            // Verificar nombre
            if (currentData.name !== originalData.name) {
                changedFields++;
                $('#name').addClass('input-changed');
                $('#nameStatus').html('<i class="fas fa-exclamation-triangle text-warning me-1"></i> Campo modificado');
            } else {
                $('#name').removeClass('input-changed');
                $('#nameStatus').html('<i class="fas fa-info-circle me-1"></i> Modifica el nombre del empleado');
            }

            // Verificar rol
            if (currentData.role !== originalData.role) {
                changedFields++;
                $('#role').addClass('input-changed');
                $('#roleStatus').html('<i class="fas fa-exclamation-triangle text-warning me-1"></i> Campo modificado');
            } else {
                $('#role').removeClass('input-changed');
                $('#roleStatus').html('<i class="fas fa-info-circle me-1"></i> Actualiza la función del empleado');
            }

            // Verificar estado
            if (currentData.estado !== originalData.estado) {
                changedFields++;
                $('#estadoStatus').html('<i class="fas fa-exclamation-triangle text-warning me-1"></i> Campo modificado');
            } else {
                $('#estadoStatus').html('<i class="fas fa-info-circle me-1"></i> Cambia el estado del empleado');
            }

            // Calcular porcentaje
            const percentage = Math.round((changedFields / totalFields) * 100);
            
            // Actualizar UI
            $('#changePercentage').text(percentage + '%');
            $('#changeProgress').css('width', percentage + '%');
            
            if (changedFields === 0) {
                $('#changeText').html('<i class="fas fa-equals me-1"></i> Sin cambios detectados');
                $('#changeProgress').removeClass('bg-warning bg-success').addClass('bg-primary');
                $('#btnSave').prop('disabled', true);
            } else if (changedFields === totalFields) {
                $('#changeText').html('<i class="fas fa-check-circle me-1 text-success"></i> Todos los campos editados');
                $('#changeProgress').removeClass('bg-primary bg-warning').addClass('bg-success');
                $('#btnSave').prop('disabled', false);
            } else {
                $('#changeText').html(`<i class="fas fa-pen me-1 text-warning"></i> ${changedFields} de ${totalFields} campos modificados`);
                $('#changeProgress').removeClass('bg-primary bg-success').addClass('bg-warning');
                $('#btnSave').prop('disabled', false);
            }
        }

        // ==========================================
        // PREVIEW EN TIEMPO REAL
        // ==========================================

        // Actualizar nombre
        $('#name').on('input', function() {
            const name = $(this).val().trim();
            const charCount = name.length;
            
            // Actualizar contador
            $('#charCount').text(`${charCount}/255`);
            
            if (charCount > 230) {
                $('#charCount').addClass('text-danger').removeClass('text-muted');
            } else {
                $('#charCount').removeClass('text-danger').addClass('text-muted');
            }
            
            // Actualizar preview
            if (name) {
                $('#previewName').text(name);
                
                // Actualizar iniciales del avatar
                const words = name.split(' ');
                let initials = '';
                if (words.length >= 2) {
                    initials = words[0].charAt(0).toUpperCase() + words[1].charAt(0).toUpperCase();
                } else if (words.length === 1) {
                    initials = words[0].charAt(0).toUpperCase();
                }
                $('#previewInitials').text(initials || '?');
            }
            
            detectChanges();
        });

        // Actualizar rol
        $('#role').on('change', function() {
            const role = $(this).val();
            
            if (role && roleConfig[role]) {
                const config = roleConfig[role];
                $('#previewRole')
                    .removeClass('bg-secondary bg-info bg-success bg-warning bg-danger')
                    .addClass(config.bg)
                    .html(`${config.emoji} ${config.text}`);
            }
            
            detectChanges();
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
            
            // Actualizar form-check-cards
            $('.form-check-card').removeClass('active');
            $(this).closest('.form-check-card').addClass('active');
            
            detectChanges();
        });

        // ==========================================
        // VALIDACIÓN Y ENVÍO DEL FORMULARIO
        // ==========================================
        $('#empleadoForm').on('submit', function(e) {
            e.preventDefault();
            
            const name = $('#name').val().trim();
            const role = $('#role').val();
            const estado = $('input[name="estado"]:checked').val();

            let errors = [];

            if (!name) {
                errors.push('El nombre del empleado es obligatorio');
            }

            if (name.length > 255) {
                errors.push('El nombre no puede exceder 255 caracteres');
            }

            if (!role) {
                errors.push('Debes seleccionar un rol para el empleado');
            }

            if (!estado) {
                errors.push('Debes seleccionar el estado del empleado');
            }

            if (errors.length > 0) {
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

            // Verificar si hay cambios
            let changedFields = [];
            if (name !== originalData.name) changedFields.push('Nombre');
            if (role !== originalData.role) changedFields.push('Rol');
            if (estado !== originalData.estado) changedFields.push('Estado');

            if (changedFields.length === 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'Sin cambios',
                    text: 'No has realizado ningún cambio en los datos del empleado.',
                    confirmButtonColor: '#6c757d'
                });
                return false;
            }

            // Confirmación antes de actualizar
            Swal.fire({
                title: '¿Actualizar empleado?',
                html: `
                    <div class="text-start">
                        <p class="mb-2">Estás a punto de actualizar los siguientes campos:</p>
                        <div class="alert alert-warning mb-3">
                            <strong><i class="fas fa-user me-1"></i> ${name}</strong><br>
                            <small class="d-block mt-2"><strong>Campos modificados:</strong></small>
                            <ul class="mb-0 mt-1">
                                ${changedFields.map(field => `<li>${field}</li>`).join('')}
                            </ul>
                        </div>
                        <p class="text-muted small mb-0">
                            <i class="fas fa-info-circle me-1"></i>
                            La fecha de última actualización será modificada.
                        </p>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-save me-2"></i> Sí, actualizar',
                cancelButtonText: '<i class="fas fa-times me-2"></i> Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Actualizando empleado...',
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
        // ELIMINAR EMPLEADO
        // ==========================================
        $('#btnDelete').on('click', function() {
            const empleadoName = '{{ $empleado->name }}';

            Swal.fire({
                title: '¿Estás seguro?',
                html: `
                    <div class="text-start">
                        <p class="mb-2">Estás a punto de eliminar al empleado:</p>
                        <div class="alert alert-danger mb-3">
                            <i class="fas fa-user me-2"></i>
                            <strong>${empleadoName}</strong>
                        </div>
                        <p class="text-muted small mb-2">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            Esta acción no se puede deshacer.
                        </p>
                        <p class="text-muted small mb-0">
                            <i class="fas fa-info-circle me-1"></i>
                            Si el empleado tiene pedidos asignados, no podrá ser eliminado.
                        </p>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74a3b',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-trash me-2"></i> Sí, eliminar',
                cancelButtonText: '<i class="fas fa-times me-2"></i> Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Eliminando empleado...',
                        text: 'Por favor espera',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $('#delete-form').submit();
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
        // DETECCIÓN INICIAL
        // ==========================================
        detectChanges();
    });
</script>
@endpush

@endsection
