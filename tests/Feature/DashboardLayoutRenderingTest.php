<?php

declare(strict_types=1);

use App\Models\User;

it('renders the admin dashboard through the shared admin layout', function (): void {
    $admin = User::factory()->create([
        'name' => 'Admin Layout Tester',
        'email' => 'admin-layout@example.test',
        'rol' => 'administrador',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Caldos &amp; Desayunos', false)
        ->assertSee('Sistema de Gestión')
        ->assertSee('Menú Principal')
        ->assertSee('Panel de Control')
        ->assertSee('href="'.route('admin.productos.index').'"', false)
        ->assertSee('Productos')
        ->assertSee('href="'.route('admin.pedidos.index').'"', false)
        ->assertSee('Pedidos')
        ->assertSee('href="'.route('profile.edit').'"', false)
        ->assertSee('Perfil')
        ->assertSee('href="'.route('admin.settings.index').'"', false)
        ->assertSee('Configuración')
        ->assertSee('/build/assets/app-', false)
        ->assertSee('type="module"', false)
        ->assertDontSee('cdn.jsdelivr.net/npm/bootstrap', false)
        ->assertDontSee('cdnjs.cloudflare.com/ajax/libs/font-awesome', false)
        ->assertSee('action="'.route('logout').'"', false)
        ->assertSee('Cerrar Sesión')
        ->assertSee('Dashboard - Panel de Control')
        ->assertSee('Resumen general del sistema')
        ->assertSee('Total Productos')
        ->assertSee('Admin Layout Tester');
});

it('renders the admin settings landing page', function (): void {
    $admin = User::factory()->create([
        'name' => 'Settings Tester',
        'email' => 'settings@example.test',
        'rol' => 'administrador',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.settings.index'))
        ->assertOk()
        ->assertSee('Configuración')
        ->assertSee('Perfil de usuario')
        ->assertSee('href="'.route('profile.edit').'"', false)
        ->assertSee('Configuración del sistema');
});

it('renders the admin order create summary card with the scoped sticky layout contract', function (): void {
    $admin = User::factory()->create([
        'name' => 'Order Summary Tester',
        'email' => 'order-summary@example.test',
        'rol' => 'administrador',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.pedidos.create'))
        ->assertOk()
        ->assertSee('Resumen del Pedido')
        ->assertSee('Total a Pagar:')
        ->assertSee('class="card shadow-sm border-0 mb-4 pedido-summary-card"', false)
        ->assertSee('.pedido-summary-card {', false)
        ->assertSee('position: sticky;', false)
        ->assertSee('top: calc(var(--header-height, 70px) + 20px);', false)
        ->assertSee('@media (max-width: 991.98px)', false)
        ->assertSee('position: static;', false)
        ->assertSee('top: auto;', false)
        ->assertDontSee('class="card shadow-sm border-0 mb-4 sticky-top"', false)
        ->assertDontSee('style="top: 20px;"', false);
});

it('renders the worker dashboard through the shared worker layout', function (): void {
    $worker = User::factory()->create([
        'name' => 'Worker Layout Tester',
        'email' => 'worker-layout@example.test',
        'rol' => 'trabajador',
    ]);

    $this->actingAs($worker)
        ->get(route('trabajador.dashboard'))
        ->assertOk()
        ->assertSee('Caldos & Desayunos - Panel Trabajador', false)
        ->assertSee('Trabajador')
        ->assertSee('/build/assets/app-', false)
        ->assertSee('type="module"', false)
        ->assertDontSee('cdn.jsdelivr.net/npm/bootstrap', false)
        ->assertDontSee('cdnjs.cloudflare.com/ajax/libs/font-awesome', false)
        ->assertSee('action="'.route('logout').'"', false)
        ->assertSee('Cerrar Sesión')
        ->assertSee('Dashboard - Panel de Control')
        ->assertSee('Resumen general del sistema')
        ->assertSee('Total Productos')
        ->assertSee('Top 5 - Productos Más Vendidos')
        ->assertSee('Worker Layout Tester');
});
