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
    <title>@hasSection('document_title')@yield('document_title')@else{{ config('app.name', 'Laravel') }} - @yield('title', 'Panel de Control')@endif</title>

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

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
            --shadow-sm: 0 2px 4px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.08);
            --shadow-lg: 0 10px 20px rgba(0,0,0,0.12);
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
            background: rgba(0,0,0,0.2);
        }

        #sidebar-wrapper::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.3);
            border-radius: 10px;
        }

        #sidebar-wrapper::-webkit-scrollbar-thumb:hover {
            background: rgba(255,255,255,0.5);
        }

        /* Logo / Brand */
        .sidebar-brand {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            background: rgba(0,0,0,0.2);
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
            color: rgba(255,255,255,0.6);
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
            color: rgba(255,255,255,0.4);
        }

        .sidebar-nav li {
            margin: 0;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: rgba(255,255,255,0.8);
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
            background: rgba(255,255,255,0.08);
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
            border-top: 1px solid rgba(255,255,255,0.1);
            background: rgba(0,0,0,0.2);
            margin-top: auto;
        }

        .sidebar-user {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: rgba(255,255,255,0.05);
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
            color: rgba(255,255,255,0.6);
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

        .breadcrumb-custom .breadcrumb-item + .breadcrumb-item::before {
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
                background: rgba(0,0,0,0.5);
                z-index: 999;
                animation: fadeIn 0.3s ease;
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
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
    <div id="flash-messages"
         class="d-none"
         data-success="{{ session('success') }}"
         data-error="{{ session('error') }}"
         data-warning="{{ session('warning') }}"
         data-info="{{ session('info') }}"></div>

    <div id="wrapper">

        @include('layouts.partials.admin-sidebar')

        {{-- ==========================================
            CONTENIDO PRINCIPAL
            ========================================== --}}
        <div id="page-content-wrapper">
            
            @include('layouts.partials.admin-navbar')

            {{-- Contenido de la Página --}}
            <div class="content-wrapper fade-in">
                @yield('content')
            </div>

        </div>
    </div>

    {{-- Custom Page Scripts --}}
    <template data-run-after-vite="legacy-scripts">
        @stack('scripts')
    </template>
</body>
</html>
