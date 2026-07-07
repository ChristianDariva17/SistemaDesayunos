<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    {{-- CSRF Token --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- SEO Meta Tags --}}
    <meta name="description" content="Panel de Control - Sistema de Gestión Caldos & Desayunos - Trabajador">
    <meta name="keywords" content="dashboard, panel, control, trabajador, gestión, restaurante">
    <meta name="author" content="Caldos & Desayunos">

    {{-- Favicon --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    {{-- Title --}}
    <title>@yield('title', 'Dashboard Trabajador - Caldos & Desayunos')</title>

    {{-- ============================================
        ESTILOS CSS
    ============================================= --}}

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    {{-- ============================================
        ESTILOS PERSONALIZADOS
    ============================================= --}}
    <style>
        /* ============================================
           VARIABLES CSS
        ============================================= */
        :root {
            --primary-color: #4e73df;
            --secondary-color: #858796;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --light-color: #f8f9fc;
            --dark-color: #5a5c69;
            --font-family: 'Poppins', sans-serif;
        }

        /* ============================================
           ESTILOS GENERALES
        ============================================= */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-family);
            background-color: var(--light-color);
            color: var(--dark-color);
            font-size: 14px;
            line-height: 1.6;
        }

        /* ============================================
           NAVBAR SUPERIOR
        ============================================= */
        .navbar-top {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 1rem 1.5rem;
        }

        .navbar-brand {
            color: white !important;
            font-weight: 700;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .navbar-user {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .navbar-user .user-name {
            color: white;
            font-weight: 500;
        }

        .navbar-user .badge-role {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        /* ============================================
           TARJETAS DE ESTADÍSTICAS
        ============================================= */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }

        .border-left-primary {
            border-left: 4px solid var(--primary-color) !important;
        }

        .border-left-success {
            border-left: 4px solid var(--success-color) !important;
        }

        .border-left-info {
            border-left: 4px solid var(--info-color) !important;
        }

        .border-left-warning {
            border-left: 4px solid var(--warning-color) !important;
        }

        .border-left-danger {
            border-left: 4px solid var(--danger-color) !important;
        }

        .border-left-secondary {
            border-left: 4px solid var(--secondary-color) !important;
        }

        /* ============================================
           COLORES DE TEXTO
        ============================================= */
        .text-gray-800 {
            color: #5a5c69 !important;
        }

        .text-xs {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        /* ============================================
           EFECTOS Y ANIMACIONES
        ============================================= */
        .hover-shadow {
            transition: all 0.3s ease;
        }

        .hover-shadow:hover {
            transform: translateY(-5px);
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175) !important;
        }

        .opacity-25 {
            opacity: 0.25;
        }

        /* ============================================
           BOTONES DE ACCESO RÁPIDO
        ============================================= */
        .btn-quick-access {
            transition: all 0.3s ease;
            border-width: 2px;
            text-decoration: none !important;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            border-radius: 12px;
        }

        .btn-quick-access:hover {
            transform: scale(1.05);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .btn-quick-access i {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        /* ============================================
           ALERTAS
        ============================================= */
        .alert {
            border-radius: 10px;
            border: none;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        /* ============================================
           TABLAS
        ============================================= */
        .table {
            border-radius: 10px;
            overflow: hidden;
        }

        .table thead {
            background-color: var(--light-color);
        }

        .table-hover tbody tr:hover {
            background-color: rgba(78, 115, 223, 0.05);
        }

        /* ============================================
           BADGES
        ============================================= */
        .badge {
            padding: 0.5rem 0.8rem;
            font-weight: 600;
            border-radius: 8px;
        }

        /* ============================================
           RESPONSIVO
        ============================================= */
        @media (max-width: 768px) {
            .navbar-brand {
                font-size: 1rem;
            }

            .btn-quick-access i {
                font-size: 2rem;
            }
        }
    </style>

    @stack('styles')
</head>
<body>

@include('layouts.partials.trabajador-navbar')

{{-- ============================================
    CONTENIDO PRINCIPAL
============================================= --}}
<main class="container-fluid py-4">
    @yield('content')
</main>

{{-- ============================================
    SCRIPTS JAVASCRIPT
============================================= --}}

<template data-run-after-vite="legacy-scripts">
    @stack('scripts')
</template>
</body>
</html>
