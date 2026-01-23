<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Caldos & Desayunos</title>
    
    {{-- Bootstrap 5.3 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    {{-- Font Awesome 6 --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .register-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.3);
            overflow: hidden;
            max-width: 500px;
            width: 100%;
        }
        
        .register-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .register-body {
            padding: 2rem;
        }
        
        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
        }
        
        .form-control, .form-select {
            border-radius: 0.5rem;
            border: 2px solid #e9ecef;
            padding: 0.75rem 1rem;
            transition: all 0.3s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-register {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 0.5rem;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(102, 126, 234, 0.4);
        }
        
        .input-group-text {
            background-color: #f8f9fa;
            border: 2px solid #e9ecef;
            border-right: none;
        }
        
        .input-group .form-control {
            border-left: none;
        }
        
        .alert {
            border-radius: 0.5rem;
            border: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-card mx-auto">
            {{-- Header --}}
            <div class="register-header">
                <i class="fas fa-user-plus fa-3x mb-3"></i>
                <h2 class="mb-0">Crear Cuenta</h2>
                <p class="mb-0">Sistema de Gestión - Caldos & Desayunos</p>
            </div>

            {{-- Body --}}
            <div class="register-body">
                {{-- Mensajes de éxito o error --}}
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                {{-- Formulario de Registro --}}
                <form action="{{ route('register.post') }}" method="POST">
                    @csrf

                    {{-- Nombre --}}
                    <div class="mb-3">
                        <label for="name" class="form-label">
                            <i class="fas fa-user me-1"></i>Nombre Completo
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-user"></i>
                            </span>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}"
                                   placeholder="Ej: Juan Pérez"
                                   required
                                   autofocus>
                        </div>
                        @error('name')
                            <div class="text-danger small mt-1">
                                <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div class="mb-3">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope me-1"></i>Correo Electrónico
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-envelope"></i>
                            </span>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}"
                                   placeholder="correo@ejemplo.com"
                                   required>
                        </div>
                        @error('email')
                            <div class="text-danger small mt-1">
                                <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Contraseña --}}
                    <div class="mb-3">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock me-1"></i>Contraseña
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Mínimo 8 caracteres"
                                   required>
                        </div>
                        @error('password')
                            <div class="text-danger small mt-1">
                                <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Confirmar Contraseña --}}
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">
                            <i class="fas fa-lock me-1"></i>Confirmar Contraseña
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" 
                                   class="form-control" 
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   placeholder="Repite la contraseña"
                                   required>
                        </div>
                    </div>

                    {{-- Rol --}}
                    <div class="mb-4">
                        <label for="rol" class="form-label">
                            <i class="fas fa-user-tag me-1"></i>Rol
                        </label>
                        <select class="form-select @error('rol') is-invalid @enderror" 
                                id="rol" 
                                name="rol" 
                                required>
                            <option value="">Seleccione un rol</option>
                            <option value="admin" {{ old('rol') == 'admin' ? 'selected' : '' }}>
                                Administrador
                            </option>
                            <option value="trabajador" {{ old('rol') == 'trabajador' ? 'selected' : '' }}>
                                Trabajador
                            </option>
                        </select>
                        @error('rol')
                            <div class="text-danger small mt-1">
                                <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Botón de Registro --}}
                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-register">
                            <i class="fas fa-user-plus me-2"></i>Registrarse
                        </button>
                    </div>

                    {{-- Link a Login --}}
                    <div class="text-center">
                        <p class="mb-0 text-muted">
                            ¿Ya tienes cuenta? 
                            <a href="{{ route('login') }}" class="text-decoration-none fw-bold">
                                Inicia Sesión
                            </a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
