@extends('layouts.admin')

@section('title', 'Configuración')

@section('breadcrumb')
    <li class="breadcrumb-item active">Configuración</li>
@endsection

@section('admin_content')
    <div class="container-fluid py-4">
        <x-page-header
            title="Configuración"
            subtitle="Accesos rápidos para administrar tu cuenta y el sistema"
            icon="fas fa-cog"
            subtitle-icon="fas fa-sliders-h"
            class="animate__animated animate__fadeInDown"
        />

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-user-cog text-primary me-2" aria-hidden="true"></i>
                            Perfil de usuario
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-4">
                            Actualiza tu nombre, correo electrónico y credenciales desde el perfil de tu cuenta.
                        </p>

                        <a href="{{ route('profile.edit') }}" class="btn btn-primary">
                            <i class="fas fa-user me-2" aria-hidden="true"></i>
                            Ir a Perfil
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-tools text-secondary me-2" aria-hidden="true"></i>
                            Configuración del sistema
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-0">
                            Las opciones avanzadas del sistema se habilitarán aquí cuando estén disponibles.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
