<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    {{-- CSRF Token --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- SEO Meta Tags --}}
    <meta name="description" content="Sistema de Gestión de Inventario y Ventas - Caldos & Desayunos">
    <meta name="keywords" content="inventario, ventas, productos, pedidos, clientes">
    <meta name="author" content="Caldos & Desayunos">

    {{-- Favicon --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

    {{-- Title --}}
    <title>Dashboard - Caldos & Desayunos</title>

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Bootstrap 5.3 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">

    {{-- Font Awesome 6.5 --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    {{-- Animate.css --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

    {{-- SweetAlert2 --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.3/dist/sweetalert2.min.css">

    {{-- Custom Styles --}}
    <style>
        :root {
            --sidebar-width: 260px;
            --sidebar-collapsed-width: 80px;
            --header-height: 70px;
            --primary-color: #FF6B35;
            --primary-dark: #E85A2A;
            --secondary-color: #004E89;
            --success-color: #1cc88a;
            --danger-color: #e74a3b;
            --warning-color: #f6c23e;
            --info-color: #36b9cc;
            --light-color: #f8f9fc;
            --dark-color: #1a1d20;
            --text-color: #2c3e50;
            --border-color: #e3e6f0;
            --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 10px 20px rgba(0, 0, 0, 0.12);
            --transition-speed: 0.3s;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            font-size: 15px;
            color: var(--text-color);
            background: var(--light-color);
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* ==========================================
           LAYOUT WRAPPER
           ========================================== */
        #wrapper {
            display: flex;
            min-height: 100vh;
            transition: all var(--transition-speed) ease;
        }

        #wrapper.toggled #sidebar-wrapper {
            margin-left: calc(-1 * var(--sidebar-width));
        }

        /* ==========================================
           SIDEBAR
           ========================================== */
        #sidebar-wrapper {
            min-height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #1a1d20 0%, #2c3e50 100%);
            color: white;
            transition: all var(--transition-speed) cubic-bezier(0.4, 0, 0.2, 1);
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1000;
            box-shadow: var(--shadow-lg);
            overflow-y: auto;
            overflow-x: hidden;
        }

        /* Scrollbar personalizado */
        #sidebar-wrapper::-webkit-scrollbar {
            width: 6px;
        }

        #sidebar-wrapper::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.2);
        }

        #sidebar-wrapper::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 10px;
        }

        #sidebar-wrapper::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
        }

        /* Logo / Brand */
        .sidebar-brand {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(0, 0, 0, 0.2);
        }

        .sidebar-brand-icon {
            width: 50px;
            height: 50px;
            background: var(--primary-color);
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
            font-size: 24px;
            box-shadow: 0 4px 12px rgba(255, 107, 53, 0.4);
        }

        .sidebar-brand-text {
            display: block;
            font-size: 18px;
            font-weight: 700;
            color: white;
            text-decoration: none;
            letter-spacing: -0.5px;
        }

        .sidebar-brand-subtext {
            display: block;
            font-size: 11px;
            color: rgba(255, 255, 255, 0.6);
            margin-top: 2px;
            font-weight: 400;
        }

        /* Menú de navegación */
        .sidebar-nav {
            padding: 20px 0;
            list-style: none;
        }

        .nav-heading {
            padding: 15px 20px 8px 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255, 255, 255, 0.4);
        }

        .sidebar-nav li {
            margin: 0;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .sidebar-nav a::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: var(--primary-color);
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }

        .sidebar-nav a:hover {
            background: rgba(255, 255, 255, 0.08);
            color: white;
            padding-left: 28px;
        }

        .sidebar-nav a:hover::before {
            transform: scaleY(1);
        }

        .sidebar-nav a.active {
            background: rgba(255, 107, 53, 0.15);
            color: white;
            border-left: 4px solid var(--primary-color);
            padding-left: 24px;
            font-weight: 600;
        }

        .sidebar-nav a i {
            width: 24px;
            font-size: 18px;
            margin-right: 12px;
            text-align: center;
        }

        .sidebar-nav a span {
            flex: 1;
            font-size: 14px;
            font-weight: 500;
        }

        .badge-sidebar {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }

        /* Footer del Sidebar */
        .sidebar-footer {
            padding: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(0, 0, 0, 0.2);
            margin-top: auto;
        }

        .sidebar-user {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            margin-bottom: 15px;
        }

        .sidebar-user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 16px;
        }

        .sidebar-user-info h6 {
            margin: 0;
            font-size: 14px;
            font-weight: 600;
            color: white;
        }

        .sidebar-user-info small {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.6);
        }

        /* ==========================================
           CONTENIDO PRINCIPAL
           ========================================== */
        #page-content-wrapper {
            flex: 1;
            margin-left: var(--sidebar-width);
            transition: all var(--transition-speed) cubic-bezier(0.4, 0, 0.2, 1);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        #wrapper.toggled #page-content-wrapper {
            margin-left: 0;
        }

        /* ==========================================
           HEADER / NAVBAR
           ========================================== */
        .navbar-custom {
            height: var(--header-height);
            background: white;
            box-shadow: var(--shadow-sm);
            border-bottom: 1px solid var(--border-color);
            padding: 0 30px;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .navbar-custom .navbar-brand {
            font-weight: 700;
            color: var(--dark-color);
            font-size: 20px;
        }

        #sidebarToggle {
            background: var(--light-color);
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            color: var(--text-color);
            font-size: 18px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        #sidebarToggle:hover {
            background: var(--primary-color);
            color: white;
            transform: scale(1.05);
        }

        /* Breadcrumb */
        .breadcrumb-custom {
            background: transparent;
            padding: 0;
            margin: 0;
            font-size: 14px;
        }

        .breadcrumb-custom .breadcrumb-item {
            color: #6c757d;
        }

        .breadcrumb-custom .breadcrumb-item.active {
            color: var(--primary-color);
            font-weight: 600;
        }

        .breadcrumb-custom .breadcrumb-item+.breadcrumb-item::before {
            content: "›";
            font-size: 18px;
            color: #dee2e6;
        }

        /* User Dropdown */
        .user-dropdown {
            position: relative;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid white;
            box-shadow: var(--shadow-sm);
        }

        .user-avatar:hover {
            transform: scale(1.08);
            box-shadow: var(--shadow-md);
        }

        /* ==========================================
           CONTENIDO
           ========================================== */
        .content-wrapper {
            flex: 1;
            padding: 30px;
        }

        /* Page Title */
        .page-header {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--border-color);
        }

        .page-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--dark-color);
            margin: 0;
        }

        .page-subtitle {
            color: #6c757d;
            font-size: 14px;
            margin-top: 5px;
        }

        /* ==========================================
           CARDS Y COMPONENTES
           ========================================== */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            margin-bottom: 24px;
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }

        .card-header {
            background: white;
            border-bottom: 1px solid var(--border-color);
            padding: 20px 24px;
            font-weight: 600;
            font-size: 16px;
            border-radius: 12px 12px 0 0 !important;
        }

        .card-body {
            padding: 24px;
        }

        /* Botones personalizados */
        .btn {
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            border: none;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn-primary {
            background: var(--primary-color);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        /* ==========================================
           RESPONSIVE
           ========================================== */
        @media (max-width: 768px) {
            #sidebar-wrapper {
                margin-left: calc(-1 * var(--sidebar-width));
            }

            #wrapper.toggled #sidebar-wrapper {
                margin-left: 0;
            }

            #page-content-wrapper {
                margin-left: 0;
            }

            .navbar-custom {
                padding: 0 15px;
            }

            .content-wrapper {
                padding: 15px;
            }

            .page-title {
                font-size: 22px;
            }

            /* Overlay cuando sidebar está abierto en mobile */
            #wrapper.toggled::before {
                content: '';
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 999;
                animation: fadeIn 0.3s ease;
            }
        }

        /* ============================================
   ESTILOS ADICIONALES PARA EL DASHBOARD
============================================= */

        /* Bordes de colores para las tarjetas */
        .border-left-primary {
            border-left: 0.35rem solid #4e73df !important;
        }

        .border-left-success {
            border-left: 0.35rem solid #1cc88a !important;
        }

        .border-left-info {
            border-left: 0.35rem solid #36b9cc !important;
        }

        .border-left-warning {
            border-left: 0.35rem solid #f6c23e !important;
        }

        .border-left-danger {
            border-left: 0.35rem solid #e74a3b !important;
        }

        .border-left-secondary {
            border-left: 0.35rem solid #858796 !important;
        }

        /* Colores de texto */
        .text-gray-800 {
            color: #5a5c69 !important;
        }

        .text-gray-300 {
            color: #dddfeb !important;
        }

        .text-xs {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        /* Efectos hover en tarjetas */
        .hover-shadow {
            transition: all 0.3s ease;
        }

        .hover-shadow:hover {
            transform: translateY(-5px);
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175) !important;
        }

        /* Botones de acceso rápido */
        .btn-quick-access {
            transition: all 0.3s ease;
            border-width: 2px;
            text-decoration: none !important;
        }

        .btn-quick-access:hover {
            transform: scale(1.05);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        /* Animaciones de opacidad */
        .opacity-25 {
            opacity: 0.25;
        }


        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        /* ==========================================
           ANIMACIONES Y EFECTOS
           ========================================== */
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        .slide-in-right {
            animation: slideInRight 0.4s ease-out;
        }

        @keyframes slideInRight {
            from {
                transform: translateX(20px);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* Loading Spinner */
        .spinner-border-custom {
            width: 20px;
            height: 20px;
            border-width: 2px;
        }

        /* Tooltip personalizado */
        .tooltip {
            font-size: 12px;
        }

        /* Toast personalizado */
        .toast-container {
            position: fixed;
            top: 90px;
            right: 20px;
            z-index: 9999;
        }
    </style>

    {{-- Custom Page Styles --}}
    @stack('styles')
</head>

<body>
    <div id="wrapper">

        {{-- ==========================================
            SIDEBAR
            ========================================== --}}
        <div id="sidebar-wrapper">
            {{-- Brand / Logo --}}
            <div class="sidebar-brand">
                <div class="sidebar-brand-icon">
                    <i class="fas fa-utensils"></i>
                </div>
                <a href="{{ url('/') }}" class="sidebar-brand-text">
                    Caldos & Desayunos
                </a>
                <span class="sidebar-brand-subtext">Sistema de Gestión</span>
            </div>

            {{-- Navegación --}}
            <ul class="sidebar-nav">
                <li class="nav-heading">Menú Principal</li>

                {{-- Dashboard/Home --}}
                <li>
                    <a href="{{ url('dashboard') }}" class="{{ request()->is('dashboard') ? 'active' : '' }}">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Panel de Control</span>
                    </a>
                </li>

                <li class="nav-heading">Gestión</li>

                {{-- Productos --}}
                <li>
                    <a href="{{ route('admin.productos.index') }}" class="{{ request()->is('productos*') ? 'active' : '' }}">
                        <i class="fas fa-box"></i>
                        <span>Productos</span>
                        @php
                        $stockBajo = \App\Models\Producto::stockBajo()->count();
                            @endphp
                            @if($stockBajo > 0)
                            <span class="badge badge-sidebar bg-warning">{{ $stockBajo }}</span>
                            @endif
                    </a>
                </li>

                {{-- Clientes --}}
                <li>
                    <a href="{{ route('admin.clientes.index') }}" class="{{ request()->is('clientes*') ? 'active' : '' }}">
                        <i class="fas fa-users"></i>
                        <span>Clientes</span>
                    </a>
                </li>

                {{-- Pedidos --}}
                <li>
                    <a href="{{ route('admin.pedidos.index') }}" class="{{ request()->is('pedidos*') ? 'active' : '' }}">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Pedidos</span>
                        @php
                        $pedidosPendientes = \App\Models\Pedido::where('estado', 'pendiente')->count();
                        @endphp
                        @if($pedidosPendientes > 0)
                        <span class="badge badge-sidebar bg-danger">{{ $pedidosPendientes }}</span>
                        @endif
                    </a>
                </li>

                {{-- Empleados --}}
                <li>
                    <a href="{{ route('admin.empleados.index') }}" class="{{ request()->is('empleados*') ? 'active' : '' }}">
                        <i class="fas fa-user-tie"></i>
                        <span>Empleados</span>
                    </a>
                </li>

                <li class="nav-heading">Reportes</li>

                {{-- Reportes --}}
                <li>
                    <a href="{{ route('admin.reportes.index') }}" class="{{ request()->is('reportes*') ? 'active' : '' }}">
                        <i class="fas fa-chart-line"></i>
                        <span>Reportes</span>
                    </a>
                </li>

                <li class="nav-heading">Sistema</li>

                {{-- Configuración --}}
                @auth
                <li>
                    <a href="{{ route('logout') }}"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Cerrar Sesión</span>
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </li>
                @endauth
            </ul>

            {{-- User Info Footer --}}
            @auth
            <div class="sidebar-footer">
                <div class="sidebar-user">
                    <div class="sidebar-user-avatar">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div class="sidebar-user-info">
                        <h6>{{ Auth::user()->name }}</h6>
                        <small>{{ Auth::user()->email }}</small>
                    </div>
                </div>
            </div>
            @endauth
        </div>

        {{-- ==========================================
            CONTENIDO PRINCIPAL
            ========================================== --}}
        <div id="page-content-wrapper">

            {{-- Navbar Superior --}}
            <nav class="navbar navbar-expand-lg navbar-custom">
                <div class="container-fluid">
                    {{-- Toggle Sidebar Button --}}
                    <button id="sidebarToggle" type="button">
                        <i class="fas fa-bars"></i>
                    </button>

                    {{-- Breadcrumb --}}
                    <nav aria-label="breadcrumb" class="ms-3 d-none d-md-block">
                        <ol class="breadcrumb breadcrumb-custom mb-0">
                            <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
                        </ol>
                    </nav>

                    {{-- Right Side Navbar --}}
                    <div class="ms-auto d-flex align-items-center gap-3">
                        {{-- Notificaciones --}}
                        <div class="dropdown">
                            <button class="btn btn-light position-relative" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-bell"></i>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 10px;">
                                    {{ $pedidosPendientes ?? 0 }}
                                </span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <h6 class="dropdown-header">Notificaciones</h6>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="{{ route('admin.pedidos.index') }}">
                                        <i class="fas fa-shopping-cart text-danger"></i>
                                        {{ $pedidosPendientes ?? 0 }} pedidos pendientes
                                    </a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.productos.index') }}">
                                        <i class="fas fa-exclamation-triangle text-warning"></i>
                                        {{ $stockBajo ?? 0 }} productos con stock bajo
                                    </a></li>
                            </ul>
                        </div>

                        {{-- User Dropdown --}}
                        @auth
                        <div class="dropdown user-dropdown">
                            <div class="user-avatar" data-bs-toggle="dropdown" aria-expanded="false">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                <li>
                                    <h6 class="dropdown-header">{{ Auth::user()->name }}</h6>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i> Perfil</a></li>
                                <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i> Configuración</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <a class="dropdown-item text-danger" href="{{ route('logout') }}"
                                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        <i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión
                                    </a>
                                </li>
                            </ul>
                        </div>
                        @endauth
                    </div>
                </div>
            </nav>

            {{-- Contenido de la Página --}}
            <div class="content-wrapper fade-in">
                {{-- ============================================
    CONTENIDO DEL DASHBOARD
============================================= --}}
                <div class="container-fluid py-4">

                    {{-- Encabezado del Dashboard --}}
                    <div class="d-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeInDown">
                        <div>
                            <h1 class="h3 mb-1 text-gray-800">
                                <i class="fas fa-tachometer-alt text-primary me-2"></i>
                                <strong>Dashboard - Panel de Control</strong>
                            </h1>
                            <p class="text-muted mb-0">
                                <i class="far fa-chart-bar me-1"></i>
                                Resumen general del sistema
                            </p>
                        </div>
                        <div class="text-end">
                            <p class="mb-0 text-muted">
                                <i class="far fa-calendar-alt me-2"></i>
                                {{ now()->translatedFormat('l, d \d\e F \d\e Y') }}
                            </p>
                            <p class="mb-0 text-muted small">
                                <i class="far fa-clock me-1"></i>
                                {{ now()->format('h:i A') }}
                            </p>
                        </div>
                    </div>

                    {{-- Bienvenida --}}
                    <div class="alert alert-info alert-dismissible fade show mb-4 animate__animated animate__fadeIn" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-info-circle fa-2x me-3"></i>
                            <div>
                                <h5 class="alert-heading mb-1">
                                    ¡Bienvenido, {{ Auth::user()->name ?? 'Usuario' }}!
                                </h5>
                                <p class="mb-0">
                                    Este es el panel de control del sistema de gestión <strong>Caldos & Desayunos</strong>
                                </p>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>

                    {{-- ============================================
        TARJETAS DE ESTADÍSTICAS - FILA 1
    ============================================= --}}
                    <div class="row g-4 mb-4">

                        {{-- Total Productos --}}
                        <div class="col-xl-3 col-md-6 animate__animated animate__fadeInUp">
                            <div class="card border-left-primary shadow-sm h-100 hover-shadow">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-2">
                                                <i class="fas fa-box me-1"></i>Total Productos
                                            </div>
                                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                                {{ $totalProductos ?? 0 }}
                                            </div>
                                            <small class="text-muted">
                                                Activos: {{ $productosActivos ?? 0 }}
                                            </small>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-box fa-3x text-primary opacity-25"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent border-top-0">
                                    <a href="{{ route('admin.productos.index') }}" class="small text-primary text-decoration-none">
                                        Ver todos <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- Total Clientes --}}
                        <div class="col-xl-3 col-md-6 animate__animated animate__fadeInUp" style="animation-delay: 0.1s;">
                            <div class="card border-left-success shadow-sm h-100 hover-shadow">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-2">
                                                <i class="fas fa-users me-1"></i>Total Clientes
                                            </div>
                                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                                {{ $totalClientes ?? 0 }}
                                            </div>
                                            <small class="text-muted">
                                                Activos: {{ $clientesActivos ?? 0 }}
                                            </small>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-users fa-3x text-success opacity-25"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent border-top-0">
                                    <a href="{{ route('admin.clientes.index') }}" class="small text-success text-decoration-none">
                                        Ver todos <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- Pedidos Pendientes --}}
                        <div class="col-xl-3 col-md-6 animate__animated animate__fadeInUp" style="animation-delay: 0.2s;">
                            <div class="card border-left-warning shadow-sm h-100 hover-shadow">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-2">
                                                <i class="fas fa-clock me-1"></i>Pedidos Pendientes
                                            </div>
                                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                                {{ $pedidosPendientes ?? 0 }}
                                            </div>
                                            <small class="text-muted">
                                                Total: {{ $totalPedidos ?? 0 }}
                                            </small>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-clock fa-3x text-warning opacity-25"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent border-top-0">
                                    <a href="{{ route('admin.pedidos.index') }}" class="small text-warning text-decoration-none">
                                        Ver todos <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- Stock Bajo --}}
                        <div class="col-xl-3 col-md-6 animate__animated animate__fadeInUp" style="animation-delay: 0.3s;">
                            <div class="card border-left-danger shadow-sm h-100 hover-shadow">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-2">
                                                <i class="fas fa-exclamation-triangle me-1"></i>Stock Bajo
                                            </div>
                                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                                {{ $stockBajo ?? 0 }}
                                            </div>
                                            <small class="text-muted">
                                                Productos críticos
                                            </small>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-exclamation-triangle fa-3x text-danger opacity-25"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent border-top-0">
                                    <a href="{{ route('admin.productos.index') }}?stock=bajo" class="small text-danger text-decoration-none">
                                        Ver productos <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- ============================================
        TARJETAS DE ESTADÍSTICAS - FILA 2
    ============================================= --}}
                    <div class="row g-4 mb-4">

                        {{-- Total Ventas --}}
                        <div class="col-xl-3 col-md-6 animate__animated animate__fadeInUp" style="animation-delay: 0.4s;">
                            <div class="card border-left-info shadow-sm h-100 hover-shadow">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-2">
                                                <i class="fas fa-dollar-sign me-1"></i>Total Ventas
                                            </div>
                                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                                S/ {{ number_format($totalVentas ?? 0, 2) }}
                                            </div>
                                            <small class="text-muted">
                                                Pedidos completados
                                            </small>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-dollar-sign fa-3x text-info opacity-25"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Ventas del Mes --}}
                        <div class="col-xl-3 col-md-6 animate__animated animate__fadeInUp" style="animation-delay: 0.5s;">
                            <div class="card border-left-primary shadow-sm h-100 hover-shadow">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-2">
                                                <i class="fas fa-calendar me-1"></i>Ventas del Mes
                                            </div>
                                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                                S/ {{ number_format($ventasMes ?? 0, 2) }}
                                            </div>
                                            <small class="text-muted">
                                                {{ now()->translatedFormat('F Y') }}
                                            </small>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-calendar fa-3x text-primary opacity-25"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Pedidos Completados --}}
                        <div class="col-xl-3 col-md-6 animate__animated animate__fadeInUp" style="animation-delay: 0.6s;">
                            <div class="card border-left-success shadow-sm h-100 hover-shadow">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-2">
                                                <i class="fas fa-check-circle me-1"></i>Pedidos Completados
                                            </div>
                                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                                {{ $pedidosCompletados ?? 0 }}
                                            </div>
                                            <small class="text-muted">
                                                Entregados
                                            </small>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-check-circle fa-3x text-success opacity-25"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Total Empleados --}}
                        <div class="col-xl-3 col-md-6 animate__animated animate__fadeInUp" style="animation-delay: 0.7s;">
                            <div class="card border-left-secondary shadow-sm h-100 hover-shadow">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-2">
                                                <i class="fas fa-user-tie me-1"></i>Total Empleados
                                            </div>
                                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                                {{ $totalEmpleados ?? 0 }}
                                            </div>
                                            <small class="text-muted">
                                                Personal activo
                                            </small>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-user-tie fa-3x text-secondary opacity-25"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- ============================================
        GRÁFICOS Y TABLAS
    ============================================= --}}
                    <div class="row g-4 mb-4">

                        {{-- Productos más vendidos --}}
                        <div class="col-lg-6 animate__animated animate__fadeInLeft">
                            <div class="card shadow-sm h-100">
                                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                    <h6 class="m-0 font-weight-bold">
                                        <i class="fas fa-chart-bar me-2"></i>Top 5 - Productos Más Vendidos
                                    </h6>
                                    <i class="fas fa-star"></i>
                                </div>
                                <div class="card-body">
                                    @if(isset($productosMasVendidos) && $productosMasVendidos->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-hover table-sm mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th width="5%">#</th>
                                                    <th>Producto</th>
                                                    <th class="text-center" width="20%">Cantidad</th>
                                                    <th class="text-end" width="25%">Ingresos</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($productosMasVendidos as $index => $producto)
                                                <tr>
                                                    <td class="text-muted">{{ $index + 1 }}</td>
                                                    <td>
                                                        <strong>{{ $producto->nombre }}</strong>
                                                        <br>
                                                        <small class="text-muted">{{ $producto->categoria ?? 'Sin categoría' }}</small>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-primary">{{ $producto->total_vendido ?? 0 }}</span>
                                                    </td>
                                                    <td class="text-end">
                                                        <strong class="text-success">S/ {{ number_format($producto->ingresos ?? 0, 2) }}</strong>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @else
                                    <div class="text-center py-5">
                                        <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                        <p class="text-muted mb-0">No hay datos de ventas disponibles</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Últimos pedidos --}}
                        <div class="col-lg-6 animate__animated animate__fadeInRight">
                            <div class="card shadow-sm h-100">
                                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                                    <h6 class="m-0 font-weight-bold">
                                        <i class="fas fa-shopping-cart me-2"></i>Últimos 5 Pedidos
                                    </h6>
                                    <i class="fas fa-history"></i>
                                </div>
                                <div class="card-body">
                                    @if(isset($ultimosPedidos) && $ultimosPedidos->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-hover table-sm mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th width="10%">ID</th>
                                                    <th>Cliente</th>
                                                    <th width="20%">Estado</th>
                                                    <th class="text-end" width="20%">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($ultimosPedidos as $pedido)
                                                <tr>
                                                    <td><strong>#{{ $pedido->id }}</strong></td>
                                                    <td>
                                                        {{ $pedido->cliente->nombre ?? 'Cliente no disponible' }}
                                                        <br>
                                                        <small class="text-muted">{{ $pedido->created_at->diffForHumans() }}</small>
                                                    </td>
                                                    <td>
                                                        @if($pedido->estado == 'completado')
                                                        <span class="badge bg-success">
                                                            <i class="fas fa-check me-1"></i>Completado
                                                        </span>
                                                        @elseif($pedido->estado == 'pendiente')
                                                        <span class="badge bg-warning">
                                                            <i class="fas fa-clock me-1"></i>Pendiente
                                                        </span>
                                                        @else
                                                        <span class="badge bg-danger">
                                                            <i class="fas fa-times me-1"></i>Cancelado
                                                        </span>
                                                        @endif
                                                    </td>
                                                    <td class="text-end">
                                                        <strong class="text-success">S/ {{ number_format($pedido->total, 2) }}</strong>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @else
                                    <div class="text-center py-5">
                                        <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                                        <p class="text-muted mb-0">No hay pedidos recientes</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- ============================================
        ACCESOS RÁPIDOS
    ============================================= --}}
                    <div class="row g-4">
                        <div class="col-12 animate__animated animate__fadeInUp" style="animation-delay: 0.8s;">
                            <div class="card shadow-sm">
                                <div class="card-header bg-dark text-white">
                                    <h6 class="m-0 font-weight-bold">
                                        <i class="fas fa-bolt me-2"></i>Accesos Rápidos
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-lg-3 col-md-6">
                                            <a href="{{ route('admin.productos.create') }}"
                                                class="btn btn-outline-primary w-100 py-4 btn-quick-access">
                                                <i class="fas fa-plus-circle fa-3x mb-3 d-block"></i>
                                                <strong>Nuevo Producto</strong>
                                                <br>
                                                <small class="text-muted">Agregar al inventario</small>
                                            </a>
                                        </div>
                                        <div class="col-lg-3 col-md-6">
                                            <a href="{{ route('admin.clientes.create') }}"
                                                class="btn btn-outline-success w-100 py-4 btn-quick-access">
                                                <i class="fas fa-user-plus fa-3x mb-3 d-block"></i>
                                                <strong>Nuevo Cliente</strong>
                                                <br>
                                                <small class="text-muted">Registrar cliente</small>
                                            </a>
                                        </div>
                                        <div class="col-lg-3 col-md-6">
                                            <a href="{{ route('admin.pedidos.create') }}"
                                                class="btn btn-outline-warning w-100 py-4 btn-quick-access">
                                                <i class="fas fa-cart-plus fa-3x mb-3 d-block"></i>
                                                <strong>Nuevo Pedido</strong>
                                                <br>
                                                <small class="text-muted">Crear pedido</small>
                                            </a>
                                        </div>
                                        <div class="col-lg-3 col-md-6">
                                            <a href="{{ route('admin.reportes.index') }}"
                                                class="btn btn-outline-info w-100 py-4 btn-quick-access">
                                                <i class="fas fa-chart-line fa-3x mb-3 d-block"></i>
                                                <strong>Ver Reportes</strong>
                                                <br>
                                                <small class="text-muted">Análisis y estadísticas</small>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                {{-- FIN CONTENIDO DEL DASHBOARD --}}

            </div>

        </div>
    </div>

    {{-- ==========================================
        SCRIPTS
        ========================================== --}}

    {{-- jQuery --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

    {{-- Bootstrap Bundle JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>

    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.3/dist/sweetalert2.all.min.js"></script>

    {{-- Custom Scripts --}}
    <script>
        // ==========================================
        // SIDEBAR TOGGLE
        // ==========================================
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('wrapper').classList.toggle('toggled');

            // Guardar estado en localStorage
            const isToggled = document.getElementById('wrapper').classList.contains('toggled');
            localStorage.setItem('sidebarToggled', isToggled);
        });

        // Restaurar estado del sidebar
        window.addEventListener('DOMContentLoaded', function() {
            const sidebarToggled = localStorage.getItem('sidebarToggled') === 'true';
            if (sidebarToggled) {
                document.getElementById('wrapper').classList.add('toggled');
            }
        });

        // Cerrar sidebar en mobile al hacer clic fuera
        if (window.innerWidth < 768) {
            document.getElementById('page-content-wrapper').addEventListener('click', function() {
                if (document.getElementById('wrapper').classList.contains('toggled')) {
                    document.getElementById('wrapper').classList.remove('toggled');
                }
            });
        }

        // ==========================================
        // TOOLTIPS
        // ==========================================
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // ==========================================
        // CSRF TOKEN PARA AJAX
        // ==========================================
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // ==========================================
        // ALERTA DE SESIÓN
        // ==========================================
        @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: '¡Éxito!',
            text: '{{ session('
            success ') }}',
            timer: 3000,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
        });
        @endif

        @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: '¡Error!',
            text: '{{ session('
            error ') }}',
            timer: 3000,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
        });
        @endif

        @if(session('warning'))
        Swal.fire({
            icon: 'warning',
            title: '¡Advertencia!',
            text: '{{ session('
            warning ') }}',
            timer: 3000,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
        });
        @endif

        @if(session('info'))
        Swal.fire({
            icon: 'info',
            title: 'Información',
            text: '{{ session('
            info ') }}',
            timer: 3000,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
        });
        @endif

        // ==========================================
        // ANIMACIONES AL SCROLL
        // ==========================================
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar-custom');
            if (window.scrollY > 10) {
                navbar.style.boxShadow = '0 4px 12px rgba(0,0,0,0.08)';
            } else {
                navbar.style.boxShadow = '0 2px 4px rgba(0,0,0,0.05)';
            }
        });
    </script>

    {{-- Custom Page Scripts --}}
    @stack('scripts')
</body>

</html>