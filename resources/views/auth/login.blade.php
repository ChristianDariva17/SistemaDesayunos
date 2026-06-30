<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    
    {{-- CSRF Token --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    {{-- SEO Meta Tags --}}
    <meta name="description" content="Iniciar sesión en el Sistema Caldos & Desayunos - Gestión de Pedidos">
    <meta name="keywords" content="login, caldos, desayunos, sistema gestión">
    <meta name="author" content="Caldos & Desayunos">
    <meta name="robots" content="noindex, nofollow">
    
    {{-- Favicon --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    
    {{-- Title --}}
    <title>Iniciar Sesión - Caldos & Desayunos</title>
    
    {{-- ============================================
        ESTILOS CSS
    ============================================= --}}
    
    {{-- Bootstrap 5.3 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    {{-- Font Awesome 6 --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    {{-- Animate.css --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    {{-- ============================================
        ESTILOS PERSONALIZADOS
    ============================================= --}}
    <style>
        /* ============================================
           VARIABLES CSS
        ============================================= */
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-color: #1cc88a;
            --danger-color: #e74a3b;
            --warning-color: #f6c23e;
            --info-color: #36b9cc;
            --light-color: #f8f9fc;
            --dark-color: #5a5c69;
            --font-family: 'Poppins', sans-serif;
        }

        /* ============================================
           RESET Y ESTILOS GENERALES
        ============================================= */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-family);
            background: var(--primary-gradient);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            overflow-x: hidden;
        }

        /* ============================================
           BACKGROUND ANIMADO
        ============================================= */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="rgba(255,255,255,0.05)" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,133.3C672,139,768,181,864,181.3C960,181,1056,139,1152,128C1248,117,1344,139,1392,149.3L1440,160L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>');
            background-size: cover;
            opacity: 0.3;
            z-index: 0;
            pointer-events: none;
        }

        /* ============================================
           CONTENEDOR PRINCIPAL
        ============================================= */
        .login-container {
            width: 100%;
            max-width: 480px;
            position: relative;
            z-index: 1;
            animation: fadeInUp 0.6s ease-out;
        }

        /* ============================================
           TARJETA DE LOGIN
        ============================================= */
        .login-card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 25px 70px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            position: relative;
        }

        /* ============================================
           HEADER DE LA TARJETA
        ============================================= */
        .login-header {
            background: var(--primary-gradient);
            color: white;
            padding: 50px 35px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .login-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: rotate 30s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .login-icon {
            width: 90px;
            height: 90px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.25);
            position: relative;
            z-index: 1;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .login-icon i {
            font-size: 40px;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .login-title {
            font-size: 32px;
            font-weight: 800;
            margin: 0;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
            position: relative;
            z-index: 1;
            letter-spacing: -0.5px;
        }

        .login-subtitle {
            font-size: 15px;
            opacity: 0.95;
            margin-top: 8px;
            position: relative;
            z-index: 1;
            font-weight: 400;
        }

        /* ============================================
           CUERPO DE LA TARJETA
        ============================================= */
        .login-body {
            padding: 45px 35px;
            background: white;
        }

        /* ============================================
           ALERTAS
        ============================================= */
        .alert {
            border-radius: 12px;
            border: none;
            margin-bottom: 25px;
            padding: 16px 20px;
            animation: shake 0.5s, fadeIn 0.3s;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
        }

        .alert-danger {
            background: linear-gradient(135deg, #fff5f5 0%, #fee 100%);
            color: #c53030;
            border-left: 4px solid var(--danger-color);
        }

        .alert-success {
            background: linear-gradient(135deg, #f0fff4 0%, #e6ffee 100%);
            color: #22543d;
            border-left: 4px solid var(--success-color);
        }

        .alert i {
            font-size: 20px;
        }

        .alert ul {
            margin: 8px 0 0 0;
            padding-left: 20px;
        }

        /* ============================================
           FORMULARIO
        ============================================= */
        .form-group {
            margin-bottom: 28px;
            position: relative;
        }

        .form-label {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 10px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0;
            z-index: 2;
            font-size: 18px;
            transition: color 0.3s ease;
        }

        .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px 18px 14px 52px;
            font-size: 15px;
            transition: all 0.3s ease;
            background: #f7fafc;
            font-weight: 500;
        }

        .form-control:hover {
            border-color: #cbd5e0;
            background: white;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            background: white;
            outline: none;
        }

        .form-control:focus + .input-icon {
            color: #667eea;
        }

        .form-control.is-invalid {
            border-color: var(--danger-color);
            background: #fff5f5;
        }

        .invalid-feedback {
            display: block;
            margin-top: 8px;
            font-size: 13px;
            color: var(--danger-color);
            font-weight: 500;
        }

        /* ============================================
           CHECKBOX REMEMBER ME
        ============================================= */
        .form-check-wrapper {
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-check-input {
            width: 20px;
            height: 20px;
            border: 2px solid #cbd5e0;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .form-check-input:checked {
            background-color: #667eea;
            border-color: #667eea;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3e%3cpath fill='none' stroke='%23fff' stroke-linecap='round' stroke-linejoin='round' stroke-width='3' d='M6 10l3 3l6-6'/%3e%3c/svg%3e");
        }

        .form-check-label {
            font-size: 14px;
            color: var(--dark-color);
            font-weight: 500;
            cursor: pointer;
            user-select: none;
        }

        /* ============================================
           BOTÓN DE LOGIN
        ============================================= */
        .btn-login {
            width: 100%;
            background: var(--primary-gradient);
            color: white;
            border: none;
            padding: 16px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            transition: all 0.3s ease;
            cursor: pointer;
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            position: relative;
            overflow: hidden;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(102, 126, 234, 0.6);
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .btn-login .spinner-border {
            width: 20px;
            height: 20px;
            border-width: 2px;
        }

        /* ============================================
           FOOTER DE LA TARJETA
        ============================================= */
        .login-footer {
            text-align: center;
            padding: 25px 35px 35px;
            border-top: 2px solid #f1f5f9;
            background: #fafbfc;
        }

        .login-footer p {
            margin: 0;
            font-size: 14px;
            color: #718096;
            font-weight: 500;
        }

        .login-footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 700;
            transition: color 0.3s ease;
        }

        .login-footer a:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        /* ============================================
           ANIMACIONES
        ============================================= */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-8px); }
            20%, 40%, 60%, 80% { transform: translateX(8px); }
        }

        /* ============================================
           BADGE DE VERSIÓN
        ============================================= */
        .version-badge {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: white;
            padding: 10px 18px;
            border-radius: 25px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
            font-size: 13px;
            color: #718096;
            font-weight: 600;
            z-index: 1000;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .version-badge i {
            color: #667eea;
        }

        /* ============================================
           RESPONSIVE
        ============================================= */
        @media (max-width: 576px) {
            .login-container {
                max-width: 100%;
            }

            .login-header {
                padding: 40px 25px 30px;
            }

            .login-body {
                padding: 35px 25px;
            }

            .login-footer {
                padding: 20px 25px 30px;
            }

            .login-title {
                font-size: 26px;
            }

            .login-subtitle {
                font-size: 14px;
            }

            .login-icon {
                width: 75px;
                height: 75px;
            }

            .login-icon i {
                font-size: 35px;
            }

            .version-badge {
                bottom: 15px;
                right: 15px;
                font-size: 12px;
                padding: 8px 14px;
            }
        }
    </style>
</head>
<body>

{{-- ============================================
    CONTENEDOR PRINCIPAL
============================================= --}}
<div class="login-container">
    
    {{-- TARJETA DE LOGIN --}}
    <div class="login-card">
        
        {{-- ============================================
            HEADER
        ============================================= --}}
        <div class="login-header">
            <div class="login-icon">
                <i class="fas fa-utensils"></i>
            </div>
            <h1 class="login-title">Caldos & Desayunos</h1>
            <p class="login-subtitle">Sistema de Gestión de Pedidos</p>
        </div>

        {{-- ============================================
            CUERPO DEL FORMULARIO
        ============================================= --}}
        <div class="login-body">
            
            {{-- MENSAJE DE ÉXITO (Logout correcto) --}}
            @if(session('success'))
                <div class="alert alert-success animate__animated animate__fadeIn" role="alert">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <strong>{{ session('success') }}</strong>
                    </div>
                </div>
            @endif

            {{-- MENSAJE DE ERROR GENERAL --}}
            @if(session('error'))
                <div class="alert alert-danger animate__animated animate__shakeX" role="alert">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>
                        <strong>{{ session('error') }}</strong>
                    </div>
                </div>
            @endif

            {{-- ERRORES DE VALIDACIÓN --}}
            @if($errors->any())
                <div class="alert alert-danger animate__animated animate__shakeX" role="alert">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>
                        <strong>Error en las credenciales:</strong>
                        <ul class="mb-0 mt-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            {{-- FORMULARIO DE LOGIN --}}
            <form action="{{ route('login.post') }}" method="POST" id="loginForm" autocomplete="on">
                @csrf

                {{-- CAMPO EMAIL --}}
                <div class="form-group">
                    <label for="email" class="form-label">
                        <i class="fas fa-envelope"></i>
                        Correo Electrónico
                    </label>
                    <div class="input-wrapper">
                        <i class="fas fa-user input-icon"></i>
                        <input type="email" 
                               name="email" 
                               id="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               placeholder="tu@correo.com" 
                               value="{{ old('email') }}"
                               required 
                               autofocus
                               autocomplete="email">
                    </div>
                    @error('email')
                        <div class="invalid-feedback">
                            <i class="fas fa-times-circle me-1"></i>{{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- CAMPO PASSWORD --}}
                <div class="form-group">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock"></i>
                        Contraseña
                    </label>
                    <div class="input-wrapper">
                        <i class="fas fa-key input-icon"></i>
                        <input type="password" 
                               name="password" 
                               id="password" 
                               class="form-control @error('password') is-invalid @enderror" 
                               placeholder="••••••••" 
                               required
                               autocomplete="current-password">
                    </div>
                    @error('password')
                        <div class="invalid-feedback">
                            <i class="fas fa-times-circle me-1"></i>{{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- CHECKBOX RECORDARME --}}
                <div class="form-check-wrapper">
                    <input type="checkbox" 
                           name="remember" 
                           id="remember" 
                           class="form-check-input"
                           {{ old('remember') ? 'checked' : '' }}>
                    <label for="remember" class="form-check-label">
                        Mantener sesión iniciada
                    </label>
                </div>

                {{-- BOTÓN DE ENVIAR --}}
                <button type="submit" class="btn-login" id="btnLogin">
                    <span id="btnText">Iniciar Sesión</span>
                    <i class="fas fa-arrow-right" id="btnIcon"></i>
                </button>

            </form>

        </div>

        {{-- ============================================
            FOOTER
        ============================================= --}}
        <div class="login-footer">
            <p>
                ¿Olvidaste tu contraseña? 
                <a href="#" onclick="alert('Contacta al administrador del sistema'); return false;">
                    Recuperar acceso
                </a>
            </p>
            <p>
                ¿No tienes una cuenta?
                <a href="{{ route('register') }}">
                    Crear cuenta
                </a>
            </p>
        </div>

    </div>

</div>

{{-- ============================================
    BADGE DE VERSIÓN
============================================= --}}
<div class="version-badge">
    <i class="fas fa-code-branch"></i>
    <span>v1.0.0</span>
</div>

{{-- ============================================
    SCRIPTS JAVASCRIPT
============================================= --}}

{{-- Bootstrap JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

{{-- Scripts personalizados --}}
<script>
    // ============================================
    // ANIMACIÓN DEL BOTÓN AL ENVIAR FORMULARIO
    // ============================================
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        const btn = document.getElementById('btnLogin');
        const btnText = document.getElementById('btnText');
        const btnIcon = document.getElementById('btnIcon');
        
        // Deshabilitar botón
        btn.disabled = true;
        btn.style.cursor = 'not-allowed';
        
        // Cambiar texto e ícono
        btnText.textContent = 'Verificando credenciales...';
        btnIcon.className = 'spinner-border';
    });

    // ============================================
    // AUTO-CERRAR ALERTAS DESPUÉS DE 6 SEGUNDOS
    // ============================================
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => {
                alert.remove();
            }, 500);
        }, 6000);
    });

    // ============================================
    // EFECTO DE FOCUS EN INPUTS
    // ============================================
    const inputs = document.querySelectorAll('.form-control');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            const icon = this.nextElementSibling;
            if (icon && icon.classList.contains('input-icon')) {
                icon.style.color = '#667eea';
                icon.style.transform = 'translateY(-50%) scale(1.1)';
            }
        });
        
        input.addEventListener('blur', function() {
            const icon = this.nextElementSibling;
            if (icon && icon.classList.contains('input-icon')) {
                icon.style.color = '#a0aec0';
                icon.style.transform = 'translateY(-50%) scale(1)';
            }
        });
    });

    // ============================================
    // PREVENIR DOBLE ENVÍO DEL FORMULARIO
    // ============================================
    let formSubmitted = false;
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        if (formSubmitted) {
            e.preventDefault();
            return false;
        }
        formSubmitted = true;
    });

    // ============================================
    // ANIMACIÓN DE ENTRADA DE LA TARJETA
    // ============================================
    window.addEventListener('load', function() {
        const card = document.querySelector('.login-card');
        card.style.transform = 'scale(0.95)';
        setTimeout(() => {
            card.style.transition = 'transform 0.5s ease';
            card.style.transform = 'scale(1)';
        }, 100);
    });
</script>

</body>
</html>
